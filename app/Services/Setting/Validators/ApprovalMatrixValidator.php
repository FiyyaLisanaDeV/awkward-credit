<?php

declare(strict_types=1);

namespace App\Services\Setting\Validators;

use App\Support\Konstanta;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class ApprovalMatrixValidator
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function validate(array $payload): void
    {
        Validator::make($payload, [
            'matrix' => ['required', 'array', 'min:1'],
            'matrix.*.plafon_min' => ['required', 'numeric', 'min:0'],
            'matrix.*.plafon_max' => ['nullable', 'numeric'],
            'matrix.*.level' => ['required', 'integer', 'between:1,'.Konstanta::SETTING_MAX_LEVEL],
            'matrix.*.role' => ['required', 'string', Rule::exists('roles', 'name')],
        ])->validate();

        $rows = array_values($payload['matrix']);

        foreach ($rows as $index => $row) {
            $max = $row['plafon_max'] ?? null;

            if ($max !== null && (float) $max <= (float) $row['plafon_min']) {
                $this->fail('matrix.'.$index.'.plafon_max', 'Plafon maksimum harus lebih besar dari plafon minimum.');
            }
        }

        $this->assertNoDuplicateLevel($rows);
        $this->assertCoverage($rows);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function assertNoDuplicateLevel(array $rows): void
    {
        $seen = [];

        foreach ($rows as $index => $row) {
            $band = (string) $row['plafon_min'].'|'.($row['plafon_max'] ?? 'inf');
            $level = (int) $row['level'];
            $key = $band.'|'.$level;

            if (isset($seen[$key])) {
                $this->fail('matrix.'.$index.'.level', 'Level duplikat untuk band plafon yang sama.');
            }

            $seen[$key] = true;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function assertCoverage(array $rows): void
    {
        $bands = [];

        foreach ($rows as $row) {
            $min = (float) $row['plafon_min'];
            $max = $row['plafon_max'] ?? null;
            $bands[(string) $min.'|'.($max === null ? 'inf' : (string) $max)] = [
                'min' => $min,
                'max' => $max === null ? null : (float) $max,
            ];
        }

        usort($bands, static fn (array $a, array $b): int => $a['min'] <=> $b['min']);

        if ($bands[0]['min'] !== 0.0) {
            $this->fail('matrix', 'Matriks harus dimulai dari plafon 0.');
        }

        $last = count($bands) - 1;

        foreach ($bands as $index => $band) {
            if ($band['max'] === null) {
                if ($index !== $last) {
                    $this->fail('matrix', 'Hanya band teratas yang boleh tanpa plafon maksimum.');
                }

                continue;
            }

            if ($index === $last) {
                $this->fail('matrix', 'Band teratas harus tanpa plafon maksimum (terbuka).');
            }

            if ($band['max'] !== $bands[$index + 1]['min']) {
                $this->fail('matrix', 'Band plafon harus bersambung tanpa celah atau tumpang tindih.');
            }
        }
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
