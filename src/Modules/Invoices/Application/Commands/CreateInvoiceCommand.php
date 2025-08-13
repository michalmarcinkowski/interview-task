<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Commands;

use Webmozart\Assert\Assert;

final readonly class CreateInvoiceCommand
{
    private function __construct(
        public readonly string $customerName,
        public readonly string $customerEmail,
        public readonly array $productLines = [],
    ) {
        $this->validate();
    }

    public static function fromValues(string $customerName, string $customerEmail, array $productLines = []): self
    {
        return new self($customerName, $customerEmail, $productLines);
    }

    private function validate(): void
    {
        Assert::notEmpty($this->customerName, 'Customer name cannot be empty.');
        Assert::email($this->customerEmail, 'Customer email is not a valid email address.');

        foreach ($this->productLines as $index => $lineData) {
            Assert::keyExists($lineData, 'productName', "Product line at index {$index} is missing productName.");
            Assert::keyExists($lineData, 'quantity', "Product line at index {$index} is missing quantity.");
            Assert::keyExists($lineData, 'unitPrice', "Product line at index {$index} is missing unitPrice.");

            Assert::string($lineData['productName'], "Product name at index {$index} must be a string.");
            Assert::notEmpty($lineData['productName'], "Product name at index {$index} cannot be empty.");

            Assert::positiveInteger($lineData['quantity'], "Quantity at index {$index} must be a positive integer.");

            Assert::positiveInteger($lineData['unitPrice'], "Unit price at index {$index} must be a positive integer.");
        }
    }
}
