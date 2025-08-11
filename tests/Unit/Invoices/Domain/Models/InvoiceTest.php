<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Domain\Models;

use PHPUnit\Framework\TestCase;
use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Enums\InvoiceStatus;
use Modules\Invoices\Domain\ValueObjects\Email;
use Ramsey\Uuid\UuidInterface;

class InvoiceTest extends TestCase
{
    public function testShouldCreateInvoiceInDraftStatusWithoutProductLines(): void
    {
        $customerName = 'John Doe';
        $customerEmail = Email::fromString('john@example.com');

        $invoice = Invoice::create($customerName, $customerEmail);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertInstanceOf(UuidInterface::class, $invoice->getId());
        $this->assertSame(InvoiceStatus::DRAFT, $invoice->getStatus());
        $this->assertSame($customerName, $invoice->getCustomerName());
        $this->assertSame($customerEmail, $invoice->getCustomerEmail());
        $this->assertFalse($invoice->hasProductLines());
    }
}
