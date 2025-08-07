<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Domain\Models;

use PHPUnit\Framework\TestCase;
use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Enums\InvoiceStatus;
use Ramsey\Uuid\UuidInterface;

class InvoiceTest extends TestCase
{
    public function testCreateInvoiceInDraftStatusWithoutProductLines(): void
    {
        $invoice = Invoice::create('John Doe', 'john@example.com');
        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertInstanceOf(UuidInterface::class, $invoice->getId());
        $this->assertSame(InvoiceStatus::DRAFT, $invoice->getStatus());
        $this->assertSame('John Doe', $invoice->getCustomerName());
        $this->assertSame('john@example.com', $invoice->getCustomerEmail());
        $this->assertNotTrue($invoice->hasProductLines());
    }
}
