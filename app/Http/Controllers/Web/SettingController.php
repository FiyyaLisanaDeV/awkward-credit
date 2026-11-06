<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\SettingAction;
use App\Enums\SettingSection;
use App\Enums\StatusRevision;
use App\Http\Controllers\Controller;
use App\Http\Requests\Setting\StoreRevisionRequest;
use App\Models\Cabang;
use App\Models\SettingRevision;
use App\Services\Setting\SettingRevisionService;
use App\Services\Setting\SettingSectionRegistry;
use App\Support\Konstanta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SettingController extends Controller
{
    public function __construct(
        private readonly SettingSectionRegistry $registry,
        private readonly SettingRevisionService $service,
    ) {}

    public function index(): View
    {
        $sections = [];

        foreach ($this->registry->all() as $key => $meta) {
            $section = SettingSection::from($key);
            $model = $meta['model'];

            $sections[] = [
                'section' => $section,
                'key' => $key,
                'label' => (string) __($meta['label']),
                'description' => (string) __($meta['description']),
                'count' => $model::query()->count(),
                'active_version' => $this->service->activeVersion($section),
                'pending' => $this->service->pendingCount($section),
            ];
        }

        return view('settings.index', ['sections' => $sections]);
    }

    public function section(Request $request, string $section): View
    {
        $sectionEnum = $this->sectionFrom($section);
        $meta = $this->registry->all()[$sectionEnum->value];
        $model = $meta['model'];
        $versioned = $meta['versioned'];

        $active = $versioned
            ? $model::query()->where('status', 'ACTIVE')->first()
            : $model::query()->orderBy('created_at')->get();

        $revisions = SettingRevision::query()
            ->where('section', $sectionEnum->value)
            ->with(['creator', 'approver', 'rejector'])
            ->orderByDesc('created_at')
            ->get();

        $payload = $this->defaultPayload($sectionEnum, $active);
        $target = null;
        $draftRevision = null;

        $targetId = $request->query('target');

        if (! $versioned && is_string($targetId) && $targetId !== '') {
            $target = $model::query()->findOrFail($targetId);
            $payload = $target->toArray();
        }

        $revisionId = $request->query('revision');

        if (is_string($revisionId) && $revisionId !== '') {
            $draftRevision = SettingRevision::query()
                ->whereKey($revisionId)
                ->where('section', $sectionEnum->value)
                ->where('status', StatusRevision::Draft->value)
                ->first();

            if ($draftRevision instanceof SettingRevision) {
                $payload = $draftRevision->payload;
                $targetId = $draftRevision->target_id;
            }
        }

        return view('settings.section', [
            'section' => $sectionEnum,
            'meta' => $meta,
            'active' => $active,
            'revisions' => $revisions,
            'payload' => $payload,
            'target' => $target,
            'targetId' => is_string($targetId) ? $targetId : null,
            'draftRevision' => $draftRevision,
            'fields' => $this->registry->referenceFields($sectionEnum),
            'policyGroups' => $this->registry->policyGroups(),
            'policyLabels' => $this->registry->policyFieldLabels(),
            'roles' => $this->registry->roles(),
            'cabang' => Cabang::query()->orderBy('nama')->get(),
        ]);
    }

    public function inbox(): View
    {
        $revisions = SettingRevision::query()
            ->where('status', StatusRevision::Pending->value)
            ->with(['creator'])
            ->orderBy('submitted_at')
            ->paginate(Konstanta::SETTING_PER_PAGE);

        return view('settings.inbox', ['revisions' => $revisions]);
    }

    public function history(Request $request): View
    {
        $section = $request->query('section');
        $status = $request->query('status');

        $sectionEnum = is_string($section) && $section !== '' ? SettingSection::tryFrom($section) : null;
        $statusEnum = is_string($status) && $status !== '' ? StatusRevision::tryFrom($status) : null;

        return view('settings.history', [
            'revisions' => $this->service->history($sectionEnum, $statusEnum),
            'section' => $sectionEnum,
            'status' => $statusEnum,
        ]);
    }

    public function store(StoreRevisionRequest $request, string $section): RedirectResponse
    {
        $sectionEnum = $this->sectionFrom($section);
        $data = $request->validated();
        $payload = $this->normalizePayload($sectionEnum, $data['payload'] ?? []);

        $revision = $this->service->createDraft(
            $sectionEnum,
            SettingAction::from((string) $data['action']),
            $payload,
            $data['target_id'] ?? null,
            $request->user(),
            $data['note'] ?? null,
        );

        return redirect()
            ->route('admin.section', ['section' => $sectionEnum->value, 'revision' => $revision->id])
            ->with('status', __('setting.draft_saved'));
    }

    public function update(StoreRevisionRequest $request, SettingRevision $revision): RedirectResponse
    {
        $data = $request->validated();
        $payload = $this->normalizePayload($revision->section, $data['payload'] ?? []);

        $this->service->updateDraft($revision, $payload, $data['note'] ?? null, $request->user());

        return redirect()
            ->route('admin.section', ['section' => $revision->section->value, 'revision' => $revision->id])
            ->with('status', __('setting.draft_saved'));
    }

    public function submit(Request $request, SettingRevision $revision): RedirectResponse
    {
        $this->service->submit($revision, $request->user());

        return back()->with('status', __('setting.submitted'));
    }

    public function approve(Request $request, SettingRevision $revision): RedirectResponse
    {
        $this->service->approve($revision, $request->user());

        return back()->with('status', __('setting.approved'));
    }

    public function reject(Request $request, SettingRevision $revision): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        $this->service->reject($revision, $request->user(), (string) $data['reason']);

        return back()->with('status', __('setting.rejected'));
    }

    public function apply(Request $request, SettingRevision $revision): RedirectResponse
    {
        $this->service->apply($revision, $request->user());

        return back()->with('status', __('setting.applied'));
    }

    private function sectionFrom(string $section): SettingSection
    {
        $sectionEnum = SettingSection::tryFrom($section);

        abort_if($sectionEnum === null, 404);

        return $sectionEnum;
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultPayload(SettingSection $section, mixed $active): array
    {
        if ($section === SettingSection::PolicyParameter) {
            return [
                'params' => $active ? $active->params : [],
                'effective_date' => $active ? $active->effective_date?->toDateString() : now()->toDateString(),
            ];
        }

        if ($section === SettingSection::ApprovalMatrix) {
            return [
                'matrix' => $active ? $active->matrix : [],
                'effective_date' => $active ? $active->effective_date?->toDateString() : now()->toDateString(),
            ];
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function normalizePayload(SettingSection $section, array $payload): array
    {
        if ($section === SettingSection::PolicyParameter) {
            $params = is_array($payload['params'] ?? null) ? $payload['params'] : [];
            $params['skor_band_draft'] = (bool) ($params['skor_band_draft'] ?? false);

            foreach (['skor_band_dscr', 'skor_band_equity_asset', 'skor_band_ltv', 'skor_band_slik'] as $key) {
                $rows = is_array($params[$key] ?? null) ? $params[$key] : [];
                $params[$key] = array_values(array_filter($rows, static function (array $row): bool {
                    return ($row['min'] ?? $row['max'] ?? $row['maks_kol3'] ?? '') !== ''
                        && ($row['skor'] ?? '') !== '';
                }));
            }

            $payload['params'] = $params;
        }

        if ($section === SettingSection::ApprovalMatrix) {
            $rows = is_array($payload['matrix'] ?? null) ? $payload['matrix'] : [];
            $rows = array_values(array_filter($rows, static fn (array $row): bool => ($row['plafon_min'] ?? '') !== '' && ($row['level'] ?? '') !== ''));

            foreach ($rows as $index => $row) {
                if (($row['plafon_max'] ?? '') === '') {
                    $rows[$index]['plafon_max'] = null;
                }
            }

            $payload['matrix'] = $rows;
        }

        if ($section === SettingSection::ChecklistDokumen) {
            $payload['wajib'] = (bool) ($payload['wajib'] ?? false);
            $payload['is_active'] = (bool) ($payload['is_active'] ?? false);
        }

        if ($section === SettingSection::CollateralCategory || $section === SettingSection::Produk) {
            $payload['is_active'] = (bool) ($payload['is_active'] ?? false);
        }

        if ($section === SettingSection::TemplateDokumen && ($payload['file_path'] ?? '') === '') {
            $payload['file_path'] = null;
        }

        return $payload;
    }
}
