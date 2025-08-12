<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\ValueObjects;

use Webmozart\Assert\Assert;

final readonly class Quantity
{
    private function __construct(
        private int $value
    ) {}

    public static function fromInteger(int $value): self
    {
        Assert::positiveInteger($value, 'Quantity must be a positive integer.');
        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(Quantity $other): bool
    {
        return $this->value === $other->value;
    }
}
