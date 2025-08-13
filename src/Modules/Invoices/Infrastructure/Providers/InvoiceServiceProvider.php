<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Invoices\Application\Factories\InvoiceFactory;
use Modules\Invoices\Application\Factories\InvoiceFactoryInterface;
use Modules\Invoices\Application\Services\InvoiceService;
use Modules\Invoices\Application\Services\InvoiceServiceInterface;
use Modules\Invoices\Domain\Repositories\InvoiceRepositoryInterface;
use Modules\Invoices\Infrastructure\Persistence\Eloquent\Repositories\InvoiceRepository;

class InvoiceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InvoiceRepositoryInterface::class, InvoiceRepository::class);
        $this->app->bind(InvoiceFactoryInterface::class, InvoiceFactory::class);
        $this->app->bind(InvoiceServiceInterface::class, InvoiceService::class);
    }
}
