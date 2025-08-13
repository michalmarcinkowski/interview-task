<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\ValueObjects;

use Webmozart\Assert\Assert;

final readonly class UnitPrice
{
    private function __construct(
        private int $value
    ) {}

    public static function fromInteger(int $value): self
    {
        Assert::positiveInteger($value, 'Unit price must be a positive integer.');

        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(UnitPrice $other): bool
    {
        return $this->value === $other->value;
    }
}
