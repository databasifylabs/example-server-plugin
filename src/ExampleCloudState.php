<?php

declare(strict_types=1);

namespace Databasify\ExampleServer;

use App\DTOs\Providers\ProviderNetwork;
use Illuminate\Support\Arr;

final readonly class ExampleCloudState
{
    /**
     * @param  list<StoredKey>  $keys
     * @param  list<ProviderNetwork>  $networks
     * @param  list<StoredServer>  $servers
     */
    public function __construct(
        public array $keys = [],
        public array $networks = [],
        public array $servers = [],
    ) {}

    /**
     * @param  array<mixed>  $state
     */
    public static function fromArray(array $state): self
    {
        return new self(
            array_map(StoredKey::fromArray(...), self::rowsIn($state, 'keys')),
            array_map(self::networkFrom(...), self::rowsIn($state, 'networks')),
            array_map(StoredServer::fromArray(...), self::rowsIn($state, 'servers')),
        );
    }

    /**
     * @return array{keys: list<array{id: string, public_key: string}>, networks: list<array{external_id: string, name: string, cidr: string, region: string, is_default: bool}>, servers: list<array{id: string, name: string}>}
     */
    public function toArray(): array
    {
        return [
            'keys' => array_map(fn (StoredKey $key): array => $key->toArray(), $this->keys),
            'networks' => array_map(fn (ProviderNetwork $network): array => $network->toArray(), $this->networks),
            'servers' => array_map(fn (StoredServer $server): array => $server->toArray(), $this->servers),
        ];
    }

    public function keyFor(string $publicKey): ?StoredKey
    {
        return collect($this->keys)->first(fn (StoredKey $key): bool => $key->publicKey === $publicKey);
    }

    public function server(string $id): ?StoredServer
    {
        return collect($this->servers)->first(fn (StoredServer $server): bool => $server->id === $id);
    }

    public function withKey(StoredKey $key): self
    {
        return new self([...$this->keys, $key], $this->networks, $this->servers);
    }

    public function withNetwork(ProviderNetwork $network): self
    {
        return new self($this->keys, [...$this->networks, $network], $this->servers);
    }

    public function withoutNetwork(string $externalId): self
    {
        return new self($this->keys, array_values(array_filter($this->networks, fn (ProviderNetwork $network): bool => $network->externalId !== $externalId)), $this->servers);
    }

    public function withServer(StoredServer $server): self
    {
        return new self($this->keys, $this->networks, [...$this->servers, $server]);
    }

    public function withoutServer(string $id): self
    {
        return new self($this->keys, $this->networks, array_values(array_filter($this->servers, fn (StoredServer $server): bool => $server->id !== $id)));
    }

    /**
     * @param  array<mixed>  $state
     * @return list<array<mixed>>
     */
    private static function rowsIn(array $state, string $key): array
    {
        $rows = $state[$key] ?? [];

        return array_values(array_filter(is_array($rows) ? $rows : [], is_array(...)));
    }

    /**
     * @param  array<mixed>  $row
     */
    private static function networkFrom(array $row): ProviderNetwork
    {
        return new ProviderNetwork(Arr::string($row, 'external_id'), Arr::string($row, 'name'), Arr::string($row, 'cidr'), Arr::string($row, 'region'));
    }
}
