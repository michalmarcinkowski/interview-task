<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\ValueObjects;

use Webmozart\Assert\Assert;

/**
 * Unit Price Value Object
 *
 * Represents a product unit price in the domain model.
 * Ensures prices are always positive integers.
 *
 * ## Design Decisions
 *
 * ### 1. Final Readonly Class
 * - **Why**: Value objects are immutable by definition
 * - **Benefit**: Prevents accidental modification, ensures data integrity
 *
 * ### 2. Positive Integer Constraint
 * - **Why**: Business rule - prices cannot be zero or negative
 * - **Benefit**: Prevents invalid business states
 *
 * ### 3. Integer vs Decimal
 * - **Why**: Simplified calculations, no floating-point precision issues
 * - **Benefit**: Predictable arithmetic, better performance
 * - **Alternative**: Use decimal/float for cents
 * - **Rejection**: Current business requirements don't need sub-unit precision
 */
final readonly class UnitPrice
{
    private function __construct(
        private int $value
    ) {}

    /**
     * Creates a valid UnitPrice from an integer.
     *
     * @param  int  $value  The unit price value
     * @return self A new UnitPrice value object
     *
     * @throws \InvalidArgumentException When value is not positive
     */
    public static function fromInteger(int $value): self
    {
        Assert::positiveInteger($value, 'Unit price must be a positive integer.');

        return new self($value);
    }

    /**
     * Returns the underlying integer value.
     *
     * @return int The unit price integer value
     */
    public function value(): int
    {
        return $this->value;
    }

    /**
     * Compares this unit price with another for equality.
     *
     * @param  UnitPrice  $other  The unit price to compare with
     * @return bool True if both unit prices have the same value
     */
    public function equals(UnitPrice $other): bool
    {
        return $this->value === $other->value;
    }
}
