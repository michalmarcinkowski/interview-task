<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Domain\ValueObjects;

use Modules\Invoices\Domain\Models\InvoiceProductLine;
use Modules\Invoices\Domain\ValueObjects\ProductLines;
use Modules\Invoices\Domain\ValueObjects\Quantity;
use Modules\Invoices\Domain\ValueObjects\UnitPrice;
use PHPUnit\Framework\TestCase;

class ProductLinesTest extends TestCase
{
    public function test_should_create_empty_product_lines(): void
    {
        $productLines = ProductLines::empty();

        $this->assertTrue($productLines->isEmpty());
        $this->assertFalse($productLines->isNotEmpty());
        $this->assertEquals(0, $productLines->count());
        $this->assertEmpty($productLines->toArray());
    }

    public function test_should_create_product_lines_from_array(): void
    {
        $items = [
            InvoiceProductLine::create(
                'Product 1',
                Quantity::fromInteger(2),
                UnitPrice::fromInteger(100)
            ),
            InvoiceProductLine::create(
                'Product 2',
                Quantity::fromInteger(3),
                UnitPrice::fromInteger(150)
            ),
        ];

        $productLines = ProductLines::fromArray($items);

        $this->assertFalse($productLines->isEmpty());
        $this->assertTrue($productLines->isNotEmpty());
        $this->assertEquals(2, $productLines->count());
        $this->assertCount(2, $productLines->toArray());
    }

    public function test_should_throw_exception_for_invalid_items(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All productLines must be InvoiceProductLine instances');

        ProductLines::fromArray(['invalid item']);
    }

    public function test_should_be_equal_with_same_items(): void
    {
        $items = [
            InvoiceProductLine::create(
                'Product 1',
                Quantity::fromInteger(2),
                UnitPrice::fromInteger(100)
            ),
        ];

        $productLines1 = ProductLines::fromArray($items);
        $productLines2 = ProductLines::fromArray($items);

        $this->assertTrue($productLines1->equals($productLines2));
    }

    public function test_should_not_be_equal_with_different_items(): void
    {
        $items1 = [
            InvoiceProductLine::create(
                'Product 1',
                Quantity::fromInteger(2),
                UnitPrice::fromInteger(100)
            ),
        ];

        $items2 = [
            InvoiceProductLine::create(
                'Product 2',
                Quantity::fromInteger(3),
                UnitPrice::fromInteger(150)
            ),
        ];

        $productLines1 = ProductLines::fromArray($items1);
        $productLines2 = ProductLines::fromArray($items2);

        $this->assertFalse($productLines1->equals($productLines2));
    }

    public function test_should_not_be_equal_with_different_counts(): void
    {
        $items1 = [
            InvoiceProductLine::create(
                'Product 1',
                Quantity::fromInteger(2),
                UnitPrice::fromInteger(100)
            ),
        ];

        $items2 = [
            InvoiceProductLine::create(
                'Product 1',
                Quantity::fromInteger(2),
                UnitPrice::fromInteger(100)
            ),
            InvoiceProductLine::create(
                'Product 2',
                Quantity::fromInteger(3),
                UnitPrice::fromInteger(150)
            ),
        ];

        $productLines1 = ProductLines::fromArray($items1);
        $productLines2 = ProductLines::fromArray($items2);

        $this->assertFalse($productLines1->equals($productLines2));
    }

    public function test_should_handle_empty_arrays_correctly(): void
    {
        $productLines = ProductLines::fromArray([]);

        $this->assertTrue($productLines->isEmpty());
        $this->assertFalse($productLines->isNotEmpty());
        $this->assertEquals(0, $productLines->count());
        $this->assertEmpty($productLines->toArray());
    }
}
