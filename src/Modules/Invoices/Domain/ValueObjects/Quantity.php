<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\ValueObjects;

use Webmozart\Assert\Assert;

/**
 * Quantity Value Object
 *
 * Represents a product quantity in the domain model.
 * Ensures quantities are always positive integers.
 *
 * ## Design Decisions
 *
 * ### 1. Final Readonly Class
 * - **Why**: Value objects are immutable by definition
 * - **Benefit**: Prevents accidental modification, ensures data integrity
 *
 * ### 2. Positive Integer Constraint
 * - **Why**: Business rule - quantities cannot be zero or negative
 * - **Benefit**: Prevents invalid business states
 */
final readonly class Quantity
{
    private function __construct(
        private int $value
    ) {}

    /**
     * Creates a valid Quantity from an integer.
     *
     * @param  int  $value  The quantity value
     * @return self A new Quantity value object
     *
     * @throws \InvalidArgumentException When value is not positive
     */
    public static function fromInteger(int $value): self
    {
        Assert::positiveInteger($value, 'Quantity must be a positive integer.');

        return new self($value);
    }

    /**
     * Returns the underlying integer value.
     *
     * @return int The quantity value
     */
    public function value(): int
    {
        return $this->value;
    }

    /**
     * Compares this quantity with another for equality.
     *
     * @param  Quantity  $other  The quantity to compare with
     * @return bool True if both quantities have the same value
     */
    public function equals(Quantity $other): bool
    {
        return $this->value === $other->value;
    }
}
