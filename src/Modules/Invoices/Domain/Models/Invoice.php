<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\Models;

use Modules\Invoices\Domain\Enums\InvoiceStatus;
use Modules\Invoices\Domain\ValueObjects\Email;
use Modules\Invoices\Domain\ValueObjects\ProductLines;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * Invoice Aggregate Root
 *
 * Represents an invoice in the domain model. This class serves as the aggregate root
 * for the Invoice bounded context, managing the lifecycle and business rules for
 * invoices and their associated product lines.
 *
 * ## Design Decisions and Reasoning
 *
 * ### 1. Final Class
 * - **Why**: Aggregate roots should not be extended to maintain design integrity
 * - **Benefit**: Prevents inheritance abuse and maintains the intended architecture
 * - **DDD Principle**: Aggregates are core business concepts that should be sealed
 *
 * ### 2. UUID as Primary Key
 * - **Why**: Provides globally unique identifiers for distributed systems
 * - **Benefit**: No coordination needed for ID generation, better for scalability
 * - **Alternative Considered**: Auto-incrementing integers
 * - **Rejection Reason**: Ties the domain to database implementation details
 *
 * ### 3. Value Objects for Complex Properties
 * - **Why**: Email and ProductLines encapsulate their own validation and business rules
 * - **Benefit**: Reusable, self-validating components with clear boundaries
 *
 * ### 4. ProductLines as Collection Value Object
 * - **Why**: Product lines form a cohesive collection with its own business rules
 * - **Benefit**: Encapsulates collection logic, enables complex operations, type safety
 *
 * ### 5. Status as Enum
 * - **Why**: Invoice status represents a finite set of valid states
 * - **Benefit**: Type safety, prevents invalid status values
 *
 * ## Architecture Context
 *
 * This class is part of the Domain layer and serves as:
 * - **Aggregate Root**: Manages the Invoice aggregate and its invariants
 * - **Entity**: Has identity and lifecycle management
 * - **Business Logic Container**: Encapsulates invoice-related business rules
 *
 * ## Business Rules
 *
 * - Invoices are always created in DRAFT status
 * - Invoices can exist without product lines
 * - Invoice totals are calculated from product line totals
 * - Invoice identity is immutable once created
 *
 * ## Future Considerations
 *
 * This class could evolve to support:
 * - **Status Transitions**: Business logic for changing invoice status
 * - **Mutability**: Changing the invoice details, add/remove product lines, etc.
 * - **Validation Rules**: Complex business rule validation
 * - **Audit Trail**: Tracking of invoice modifications
 */
final class Invoice
{
    /**
     * Private constructor ensures proper object construction and validation.
     *
     * This constructor is private to enforce the use of static factory methods,
     * which provide better control over object creation and ensure business
     * rules are followed.
     *
     * @param  UuidInterface  $id  The unique identifier for the invoice
     * @param  InvoiceStatus  $status  The current status of the invoice
     * @param  string  $customerName  The name of the customer
     * @param  Email  $customerEmail  The customer's email address
     * @param  ProductLines  $productLines  The collection of product lines
     */
    private function __construct(
        private UuidInterface $id,
        private InvoiceStatus $status,
        private string $customerName,
        private Email $customerEmail,
        private ProductLines $productLines
    ) {}

    /**
     * Factory method for creating new invoices.
     *
     * This method enforces the business rule that all new invoices start in
     * DRAFT status. It generates a new UUID and delegates to the private
     * constructor, ensuring proper object initialization.
     *
     * @param  string  $customerName  The customer's name
     * @param  Email  $customerEmail  The customer's email address
     * @param  ProductLines  $productLines  The product lines for the invoice
     * @return self A new Invoice instance in DRAFT status
     */
    public static function create(string $customerName, Email $customerEmail, ProductLines $productLines): self
    {
        return new self(
            Uuid::uuid4(),
            InvoiceStatus::DRAFT,
            $customerName,
            $customerEmail,
            $productLines,
        );
    }

    /**
     * Factory method for reconstituting invoices from persistence.
     *
     * This method should be used ONLY when loading existing invoices from
     * persistence layer. It allows the repository to reconstruct the domain
     * object with its saved state while maintaining encapsulation.
     * For other use cases use create() method.
     *
     * @param  UuidInterface  $id  The existing invoice ID
     * @param  InvoiceStatus  $status  The saved invoice status
     * @param  string  $customerName  The saved customer name
     * @param  Email  $customerEmail  The saved customer email
     * @param  ProductLines  $productLines  The saved product lines
     * @return self A reconstituted Invoice instance
     */
    public static function reconstitute(
        UuidInterface $id,
        InvoiceStatus $status,
        string $customerName,
        Email $customerEmail,
        ProductLines $productLines,
    ): self {
        return new self($id, $status, $customerName, $customerEmail, $productLines);
    }

    /**
     * Returns the unique identifier of the invoice.
     *
     * @return UuidInterface The invoice's unique identifier
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * Returns the current status of the invoice.
     *
     * @return InvoiceStatus The current invoice status
     */
    public function getStatus(): InvoiceStatus
    {
        return $this->status;
    }

    /**
     * Returns the customer's name.
     *
     * @return string The customer's name
     */
    public function getCustomerName(): string
    {
        return $this->customerName;
    }

    /**
     * Returns the customer's email address.
     *
     * @return Email The customer's email address
     */
    public function getCustomerEmail(): Email
    {
        return $this->customerEmail;
    }

    /**
     * Returns the collection of product lines.
     *
     * @return ProductLines Immutable product lines collection
     */
    public function getProductLines(): ProductLines
    {
        return $this->productLines;
    }

    /**
     * Checks if the invoice has any product lines.
     *
     * This method provides a semantic way to check for product lines,
     * making the code more readable than checking count() > 0.
     *
     * @return bool True if the invoice has product lines
     */
    public function hasProductLines(): bool
    {
        return $this->productLines->isNotEmpty();
    }

    /**
     * Calculates the total invoice amount.
     *
     * This method encapsulates the business logic for calculating invoice totals.
     * It sums up the total unit price of all product lines, returning 0 for
     * invoices without products. The calculation is performed using functional
     * programming principles for clarity and performance.
     *
     * Business Rule: Invoice total is the sum of all product line totals
     *
     * @return int The total invoice amount
     */
    public function getTotal(): int
    {
        return array_sum(array_map(fn (InvoiceProductLine $line) => $line->getTotalUnitPrice(), $this->productLines->toArray()));
    }

    /**
     * Checks if the invoice can be sent.
     *
     * Business Rules:
     * - Invoice must be in DRAFT status
     * - Invoice must have at least one product line
     * - All product lines must have positive quantity and unit price (already enforced in ProductLine)
     *
     * @return bool True if the invoice can be sent
     */
    public function canBeSent(): bool
    {
        if ($this->status !== InvoiceStatus::DRAFT) {
            return false;
        }

        if (! $this->hasProductLines()) {
            return false;
        }

        return true;
    }

    /**
     * Marks the invoice as sending.
     *
     * This method validates the status transition and changes the invoice status
     * from DRAFT to SENDING.
     *
     * @throws \InvalidArgumentException When invoice cannot be sent
     */
    public function markAsSending(): void
    {
        Assert::true(
            $this->canBeSent(),
            'Invoice cannot be sent. Make sure it fulfills the business rules.'
        );

        $this->status = InvoiceStatus::SENDING;
    }

    /**
     * Marks the invoice as sent to client.
     *
     * This method validates the status transition and changes the invoice status
     * from SENDING to SENT_TO_CLIENT.
     *
     * @throws \InvalidArgumentException When invoice cannot be marked as sent to client
     */
    public function markAsSentToClient(): void
    {
        Assert::true(
            $this->canBeMarkedAsSentToClient(),
            'Invoice cannot be marked as sent to client.'
        );

        $this->status = InvoiceStatus::SENT_TO_CLIENT;
    }

    /**
     * Checks if the invoice is already marked as sent to client.
     *
     * This method provides a semantic way to check if the invoice
     * has already been delivered to the client.
     *
     * @return bool True if the invoice is in SENT_TO_CLIENT status
     */
    public function isSentToClientAlready(): bool
    {
        return $this->status === InvoiceStatus::SENT_TO_CLIENT;
    }

    /**
     * Checks if the invoice can be marked as sent to client.
     *
     * This method validates the business rule that an invoice must be
     * in SENDING status to be marked as sent to client.
     *
     * @return bool True if the invoice can be marked as sent to client
     */
    public function canBeMarkedAsSentToClient(): bool
    {
        return $this->status === InvoiceStatus::SENDING;
    }
}
