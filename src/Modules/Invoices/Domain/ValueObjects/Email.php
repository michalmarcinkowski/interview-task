<?php

namespace Modules\Invoices\Domain\ValueObjects;

use Webmozart\Assert\Assert;

final readonly class Email
{
    private function __construct(
        private string $value
    ) {}

    public static function fromString(string $email): self
    {
        $normalizedEmail = strtolower(trim($email));
        Assert::email($normalizedEmail);

        return new self($normalizedEmail);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
