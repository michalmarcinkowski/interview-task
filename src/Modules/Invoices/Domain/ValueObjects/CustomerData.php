<?php

namespace Modules\Invoices\Domain\ValueObjects;

use Webmozart\Assert\Assert;

final readonly class CustomerData
{
    private string $name;

    private string $email; // create Email VO

    private function __construct(string $name, string $email)
    {
        $this->name = $name;
        $this->email = $email;
    }

    public static function of(string $name, string $email): self
    {
        Assert::stringNotEmpty($name, 'Customer name must not be empty.'); // not explicitly defined in the requirements but
        Assert::email($email, 'Invalid customer email.'); // not explicitly defined in the requirements but

        return new self($name, $email);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
