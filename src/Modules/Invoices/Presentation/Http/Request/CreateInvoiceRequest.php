<?php

declare(strict_types=1);

namespace Modules\Invoices\Presentation\Http\Request;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\ArrayType;

class CreateInvoiceRequest extends Data
{
    public function __construct(
        #[Required]
        public string $customerName,

        #[Required, Email]
        public string $customerEmail,

        #[ArrayType]
        public ?array $productLines = null,
    ) {}

    public static function rules(): array
    {
        return [
            'productLines.*.productName' => ['required', 'string', 'max:255'],
            'productLines.*.quantity' => ['required', 'integer', 'min:1'],
            'productLines.*.unitPrice' => ['required', 'integer', 'min:1'],
        ];
    }
}
