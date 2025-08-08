<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Invoices\Presentation\Http\InvoiceController;

Route::post('/invoices', [InvoiceController::class, 'create'])->name('invoices.create');
