<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Domain\Models;

use Modules\Invoices\Domain\Enums\InvoiceStatus;
use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\ValueObjects\Email;
use Modules\Invoices\Domain\ValueObjects\ProductLines;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

class InvoiceTest extends TestCase
{
    public function test_should_create_invoice_in_draft_status_without_product_lines(): void
    {
        $customerName = 'John Doe';
        $customerEmail = Email::fromString('john@example.com');
        $emptyProductLines = ProductLines::empty();

        $invoice = Invoice::create($customerName, $customerEmail, $emptyProductLines);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertInstanceOf(UuidInterface::class, $invoice->getId());
        $this->assertSame(InvoiceStatus::DRAFT, $invoice->getStatus());
        $this->assertSame($customerName, $invoice->getCustomerName());
        $this->assertSame($customerEmail, $invoice->getCustomerEmail());
        $this->assertFalse($invoice->hasProductLines());
    }
}
