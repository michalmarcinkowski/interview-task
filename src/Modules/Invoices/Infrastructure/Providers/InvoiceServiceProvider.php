<?php

declare(strict_types=1);

namespace Modules\Invoices\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Invoices\Domain\Repositories\InvoiceRepositoryInterface;
use Modules\Invoices\Infrastructure\Persistence\Eloquent\Repositories\InvoiceRepository;

class InvoiceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InvoiceRepositoryInterface::class, InvoiceRepository::class);
    }
}
