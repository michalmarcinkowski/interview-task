<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Domain\ValueObjects;

use Modules\Invoices\Domain\ValueObjects\UnitPrice;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

class UnitPriceTest extends TestCase
{
    public function test_should_create_unit_price_with_valid_value(): void
    {
        $unitPrice = UnitPrice::fromInteger(100);
        $this->assertEquals(100, $unitPrice->value());
    }

    public function test_should_throw_exception_for_zero_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unit price must be a positive integer.');
        UnitPrice::fromInteger(0);
    }

    public function test_should_throw_exception_for_negative_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unit price must be a positive integer.');
        UnitPrice::fromInteger(-100);
    }

    public function test_should_return_true_for_equal_unit_prices(): void
    {
        $unitPrice1 = UnitPrice::fromInteger(100);
        $unitPrice2 = UnitPrice::fromInteger(100);
        $this->assertTrue($unitPrice1->equals($unitPrice2));
    }

    public function test_should_return_false_for_different_unit_prices(): void
    {
        $unitPrice1 = UnitPrice::fromInteger(100);
        $unitPrice2 = UnitPrice::fromInteger(200);
        $this->assertFalse($unitPrice1->equals($unitPrice2));
    }

    public function test_should_handle_large_numbers(): void
    {
        $largeUnitPrice = UnitPrice::fromInteger(999999999);
        $this->assertEquals(999999999, $largeUnitPrice->value());
    }
}
