<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Domain\Models;

use Modules\Invoices\Domain\Enums\InvoiceStatus;
use Modules\Invoices\Domain\Models\Invoice;
use Modules\Invoices\Domain\Models\InvoiceProductLine;
use Modules\Invoices\Domain\ValueObjects\Email;
use Modules\Invoices\Domain\ValueObjects\ProductLines;
use Modules\Invoices\Domain\ValueObjects\Quantity;
use Modules\Invoices\Domain\ValueObjects\UnitPrice;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class InvoiceTest extends TestCase
{
    private const CUSTOMER_NAME = 'John Doe';

    private const CUSTOMER_EMAIL = 'john@example.com';

    public function test_should_create_invoice_in_draft_status_without_product_lines(): void
    {
        $invoice = $this->createEmptyInvoice();

        $this->assertInvoiceBasics($invoice);
        $this->assertFalse($invoice->hasProductLines());
    }

    public function test_should_return_zero_total_for_invoice_without_product_lines(): void
    {
        $invoice = $this->createEmptyInvoice();

        $this->assertEquals(0, $invoice->getTotal());
    }

    #[DataProvider('singleProductLineProvider')]
    public function test_should_calculate_total_for_single_product_line(int $quantity, int $unitPrice, int $expectedTotal): void
    {
        $invoice = $this->createInvoiceWithProductLine($quantity, $unitPrice);

        $this->assertEquals($expectedTotal, $invoice->getTotal());
    }

    public function test_should_calculate_total_for_multiple_product_lines(): void
    {
        $invoice = $this->createInvoiceWithMultipleProductLines();

        $this->assertEquals(500, $invoice->getTotal());
    }

    public function test_should_calculate_total_for_large_numbers(): void
    {
        $invoice = $this->createInvoiceWithProductLine(999, 999999);

        $this->assertEquals(998999001, $invoice->getTotal());
    }

    public function test_should_calculate_total_for_reconstituted_invoice(): void
    {
        $invoice = $this->createReconstitutedInvoice();

        $this->assertEquals(125, $invoice->getTotal());
    }

    //
    // Data Providers

    public static function singleProductLineProvider(): array
    {
        return [
            '2x100 = 200' => [2, 100, 200],
            '5x25 = 125' => [5, 25, 125],
            '1x1000 = 1000' => [1, 1000, 1000],
        ];
    }

    //
    // Helper Methods

    private function createEmptyInvoice(): Invoice
    {
        return Invoice::create(
            self::CUSTOMER_NAME,
            Email::fromString(self::CUSTOMER_EMAIL),
            ProductLines::empty()
        );
    }

    private function createInvoiceWithProductLine(int $quantity, int $unitPrice): Invoice
    {
        $productLine = InvoiceProductLine::create(
            'Test Product',
            Quantity::fromInteger($quantity),
            UnitPrice::fromInteger($unitPrice)
        );

        return Invoice::create(
            self::CUSTOMER_NAME,
            Email::fromString(self::CUSTOMER_EMAIL),
            ProductLines::fromArray([$productLine])
        );
    }

    private function createInvoiceWithMultipleProductLines(): Invoice
    {
        $productLines = [
            InvoiceProductLine::create('Product 1', Quantity::fromInteger(3), UnitPrice::fromInteger(50)),
            InvoiceProductLine::create('Product 2', Quantity::fromInteger(2), UnitPrice::fromInteger(75)),
            InvoiceProductLine::create('Product 3', Quantity::fromInteger(1), UnitPrice::fromInteger(200)),
        ];

        return Invoice::create(
            self::CUSTOMER_NAME,
            Email::fromString(self::CUSTOMER_EMAIL),
            ProductLines::fromArray($productLines)
        );
    }

    private function createReconstitutedInvoice(): Invoice
    {
        $productLine = InvoiceProductLine::create(
            'Product 1',
            Quantity::fromInteger(5),
            UnitPrice::fromInteger(25)
        );

        return Invoice::reconstitute(
            Uuid::uuid4(),
            InvoiceStatus::DRAFT,
            self::CUSTOMER_NAME,
            Email::fromString(self::CUSTOMER_EMAIL),
            ProductLines::fromArray([$productLine])
        );
    }

    /**
     * Asserts the common properties of a newly created invoice.
     * This helps avoid code duplication across multiple tests.
     */
    private function assertInvoiceBasics(Invoice $invoice): void
    {
        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertInstanceOf(UuidInterface::class, $invoice->getId());
        $this->assertSame(InvoiceStatus::DRAFT, $invoice->getStatus());
        $this->assertSame(self::CUSTOMER_NAME, $invoice->getCustomerName());
        $this->assertSame(self::CUSTOMER_EMAIL, $invoice->getCustomerEmail()->value());
    }
}
