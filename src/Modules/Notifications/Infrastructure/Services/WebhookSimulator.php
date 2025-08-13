<?php

declare(strict_types=1);

namespace Modules\Notifications\Infrastructure\Services;

use Illuminate\Contracts\Events\Dispatcher;
use Modules\Notifications\Api\Events\ResourceDeliveredEvent;
use Modules\Notifications\Application\Services\WebhookSimulatorInterface;
use Ramsey\Uuid\Uuid;

final readonly class WebhookSimulator implements WebhookSimulatorInterface
{
    public function __construct(
        private Dispatcher $dispatcher,
    ) {}

    public function simulateDeliveryConfirmation(string $reference): bool
    {
        try {
            // This simulates what the webhook would do
            // Instead of making an HTTP call, directly dispatch the event
            // $response = $this->httpClient->get("/notification/hook/delivered/{$reference}");
            // return $response->successful();
            $this->dispatcher->dispatch(new ResourceDeliveredEvent(
                resourceId: Uuid::fromString($reference),
            ));
            
            return true;
        } catch (\Exception $e) {
            // In a real scenario, this would be logged
            return false;
        }
    }
}
