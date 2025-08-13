<?php

declare(strict_types=1);

namespace Modules\Notifications\Infrastructure\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Modules\Notifications\Api\NotificationFacadeInterface;
use Modules\Notifications\Application\Facades\NotificationFacade;
use Modules\Notifications\Application\Services\WebhookSimulatorInterface;
use Modules\Notifications\Infrastructure\Drivers\DummyDriver;
use Modules\Notifications\Infrastructure\Services\WebhookSimulator;
use Illuminate\Events\Dispatcher;

final class NotificationServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->scoped(NotificationFacadeInterface::class, NotificationFacade::class);

        $this->app->singleton(WebhookSimulatorInterface::class, function ($app) {
            return new WebhookSimulator(
                dispatcher: $app->make(Dispatcher::class),
            );
        });

        $this->app->singleton(NotificationFacade::class, static fn ($app) => new NotificationFacade(
            driver: $app->make(DummyDriver::class),
            webhookSimulator: $app->make(WebhookSimulatorInterface::class),
        ));
    }

    /** @return array<class-string> */
    public function provides(): array
    {
        return [
            NotificationFacadeInterface::class,
            WebhookSimulatorInterface::class,
        ];
    }
}
