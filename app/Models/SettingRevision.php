<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SettingAction;
use App\Enums\SettingSection;
use App\Enums\StatusRevision;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property SettingSection $section
 * @property SettingAction $action
 * @property array<string, mixed> $payload
 * @property StatusRevision $status
 */
class SettingRevision extends Model
{
    use HasUuids;

    protected $table = 'setting_revisions';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'section',
        'action',
        'target_id',
        'payload',
        'status',
        'note',
        'created_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'applied_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'section' => SettingSection::class,
            'action' => SettingAction::class,
            'payload' => 'array',
            'status' => StatusRevision::class,
            'created_by' => 'integer',
            'submitted_at' => 'datetime',
            'approved_by' => 'integer',
            'approved_at' => 'datetime',
            'rejected_by' => 'integer',
            'rejected_at' => 'datetime',
            'applied_at' => 'datetime',
        ];
    }

    public function isEditable(): bool
    {
        return $this->status === StatusRevision::Draft;
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return BelongsTo<User, $this> */
    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
