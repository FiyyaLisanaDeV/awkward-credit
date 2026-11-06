<?php

declare(strict_types=1);

namespace App\Domain\Policy;

use App\Enums\PolicyParameterKey;
use App\Exceptions\MissingPolicyParameter;
use App\Models\RefParameterKebijakan;

final class PolicyParameters
{
    /**
     * @param  array<string, mixed>  $params
     */
    public function __construct(
        public readonly string $id,
        public readonly string $version,
        private readonly array $params,
    ) {}

    public static function fromModel(RefParameterKebijakan $model): self
    {
        $params = $model->params;

        if (! is_array($params)) {
            throw new MissingPolicyParameter('Parameter kebijakan tidak berisi object JSON yang sah.');
        }

        return new self((string) $model->id, (string) $model->version, $params);
    }

    /**
     * @return array{id: string, version: string, params: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'version' => $this->version,
            'params' => $this->params,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        if (! isset($data['id'], $data['version']) || ! isset($data['params']) || ! is_array($data['params'])) {
            throw new MissingPolicyParameter('Snapshot parameter kebijakan tidak lengkap.');
        }

        return new self(
            (string) $data['id'],
            (string) $data['version'],
            $data['params'],
        );
    }

    public function float(PolicyParameterKey $key): float
    {
        return (float) $this->raw($key);
    }

    public function int(PolicyParameterKey $key): int
    {
        return (int) $this->raw($key);
    }

    public function bool(PolicyParameterKey $key): bool
    {
        return (bool) $this->raw($key);
    }

    /**
     * @return array<int|string, mixed>
     */
    public function array(PolicyParameterKey $key): array
    {
        $value = $this->raw($key);

        if (! is_array($value)) {
            throw new MissingPolicyParameter("Parameter {$key->value} bukan array.");
        }

        return $value;
    }

    public function has(PolicyParameterKey $key): bool
    {
        return array_key_exists($key->value, $this->params);
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->params;
    }

    private function raw(PolicyParameterKey $key): mixed
    {
        if (! array_key_exists($key->value, $this->params)) {
            throw new MissingPolicyParameter("Parameter {$key->value} tidak ditemukan pada versi {$this->version}.");
        }

        return $this->params[$key->value];
    }
}
