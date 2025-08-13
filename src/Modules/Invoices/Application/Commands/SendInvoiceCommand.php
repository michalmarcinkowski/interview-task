<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Commands;

use Ramsey\Uuid\UuidInterface;

/**
 * Send Invoice Command
 *
 * Represents the command to send an invoice to a customer.
 * This command contains the data needed to execute the send invoice operation.
 */
final readonly class SendInvoiceCommand
{
    public function __construct(
        public UuidInterface $invoiceId,
    ) {}
}
