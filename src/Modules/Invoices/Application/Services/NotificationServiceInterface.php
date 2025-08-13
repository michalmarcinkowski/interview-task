<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Services;

use Ramsey\Uuid\UuidInterface;

/**
 * Notification Service Interface
 *
 * Defines the contract for sending notifications in the Invoices module.
 * This interface belongs to the application layer, ensuring the core business
 * logic is not tied to a specific notification implementation.
 *
 * ## Purpose
 * - Provides a clean contract for notification operations
 * - Enables dependency injection and loose coupling
 * - Supports testing through interface mocking
 * - Maintains hexagonal architecture boundaries
 */
interface NotificationServiceInterface
{
    /**
     * Sends a notification to the specified email address.
     *
     * @param  UuidInterface  $resourceId  The ID of the resource being notified about
     * @param  string  $toEmail  The recipient's email address
     * @param  string  $subject  The email subject
     * @param  string  $message  The email message content
     */
    public function notify(UuidInterface $resourceId, string $toEmail, string $subject, string $message): void;
}
