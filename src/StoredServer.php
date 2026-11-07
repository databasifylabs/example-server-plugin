<?php

declare(strict_types=1);

namespace Databasify\ExampleServer;

use App\DTOs\Providers\NewProviderServer;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final readonly class StoredServer
{
    public function __construct(
        public string $id,
        public string $name,
    ) {}

    public static function ordered(NewProviderServer $server): self
    {
        return new self(Str::uuid()->toString(), $server->name);
    }

    /**
     * @param  array<mixed>  $row
     */
    public static function fromArray(array $row): self
    {
        return new self(Arr::string($row, 'id'), Arr::string($row, 'name'));
    }

    /**
     * @return array{id: string, name: string}
     */
    public function toArray(): array
    {
        return ['id' => $this->id, 'name' => $this->name];
    }
}
