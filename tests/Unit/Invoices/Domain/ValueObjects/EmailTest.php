<?php

declare(strict_types=1);

namespace Tests\Unit\Invoices\Domain\ValueObjects;

use Modules\Invoices\Domain\ValueObjects\Email;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

class EmailTest extends TestCase
{
    #[DataProvider('validEmailsProvider')]
    public function test_from_string_with_valid_emails(string $validEmail): void
    {
        $email = Email::fromString($validEmail);

        $this->assertInstanceOf(Email::class, $email);
        $this->assertSame($validEmail, $email->value());
    }

    #[DataProvider('invalidEmailsProvider')]
    public function test_from_string_with_invalid_emails(string $invalidEmail): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a value to be a valid e-mail address. Got: "'.$invalidEmail.'"');

        Email::fromString($invalidEmail);
    }

    public function test_value_and_to_string_methods(): void
    {
        $email = Email::fromString('test@example.com');

        $this->assertSame('test@example.com', $email->value());
        $this->assertSame('test@example.com', (string) $email);
    }

    #[DataProvider('equalsProvider')]
    public function test_equals(string $email1, string $email2, bool $expectedResult): void
    {
        $firstEmail = Email::fromString($email1);
        $secondEmail = Email::fromString($email2);

        $this->assertSame($expectedResult, $firstEmail->equals($secondEmail));
        $this->assertSame($expectedResult, $secondEmail->equals($firstEmail));
    }

    #[DataProvider('normalizationProvider')]
    public function test_normalization(string $inputEmail, string $expectedNormalizedEmail): void
    {
        $email = Email::fromString($inputEmail);

        $this->assertSame($expectedNormalizedEmail, $email->value());
    }

    public static function validEmailsProvider(): array
    {
        return [
            'simple email' => ['test@example.com'],
            'email with subdomain' => ['user@subdomain.example.com'],
            'email with plus' => ['user+tag@example.com'],
            'email with dots' => ['user.name@example.com'],
            'email with numbers' => ['user123@example.com'],
            'email with underscore' => ['user_name@example.com'],
            'email with dash' => ['user-name@example.com'],
            'email with multiple dots in domain' => ['user@example.co.uk'],
        ];
    }

    public static function invalidEmailsProvider(): array
    {
        return [
            'empty string' => [''],
            'missing @' => ['testexample.com'],
            'missing domain' => ['test@'],
            'missing local part' => ['@example.com'],
            'multiple @' => ['test@@example.com'],
            'invalid characters' => ['test@example@.com'],
            'space in email' => ['test @example.com'],
            'space in domain' => ['test@example .com'],
            'invalid domain format' => ['test@.com'],
            'invalid domain format 2' => ['test@example.'],
            'just text' => ['notanemail'],
        ];
    }

    public static function normalizationProvider(): array
    {
        return [
            'uppercase to lowercase' => ['TEST@EXAMPLE.COM', 'test@example.com'],
            'mixed case to lowercase' => ['Test@Example.com', 'test@example.com'],
            'trim whitespace' => ['  test@example.com  ', 'test@example.com'],
            'trim left whitespace' => ['  test@example.com', 'test@example.com'],
            'trim right whitespace' => ['test@example.com  ', 'test@example.com'],
            'mixed case and whitespace' => ['  Test@Example.com  ', 'test@example.com'],
        ];
    }

    public static function equalsProvider(): array
    {
        return [
            'same email' => ['test@example.com', 'test@example.com', true],
            'different emails' => ['test@example.com', 'other@example.com', false],
            'same email different case' => ['test@example.com', 'TEST@EXAMPLE.COM', true],
            'same email with whitespace' => ['test@example.com', '  test@example.com  ', true],
            'different domains' => ['user@domain1.com', 'user@domain2.com', false],
            'different local parts' => ['user1@example.com', 'user2@example.com', false],
        ];
    }
}
