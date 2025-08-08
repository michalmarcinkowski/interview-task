<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http\Request;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Required;

class CreateInvoiceRequest extends Data
{
    public function __construct(
        #[Required]
        public string $customerName,

        #[Required, Email]
        public string $customerEmail,
    ) {}
}
