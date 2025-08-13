<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Exceptions;

use Modules\Invoices\Domain\Enums\InvoiceStatus;
use Ramsey\Uuid\UuidInterface;

final class InvalidInvoiceStatusTransitionException extends \DomainException
{
    public static function cannotMarkAsSentToClient(
        UuidInterface $invoiceId,
        InvoiceStatus $currentStatus
    ): self {
        return new self(
            "Cannot mark invoice {$invoiceId->toString()} as sent to client. ".
            "Current status is '{$currentStatus->value}', but must be 'sending'."
        );
    }

    public static function cannotMarkAsSending(
        UuidInterface $invoiceId,
        InvoiceStatus $currentStatus
    ): self {
        return new self(
            "Cannot mark invoice {$invoiceId->toString()} as sending. ".
            "Current status is '{$currentStatus->value}', but must be 'draft'."
        );
    }

    public static function cannotMarkAsSendingDueToBusinessRules(
        UuidInterface $invoiceId,
        string $reason
    ): self {
        return new self(
            "Cannot mark invoice {$invoiceId->toString()} as sending. ".$reason
        );
    }
}
