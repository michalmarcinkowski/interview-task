<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Domain\Models;

use PHPUnit\Framework\TestCase;
use Modules\Invoices\Domain\Models\InvoiceProductLine;
use Modules\Invoices\Domain\ValueObjects\Quantity;
use Modules\Invoices\Domain\ValueObjects\UnitPrice;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\InvalidArgumentException;

class InvoiceProductLineTest extends TestCase
{
    public function testShouldCreateInvoiceProductLineWithValidData(): void
    {
        $productLine = InvoiceProductLine::create(
            'Test Product',
            Quantity::fromInteger(2),
            UnitPrice::fromInteger(100)
        );
        
        $this->assertInstanceOf(UuidInterface::class, $productLine->getId());
        $this->assertEquals('Test Product', $productLine->getProductName());
        $this->assertEquals(2, $productLine->getQuantity()->value());
        $this->assertEquals(100, $productLine->getUnitPrice()->value());
        $this->assertEquals(200, $productLine->getTotalUnitPrice()); // 2 * 100
    }

    public function testShouldReconstituteInvoiceProductLineWithExistingId(): void
    {
        $id = Uuid::uuid4();
        $productLine = InvoiceProductLine::reconstitute(
            $id,
            'Reconstituted Product',
            Quantity::fromInteger(3),
            UnitPrice::fromInteger(150)
        );
        
        $this->assertEquals($id, $productLine->getId());
        $this->assertEquals('Reconstituted Product', $productLine->getProductName());
        $this->assertEquals(3, $productLine->getQuantity()->value());
        $this->assertEquals(150, $productLine->getUnitPrice()->value());
        $this->assertEquals(450, $productLine->getTotalUnitPrice()); // 3 * 150
    }

    public function testShouldCalculateTotalUnitPriceCorrectly(): void
    {
        $productLine = InvoiceProductLine::create(
            'Test Product',
            Quantity::fromInteger(5),
            UnitPrice::fromInteger(75)
        );
        
        $this->assertEquals(375, $productLine->getTotalUnitPrice()); // 5 * 75
    }

    public function testShouldCalculateTotalUnitPriceWithLargeNumbers(): void
    {
        $productLine = InvoiceProductLine::create(
            'Expensive Product',
            Quantity::fromInteger(1),
            UnitPrice::fromInteger(999999)
        );
        
        $this->assertEquals(999999, $productLine->getTotalUnitPrice());
    }

    public function testShouldCalculateTotalUnitPriceWithQuantityOne(): void
    {
        $productLine = InvoiceProductLine::create(
            'Single Item',
            Quantity::fromInteger(1),
            UnitPrice::fromInteger(250)
        );
        
        $this->assertEquals(250, $productLine->getTotalUnitPrice());
    }

    public function testShouldThrowExceptionForEmptyProductName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        
        InvoiceProductLine::create(
            '',
            Quantity::fromInteger(1),
            UnitPrice::fromInteger(100)
        );
    }

    public function testShouldAllowWhitespaceOnlyProductName(): void
    {
        // Assert::stringNotEmpty only checks for empty strings, not whitespace-only strings
        $productLine = InvoiceProductLine::create(
            '   ', // Whitespace-only product name
            Quantity::fromInteger(1),
            UnitPrice::fromInteger(100)
        );
        
        $this->assertInstanceOf(InvoiceProductLine::class, $productLine);
        $this->assertEquals('   ', $productLine->getProductName());
    }

    public function testShouldHandleSpecialCharactersInProductName(): void
    {
        $productLine = InvoiceProductLine::create(
            'Product with Special Chars: !@#$%^&*()',
            Quantity::fromInteger(1),
            UnitPrice::fromInteger(100)
        );
        
        $this->assertEquals('Product with Special Chars: !@#$%^&*()', $productLine->getProductName());
    }

    public function testShouldHandleLongProductName(): void
    {
        $longName = str_repeat('A', 255); // Maximum reasonable length
        $productLine = InvoiceProductLine::create(
            $longName,
            Quantity::fromInteger(1),
            UnitPrice::fromInteger(100)
        );
        
        $this->assertEquals($longName, $productLine->getProductName());
    }

    public function testShouldReturnCorrectValueObjects(): void
    {
        $quantity = Quantity::fromInteger(4);
        $unitPrice = UnitPrice::fromInteger(125);
        
        $productLine = InvoiceProductLine::create(
            'Test Product',
            $quantity,
            $unitPrice
        );
        
        $this->assertSame($quantity, $productLine->getQuantity());
        $this->assertSame($unitPrice, $productLine->getUnitPrice());
    }

    public function testShouldGenerateUniqueIdsForDifferentInstances(): void
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
        
        $this->assertNotEquals($productLine1->getId(), $productLine2->getId());
    }
}
