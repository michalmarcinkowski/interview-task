<?php

namespace Modules\Invoices\Domain\Models;

use Modules\Invoices\Domain\Enums\InvoiceStatus;
use Modules\Invoices\Domain\ValueObjects\CustomerData;
use Modules\Invoices\Domain\ValueObjects\InvoiceProductLine;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Invoice
{
    private UuidInterface $id;

    private InvoiceStatus $status;

    private CustomerData $customerData;

    /** @var InvoiceProductLine[] */
    private array $productLines = [];

    private function __construct(UuidInterface $id, InvoiceStatus $status, CustomerData $customerData, array $productLines = [])
    {
        $this->id = $id;
        $this->status = $status;
        $this->customerData = $customerData;
        $this->productLines = $productLines;
    }

    public static function create(string $customerName, string $customerEmail): self
    {
        $customerData = CustomerData::of($customerName, $customerEmail);

        return new self(Uuid::uuid4(), InvoiceStatus::DRAFT, $customerData);
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getStatus(): InvoiceStatus
    {
        return $this->status;
    }

    public function getCustomerName(): string
    {
        return $this->customerData->getName();
    }

    public function getCustomerEmail(): string
    {
        return $this->customerData->getEmail();
    }

    public function hasProductLines()
    {
        return count($this->productLines) > 0;
    }
}
