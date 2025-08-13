<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Domain\ValueObjects;

use Modules\Invoices\Domain\ValueObjects\UnitPrice;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

class UnitPriceTest extends TestCase
{
    public function testShouldCreateUnitPriceWithValidValue(): void
    {
        $unitPrice = UnitPrice::fromInteger(100);
        $this->assertEquals(100, $unitPrice->value());
    }

    public function testShouldThrowExceptionForZeroValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unit price must be a positive integer.');
        UnitPrice::fromInteger(0);
    }

    public function testShouldThrowExceptionForNegativeValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unit price must be a positive integer.');
        UnitPrice::fromInteger(-100);
    }

    public function testShouldReturnTrueForEqualUnitPrices(): void
    {
        $unitPrice1 = UnitPrice::fromInteger(100);
        $unitPrice2 = UnitPrice::fromInteger(100);
        $this->assertTrue($unitPrice1->equals($unitPrice2));
    }

    public function testShouldReturnFalseForDifferentUnitPrices(): void
    {
        $unitPrice1 = UnitPrice::fromInteger(100);
        $unitPrice2 = UnitPrice::fromInteger(200);
        $this->assertFalse($unitPrice1->equals($unitPrice2));
    }

    public function testShouldHandleLargeNumbers(): void
    {
        $largeUnitPrice = UnitPrice::fromInteger(999999999);
        $this->assertEquals(999999999, $largeUnitPrice->value());
    }
}
