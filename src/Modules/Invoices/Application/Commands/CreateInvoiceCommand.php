<?php

declare(strict_types=1);

namespace Modules\Invoices\Application\Commands;

use Webmozart\Assert\Assert;

/**
 * Create Invoice Command
 *
 * Represents the command to create a new invoice in the application layer.
 * This class encapsulates all the data and validation rules needed to create
 * an invoice, following the Command pattern from DDD.
 *
 * ## Design Decisions and Reasoning
 *
 * ### 1. Final Class
 * - **Why**: Commands represent specific actions and shouldn't be extended
 * - **Benefit**: Prevents inheritance abuse and maintains design intent
 * - **DDD Principle**: Commands are value objects that represent intent
 *
 * ### 2. Readonly Properties
 * - **Why**: Commands are immutable - once created, they represent a specific action
 * - **Benefit**: Prevents accidental modification during command execution
 * - **DDD Principle**: Commands should be immutable to maintain integrity
 *
 * ### 3. Public Readonly Properties
 * - **Why**: Commands need to be read by application services
 * - **Benefit**: No need for getter methods, direct property access
 * - **DDD Principle**: Commands are data carriers, not behavior containers
 *
 * ### 4. Validation in Constructor
 * - **Why**: Fail-fast approach - invalid commands can't exist
 * - **Benefit**: Prevents invalid data from reaching the domain layer
 * - **Alternative Considered**: No validation in commands just in domain layer
 * - **Rejection Reason**: Would require checking validity before every use
 *
 * ### 5. Assert Library Usage
 * - **Why**: Provides clear, descriptive error messages
 * - **Benefit**: Better debugging and user experience
 * - **Alternative Considered**: Custom validation logic
 * - **Rejection Reason**: More maintenance, less standardized
 *
 * ## Architecture Context
 *
 * This command is part of the Application layer and follows the Command pattern
 * representing action that can change the state of the system.
 *
 * The command flows: Controller → Command → Application Service → Domain
 */
final readonly class CreateInvoiceCommand
{
    /**
     * Private constructor ensures validation runs before object creation.
     * This prevents invalid command objects from existing in the system.
     *
     * @param  string  $customerName  The customer's full name
     * @param  string  $customerEmail  The customer's email address
     * @param  array  $productLines  Array of product line data (optional)
     */
    private function __construct(
        public readonly string $customerName,
        public readonly string $customerEmail,
        public readonly array $productLines = [],
    ) {
        $this->validate();
    }

    /**
     * Static factory method for creating valid command instances.
     *
     * This method provides a clean API for command creation while ensuring
     * all validation rules are enforced. The factory pattern here serves
     * as a validation gateway.
     *
     * @param  string  $customerName  The customer's full name
     * @param  string  $customerEmail  The customer's email address
     * @param  array  $productLines  Array of product line data (default: empty)
     * @return self A validated command instance
     *
     * @throws \InvalidArgumentException When validation fails
     */
    public static function fromValues(string $customerName, string $customerEmail, array $productLines = []): self
    {
        return new self($customerName, $customerEmail, $productLines);
    }

    /**
     * Validates all command data according to business rules.
     *
     * This method implements fail-fast validation, ensuring that:
     * - Required fields are present and valid
     * - Business rules are enforced (e.g., positive quantities)
     * - Data structure is correct for downstream processing
     *
     * Validation happens at construction time, guaranteeing that
     * all command instances in the system are valid.
     *
     * @throws \InvalidArgumentException When any validation rule is violated
     */
    private function validate(): void
    {
        Assert::notEmpty($this->customerName, 'Customer name cannot be empty.');
        Assert::email($this->customerEmail, 'Customer email is not a valid email address.');

        foreach ($this->productLines as $index => $lineData) {
            Assert::keyExists($lineData, 'productName', "Product line at index {$index} is missing productName.");
            Assert::keyExists($lineData, 'quantity', "Product line at index {$index} is missing quantity.");
            Assert::keyExists($lineData, 'unitPrice', "Product line at index {$index} is missing unitPrice.");

            Assert::string($lineData['productName'], "Product name at index {$index} must be a string.");
            Assert::notEmpty($lineData['productName'], "Product name at index {$index} cannot be empty.");

            Assert::positiveInteger($lineData['quantity'], "Quantity at index {$index} must be a positive integer.");

            Assert::positiveInteger($lineData['unitPrice'], "Unit price at index {$index} must be a positive integer.");
        }
    }
}
