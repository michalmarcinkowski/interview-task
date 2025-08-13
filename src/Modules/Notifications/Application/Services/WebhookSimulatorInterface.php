<?php

declare(strict_types=1);

namespace Modules\Notifications\Application\Services;

interface WebhookSimulatorInterface
{
    /**
     * Simulates an external notification service calling our webhook
     * to confirm successful delivery.
     *
     * @param  string  $reference  The resource reference (usually invoice ID)
     * @return bool True if webhook call was successful, false otherwise
     */
    public function simulateDeliveryConfirmation(string $reference): bool;
}
