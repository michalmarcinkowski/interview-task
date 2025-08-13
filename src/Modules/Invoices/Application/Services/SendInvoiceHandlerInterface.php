<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Services;

use Modules\Invoices\Application\Commands\SendInvoiceCommand;

interface SendInvoiceHandlerInterface
{
    /**
     * Handles the invoice sending process.
     *
     * @param  SendInvoiceCommand  $command  The send invoice command
     *
     * @throws \Modules\Invoices\Domain\Exceptions\InvoiceNotFoundException When invoice is not found
     * @throws \InvalidArgumentException When invoice cannot be sent (business rules violation)
     */
    public function handle(SendInvoiceCommand $command): void;
}
