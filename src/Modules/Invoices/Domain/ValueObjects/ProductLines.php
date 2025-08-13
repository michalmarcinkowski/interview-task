<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\ValueObjects;

use Modules\Invoices\Domain\Models\InvoiceProductLine;

/**
 * Product Lines Value Object
 *
 * Represents a collection of InvoiceProductLine entities as a value object.
 * This class encapsulates the business logic for managing product line collections
 * within the Invoice aggregate, following DDD value object principles.
 *
 * ## Design Decisions and Reasoning
 *
 * ### 1. Final Readonly Class
 * - **Why**: Value objects are immutable by definition - they represent a concept, not an entity
 * - **Benefit**: Prevents accidental modification and ensures data integrity
 * - **DDD Principle**: Value objects should be immutable to maintain consistency
 * - **Alternative Considered**: Mutable collection with add/remove methods
 * - **Rejection Reason**: No need for mutable collection in this case, add/remove can be added later if needed
 *
 * ### 2. Private Constructor with Static Factories
 * - **Why**: Ensures validation and business rules are enforced during creation
 * - **Benefit**: Guarantees that all ProductLines instances are valid
 * - **Alternative Considered**: Public constructor with post-creation validation
 * - **Rejection Reason**: Could lead to invalid ProductLines objects in the system
 *
 * ### 3. Simple Collection Operations
 * - **Why**: Focus on essential operations needed by the domain
 * - **Benefit**: Keeps the class focused and easy to understand
 * - **Alternative Considered**: Rich collection API with filtering, mapping, etc.
 * - **Rejection Reason**: Current domain needs don't require complex operations
 *
 * ## Architecture Context
 *
 * This value object is part of the Domain layer and serves as:
 * - **Aggregate Component**: Part of the Invoice aggregate
 * - **Value Object**: Immutable representation of product line collection
 * - **Domain Logic**: Encapsulates business rules about product line collections
 *
 * ## Business Rules
 *
 * - Product lines can be empty (invoices can exist without products)
 * - All product lines must be valid InvoiceProductLine instances
 * - The collection is immutable once created
 *
 * ## Future Considerations
 *
 * This class could evolve to support:
 * - **Rich Collection API**: add(), remove(), filter() methods
 * - **Business Logic**: total calculation, validation rules (max number of product lines)
 *
 * @see Invoice
 * @see InvoiceProductLine
 */
final readonly class ProductLines
{
    /**
     * Private constructor ensures validation and business rules are enforced.
     *
     * @param  InvoiceProductLine[]  $productLines  Array of product line entities
     */
    private function __construct(
        private array $productLines
    ) {}

    /**
     * Creates ProductLines from an array of InvoiceProductLine instances.
     *
     * This factory method validates that all elements are proper InvoiceProductLine
     * instances before creating the value object. This ensures type safety and
     * maintains domain model integrity.
     *
     * @param  InvoiceProductLine[]  $productLines  Array of product line entities
     * @return self A new ProductLines value object
     *
     * @throws \InvalidArgumentException When any element is not an InvoiceProductLine
     */
    public static function fromArray(array $productLines): self
    {
        foreach ($productLines as $productLine) {
            if (! $productLine instanceof InvoiceProductLine) {
                throw new \InvalidArgumentException('All productLines must be InvoiceProductLine instances');
            }
        }

        return new self($productLines);
    }

    /**
     * Creates an empty ProductLines collection.
     *
     * This factory method represents the business concept of "no product lines"
     * and is semantically clearer than ProductLines::fromArray([]).
     *
     * @return self An empty ProductLines value object
     */
    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * Returns the underlying array of product lines.
     *
     * This method provides access to the collection for iteration and processing.
     * The returned array is a copy, maintaining immutability of the value object.
     *
     * @return InvoiceProductLine[] Array of product line entities
     */
    public function toArray(): array
    {
        return $this->productLines;
    }

    /**
     * Returns the number of product lines in the collection.
     *
     * @return int The count of product lines
     */
    public function count(): int
    {
        return count($this->productLines);
    }

    /**
     * Checks if the collection contains no product lines.
     *
     * This method provides a semantic way to check for empty collections,
     * making the code more readable than checking count() == 0.
     *
     * @return bool True if the collection is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->productLines);
    }

    /**
     * Checks if the collection contains at least one product line.
     *
     * This method provides a semantic way to check for non-empty collections,
     * making the code more readable than checking !isEmpty().
     *
     * @return bool True if the collection is not empty
     */
    public function isNotEmpty(): bool
    {
        return ! empty($this->productLines);
    }
}
