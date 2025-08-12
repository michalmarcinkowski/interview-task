<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Domain\ValueObjects;

use PHPUnit\Framework\TestCase;
use Modules\Invoices\Domain\ValueObjects\ProductLines;
use Modules\Invoices\Domain\ValueObjects\Quantity;
use Modules\Invoices\Domain\ValueObjects\UnitPrice;
use Modules\Invoices\Domain\Models\InvoiceProductLine;

class ProductLinesTest extends TestCase
{
    public function testShouldCreateEmptyProductLines(): void
    {
        $productLines = ProductLines::empty();
        
        $this->assertTrue($productLines->isEmpty());
        $this->assertFalse($productLines->isNotEmpty());
        $this->assertEquals(0, $productLines->count());
        $this->assertEmpty($productLines->toArray());
    }

    public function testShouldCreateProductLinesFromArray(): void
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

    public function testShouldThrowExceptionForInvalidItems(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All productLines must be InvoiceProductLine instances');
        
        ProductLines::fromArray(['invalid item']);
    }

    public function testShouldBeEqualWithSameItems(): void
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

    public function testShouldNotBeEqualWithDifferentItems(): void
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

    public function testShouldNotBeEqualWithDifferentCounts(): void
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

    public function testShouldHandleEmptyArraysCorrectly(): void
    {
        $productLines = ProductLines::fromArray([]);
        
        $this->assertTrue($productLines->isEmpty());
        $this->assertFalse($productLines->isNotEmpty());
        $this->assertEquals(0, $productLines->count());
        $this->assertEmpty($productLines->toArray());
    }
}
