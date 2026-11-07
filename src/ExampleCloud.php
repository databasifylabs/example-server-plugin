<?php

declare(strict_types=1);

namespace Databasify\ExampleServer;

use App\DTOs\Providers\NewProviderServer;
use App\DTOs\Providers\ProviderNetwork;
use App\DTOs\Providers\ProviderPlan;
use App\DTOs\Providers\ProviderRegion;
use App\DTOs\Providers\ProviderServer;
use App\Exceptions\Providers\ProviderRequestFailed;
use App\ServerProviders\Drivers\AbstractServerDriver;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final readonly class ExampleCloud extends AbstractServerDriver
{
    private const string LABEL = 'Example Cloud';

    private const string REGION = 'local';

    private const string PLAN = 'your-server';

    private const string NETWORK_RANGE = '10.0.0.0/16';

    public static function id(): string
    {
        return 'example-cloud';
    }

    public static function directory(): string
    {
        return storage_path('app/example-cloud');
    }

    public function verify(): void
    {
        if (filter_var($this->host(), FILTER_VALIDATE_IP) === false) {
            throw ProviderRequestFailed::from(self::LABEL, 422, 'Enter the IP address of your server, for example 203.0.113.10.');
        }
    }

    public function regions(): array
    {
        return [new ProviderRegion(self::REGION, 'Your server')];
    }

    public function plans(string $region): array
    {
        return [new ProviderPlan(self::PLAN, 'Your own server', 1, self::SMALLEST_MEMORY_MB, 25, 0.0)];
    }

    public function networks(string $region): array
    {
        return array_values(array_filter($this->load()->networks, fn (ProviderNetwork $network): bool => $network->region === $region));
    }

    public function createNetwork(string $region, string $name): ProviderNetwork
    {
        $network = new ProviderNetwork(Str::uuid()->toString(), $name, self::NETWORK_RANGE, $region);
        $this->save($this->load()->withNetwork($network));

        return $network;
    }

    public function deleteNetwork(string $externalId): void
    {
        $this->save($this->load()->withoutNetwork($externalId));
    }

    public function findKey(string $publicKey): ?string
    {
        return $this->load()->keyFor($publicKey)?->id;
    }

    public function createKey(string $name, string $publicKey): string
    {
        $key = StoredKey::created($publicKey);
        $this->save($this->load()->withKey($key));

        return $key->id;
    }

    public function createServer(NewProviderServer $server): string
    {
        $stored = StoredServer::ordered($server);
        $this->save($this->load()->withServer($stored));

        return $stored->id;
    }

    public function findServer(string $id): ProviderServer
    {
        if ($this->load()->server($id) === null) {
            throw ProviderRequestFailed::from(self::LABEL, 404, "There is no server {$id} at {$this->host()}.");
        }

        return new ProviderServer($id, running: true, publicIp: $this->host(), privateIp: null);
    }

    public function deleteServer(string $id): void
    {
        $this->save($this->load()->withoutServer($id));
    }

    private function save(ExampleCloudState $state): void
    {
        File::ensureDirectoryExists(self::directory());
        File::put($this->file(), json_encode($state->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR), lock: true);
    }

    private function load(): ExampleCloudState
    {
        $state = File::exists($this->file()) ? json_decode(File::get($this->file(), lock: true), true) : [];

        return ExampleCloudState::fromArray(is_array($state) ? $state : []);
    }

    private function file(): string
    {
        return self::directory().'/'.sha1($this->host()).'.json';
    }

    private function host(): string
    {
        return trim($this->serverProvider->credentials['host'] ?? '');
    }
}
