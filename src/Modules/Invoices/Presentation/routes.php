<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Invoices\Presentation\Http\InvoiceController;
use Ramsey\Uuid\Validator\GenericValidator;

Route::pattern('id', (new GenericValidator)->getPattern());

Route::post('/invoices', [InvoiceController::class, 'create'])->name('invoices.create');
Route::get('/invoices/{id}', [InvoiceController::class, 'view'])->name('invoices.view');
