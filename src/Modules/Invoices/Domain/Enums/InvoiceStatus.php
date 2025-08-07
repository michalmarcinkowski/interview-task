<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Enums;

enum InvoiceStatus: string
{
    case DRAFT = 'draft';
    case SENDING = 'sending';
    case SENT_TO_CLIENT = 'sent-to-client';
}
