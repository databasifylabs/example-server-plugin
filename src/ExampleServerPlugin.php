<?php

declare(strict_types=1);

namespace Databasify\ExampleServer;

use App\DTOs\Forms\DynamicField;
use App\DTOs\Forms\DynamicForm;
use App\Enums\Servers\OperatingSystem;
use App\Plugins\AbstractPlugin;
use Illuminate\Support\Facades\File;

final class ExampleServerPlugin extends AbstractPlugin
{
    public function boot(): void
    {
        $this->databasify()->server(ExampleCloud::id())
            ->label('Example Cloud')
            ->handler(ExampleCloud::class)
            ->form(DynamicForm::make([
                DynamicField::make('host')->text()->label('Server IP address')->placeholder('203.0.113.10'),
            ]))
            ->serverLabel('server')
            ->sshUser('root')
            ->images($this->imagesOf(OperatingSystem::cases()))
            ->instructions([
                'This provider orders nothing. It hands Databasify a server you already own, so you can try ordering without a cloud account.',
                'Enter the public IP of an existing '.OperatingSystem::supportedSummary().' server that Databasify can reach over SSH on port 22.',
                'Before you order, add the Databasify SSH key to `/root/.ssh/authorized_keys` on that server. Open **Servers → Add server → Existing server**, choose **SSH key** under First sign-in, copy the command and run it as `root`.',
                'When you order, pick the operating system the server already runs. Databasify then sets it up over SSH as `root`.',
                'Destroying the server at the provider when you delete it in Databasify only forgets it. The machine keeps running.',
            ])
            ->register();
    }

    public function uninstall(): void
    {
        File::deleteDirectory(ExampleCloud::directory());
    }

    /**
     * @param  list<OperatingSystem>  $systems
     * @return array<string, string>
     */
    private function imagesOf(array $systems): array
    {
        return collect($systems)->mapWithKeys(fn (OperatingSystem $os): array => [$os->value => $os->value])->all();
    }
}
