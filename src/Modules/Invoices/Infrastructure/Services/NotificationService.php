<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Services;

use Modules\Invoices\Application\Services\NotificationServiceInterface;
use Modules\Notifications\Api\Dtos\NotifyData;
use Modules\Notifications\Api\NotificationFacadeInterface;
use Ramsey\Uuid\UuidInterface;

/**
 * Notification Service
 *
 * Concrete implementation of NotificationServiceInterface that acts as an adapter
 * to the external Notifications module. This class belongs to the infrastructure
 * layer and contains the actual call to the NotificationFacade.
 *
 * ## Purpose
 * - Implements the local notification service interface
 * - Adapts external notification functionality to local needs
 * - Maintains hexagonal architecture boundaries
 * - Provides concrete implementation for dependency injection
 */
class NotificationService implements NotificationServiceInterface
{
    public function __construct(
        private NotificationFacadeInterface $notificationFacade
    ) {}

    public function notify(UuidInterface $resourceId, string $toEmail, string $subject, string $message): void
    {
        $notifyData = new NotifyData(
            resourceId: $resourceId,
            toEmail: $toEmail,
            subject: $subject,
            message: $message
        );

        $this->notificationFacade->notify($notifyData);
    }
}
