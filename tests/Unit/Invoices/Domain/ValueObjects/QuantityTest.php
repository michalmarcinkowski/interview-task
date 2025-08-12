<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Domain\ValueObjects;

use Tests\TestCase;
use Modules\Invoices\Domain\ValueObjects\Quantity;
use Webmozart\Assert\InvalidArgumentException;

class QuantityTest extends TestCase
{
    public function testShouldCreateQuantityWithValidValue(): void
    {
        $quantity = Quantity::fromInteger(5);
        $this->assertEquals(5, $quantity->value());
    }

    public function testShouldThrowExceptionForZero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be a positive integer.');

        Quantity::fromInteger(0);
    }

    public function testShouldThrowExceptionForNegativeValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be a positive integer.');

        Quantity::fromInteger(-1);
    }

    public function testShouldBeEqualWithSameValue(): void
    {
        $quantity1 = Quantity::fromInteger(5);
        $quantity2 = Quantity::fromInteger(5);

        $this->assertTrue($quantity1->equals($quantity2));
    }

    public function testShouldNotBeEqualWithDifferentValues(): void
    {
        $quantity1 = Quantity::fromInteger(5);
        $quantity2 = Quantity::fromInteger(10);

        $this->assertFalse($quantity1->equals($quantity2));
    }

    public function testShouldHandleLargeNumbers(): void
    {
        $largeQuantity = Quantity::fromInteger(999999999);
        $this->assertEquals(999999999, $largeQuantity->value());
    }
}
