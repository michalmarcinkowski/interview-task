<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Exceptions;

use Ramsey\Uuid\UuidInterface;

final class InvoiceNotFoundException extends NotFoundException
{
    public static function withId(UuidInterface $invoiceId): self
    {
        return new self(
            sprintf('Invoice with ID "%s" was not found.', $invoiceId->toString())
        );
    }
}
