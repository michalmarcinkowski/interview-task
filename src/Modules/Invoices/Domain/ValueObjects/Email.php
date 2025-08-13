<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\ValueObjects;

use Webmozart\Assert\Assert;

/**
 * Email Value Object
 *
 * This class represents an email address as a value object in the domain model.
 * Value objects are immutable, meaning once created, their state cannot be changed.
 * This ensures data integrity and prevents accidental modifications to email addresses.
 *
 * Key Design Decisions:
 * 1. Immutability: Using 'readonly' class to prevent state changes after construction
 * 2. Validation: Email format validation using Assert library for input sanitization
 * 3. Normalization: Converting to lowercase and trimming whitespace for consistency
 * 4. Type Safety: Strong typing with PHP 8.2+ features
 * 5. Domain Logic: Encapsulates email-specific business rules and validation
 */
final readonly class Email
{
    /**
     * Private constructor ensures email objects can only be created through
     * the static factory method 'fromString', which provides validation and normalization.
     *
     * @param  string  $value  The normalized email address
     */
    private function __construct(
        private string $value
    ) {}

    /**
     * Factory method to create an Email value object from a string.
     *
     * This method implements the following business rules:
     * - Trims whitespace from input to handle user input inconsistencies
     * - Converts to lowercase for case-insensitive email comparison
     * - Validates email format using Assert library
     * - Returns a new immutable Email instance
     *
     * Rationale for normalization:
     * - Emails are case-insensitive according to RFC 5321
     * - Trimming prevents issues with accidental spaces
     * - Consistent format enables reliable equality comparison
     *
     * @param  string  $email  The raw email string to validate and normalize
     * @return self A new Email value object
     *
     * @throws \InvalidArgumentException If email format is invalid
     */
    public static function fromString(string $email): self
    {
        $normalizedEmail = strtolower(trim($email));
        Assert::email($normalizedEmail);

        return new self($normalizedEmail);
    }

    /**
     * Returns the normalized email address as a string.
     *
     * This method provides controlled access to the internal value.
     * The returned string is guaranteed to be normalized (lowercase, trimmed).
     *
     * @return string The normalized email address
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Compares this email with another email for equality.
     *
     * Value objects should implement proper equality comparison based on their values,
     * not their identity. Two Email objects are equal if they represent the same
     * email address (case-insensitive comparison is handled by normalization).
     *
     * @param  Email  $other  The email to compare with
     * @return bool True if both emails represent the same address
     */
    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * String representation of the email address.
     *
     * This method allows the Email object to be used in string contexts
     * (e.g., concatenation, output, logging) while maintaining encapsulation.
     *
     * @return string The normalized email address
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
