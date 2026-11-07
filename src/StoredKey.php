<?php

declare(strict_types=1);

namespace Databasify\ExampleServer;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final readonly class StoredKey
{
    public function __construct(
        public string $id,
        public string $publicKey,
    ) {}

    public static function created(string $publicKey): self
    {
        return new self(Str::uuid()->toString(), $publicKey);
    }

    /**
     * @param  array<mixed>  $row
     */
    public static function fromArray(array $row): self
    {
        return new self(Arr::string($row, 'id'), Arr::string($row, 'public_key'));
    }

    /**
     * @return array{id: string, public_key: string}
     */
    public function toArray(): array
    {
        return ['id' => $this->id, 'public_key' => $this->publicKey];
    }
}
