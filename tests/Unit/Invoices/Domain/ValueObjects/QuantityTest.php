<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Domain\ValueObjects;

use Modules\Invoices\Domain\ValueObjects\Quantity;
use Tests\TestCase;
use Webmozart\Assert\InvalidArgumentException;

class QuantityTest extends TestCase
{
    public function test_should_create_quantity_with_valid_value(): void
    {
        $quantity = Quantity::fromInteger(5);
        $this->assertEquals(5, $quantity->value());
    }

    public function test_should_throw_exception_for_zero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be a positive integer.');

        Quantity::fromInteger(0);
    }

    public function test_should_throw_exception_for_negative_value(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be a positive integer.');

        Quantity::fromInteger(-1);
    }

    public function test_should_be_equal_with_same_value(): void
    {
        $quantity1 = Quantity::fromInteger(5);
        $quantity2 = Quantity::fromInteger(5);

        $this->assertTrue($quantity1->equals($quantity2));
    }

    public function test_should_not_be_equal_with_different_values(): void
    {
        $quantity1 = Quantity::fromInteger(5);
        $quantity2 = Quantity::fromInteger(10);

        $this->assertFalse($quantity1->equals($quantity2));
    }

    public function test_should_handle_large_numbers(): void
    {
        $largeQuantity = Quantity::fromInteger(999999999);
        $this->assertEquals(999999999, $largeQuantity->value());
    }
}
