<?php

declare(strict_types=1);

namespace Modules\Notifications\Application\Facades;

use Modules\Notifications\Api\Dtos\NotifyData;
use Modules\Notifications\Api\NotificationFacadeInterface;
use Modules\Notifications\Application\Services\WebhookSimulatorInterface;
use Modules\Notifications\Infrastructure\Drivers\DriverInterface;

final readonly class NotificationFacade implements NotificationFacadeInterface
{
    public function __construct(
        private DriverInterface $driver,
        private WebhookSimulatorInterface $webhookSimulator,
    ) {}

    public function notify(NotifyData $data): void
    {
        $sent = $this->driver->send(
            toEmail: $data->toEmail,
            subject: $data->subject,
            message: $data->message,
            reference: $data->resourceId->toString(),
        );

        if ($sent) {
            // Simulate delivery confirmation webhook after successful sending
            $this->webhookSimulator->simulateDeliveryConfirmation($data->resourceId->toString());
        }
    }
}
