<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Domain\Models;

use Modules\Invoices\Domain\Models\InvoiceProductLine;
use Modules\Invoices\Domain\ValueObjects\Quantity;
use Modules\Invoices\Domain\ValueObjects\UnitPrice;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\InvalidArgumentException;

class InvoiceProductLineTest extends TestCase
{
    public function test_should_create_invoice_product_line_with_valid_data(): void
    {
        $productLine = InvoiceProductLine::create(
            'Test Product',
            Quantity::fromInteger(2),
            UnitPrice::fromInteger(100)
        );

        $this->assertProductLineBasics($productLine, 'Test Product', 2, 100, 200);
    }

    public function test_should_reconstitute_invoice_product_line_with_existing_id(): void
    {
        $id = Uuid::uuid4();
        $productLine = InvoiceProductLine::reconstitute(
            $id,
            'Reconstituted Product',
            Quantity::fromInteger(3),
            UnitPrice::fromInteger(150)
        );

        $this->assertProductLineBasics($productLine, 'Reconstituted Product', 3, 150, 450);
        $this->assertEquals($id, $productLine->getId());
    }

    public function test_should_calculate_total_unit_price_correctly(): void
    {
        $productLine = InvoiceProductLine::create(
            'Test Product',
            Quantity::fromInteger(5),
            UnitPrice::fromInteger(75)
        );

        $this->assertEquals(375, $productLine->getTotalUnitPrice()); // 5 * 75
    }

    public function test_should_calculate_total_unit_price_with_large_numbers(): void
    {
        $productLine = InvoiceProductLine::create(
            'Expensive Product',
            Quantity::fromInteger(999999),
            UnitPrice::fromInteger(99999999)
        );

        $this->assertEquals(99999899000001, $productLine->getTotalUnitPrice());
    }

    public function test_should_calculate_total_unit_price_with_quantity_one(): void
    {
        $productLine = InvoiceProductLine::create(
            'Single Item',
            Quantity::fromInteger(1),
            UnitPrice::fromInteger(250)
        );

        $this->assertEquals(250, $productLine->getTotalUnitPrice());
    }

    public function test_should_throw_exception_for_empty_product_name(): void
    {
        $this->expectException(InvalidArgumentException::class);

        InvoiceProductLine::create(
            '',
            Quantity::fromInteger(1),
            UnitPrice::fromInteger(100)
        );
    }

    public function test_should_throw_exception_for_whitespace_only_product_name(): void
    {
        $this->expectException(\Webmozart\Assert\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product name must not be empty or only whitespace');

        InvoiceProductLine::create(
            '   ', // Whitespace-only product name
            Quantity::fromInteger(1),
            UnitPrice::fromInteger(100)
        );
    }

    public function test_should_handle_long_product_name(): void
    {
        $longName = str_repeat('A', 255); // Maximum reasonable length
        $productLine = InvoiceProductLine::create(
            $longName,
            Quantity::fromInteger(1),
            UnitPrice::fromInteger(100)
        );

        $this->assertEquals($longName, $productLine->getProductName());
    }

    public function test_should_return_correct_value_objects(): void
    {
        $quantity = Quantity::fromInteger(4);
        $unitPrice = UnitPrice::fromInteger(125);

        $productLine = InvoiceProductLine::create(
            'Test Product',
            $quantity,
            $unitPrice
        );

        $this->assertValueObjectsAreCorrect($productLine, $quantity, $unitPrice);
    }

    public function test_should_generate_unique_ids_for_different_instances(): void
    {
        $productLine1 = InvoiceProductLine::create(
            'Product 1',
            Quantity::fromInteger(1),
            UnitPrice::fromInteger(100)
        );

        $productLine2 = InvoiceProductLine::create(
            'Product 2',
            Quantity::fromInteger(1),
            UnitPrice::fromInteger(100)
        );

        $this->assertProductLinesHaveUniqueIds($productLine1, $productLine2);
    }

    /**
     * Assert basic product line properties including calculated total
     */
    private function assertProductLineBasics(
        InvoiceProductLine $productLine,
        string $expectedProductName,
        int $expectedQuantity,
        int $expectedUnitPrice,
        int $expectedTotalUnitPrice
    ): void {
        $this->assertInstanceOf(UuidInterface::class, $productLine->getId());
        $this->assertEquals($expectedProductName, $productLine->getProductName());
        $this->assertEquals($expectedQuantity, $productLine->getQuantity()->value());
        $this->assertEquals($expectedUnitPrice, $productLine->getUnitPrice()->value());
        $this->assertEquals($expectedTotalUnitPrice, $productLine->getTotalUnitPrice());
    }

    /**
     * Assert that value objects are correctly stored and retrieved
     */
    private function assertValueObjectsAreCorrect(
        InvoiceProductLine $productLine,
        Quantity $expectedQuantity,
        UnitPrice $expectedUnitPrice
    ): void {
        $this->assertSame($expectedQuantity, $productLine->getQuantity());
        $this->assertSame($expectedUnitPrice, $productLine->getUnitPrice());
    }

    /**
     * Assert two product lines have unique IDs
     */
    private function assertProductLinesHaveUniqueIds(InvoiceProductLine $productLine1, InvoiceProductLine $productLine2): void
    {
        $this->assertNotEquals($productLine1->getId(), $productLine2->getId());
    }
}
