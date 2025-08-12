<?php

declare(strict_types=1);

namespace Modules\Invoices\Domain\ValueObjects;

use Modules\Invoices\Domain\Models\InvoiceProductLine;

final readonly class ProductLines
{
    /**
     * @param InvoiceProductLine[] $productLines
     */
    private function __construct(
        private array $productLines
    ) {}

    public static function fromArray(array $productLines): self
    {
        foreach ($productLines as $productLine) {
            if (!$productLine instanceof InvoiceProductLine) {
                throw new \InvalidArgumentException('All productLines must be InvoiceProductLine instances');
            }
        }

        return new self($productLines);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    /**
     * @return InvoiceProductLine[]
     */
    public function toArray(): array
    {
        return $this->productLines;
    }

    public function count(): int
    {
        return count($this->productLines);
    }

    public function isEmpty(): bool
    {
        return empty($this->productLines);
    }

    public function isNotEmpty(): bool
    {
        return !empty($this->productLines);
    }

    public function equals(ProductLines $other): bool
    {
        if ($this->count() !== $other->count()) {
            return false;
        }

        foreach ($this->productLines as $index => $item) {
            if (!$item->equals($other->productLines[$index])) {
                return false;
            }
        }

        return true;
    }
}
