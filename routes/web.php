<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VatRateController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ContractorController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SettingsController;

use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::resource('vat_rates', VatRateController::class);
    Route::resource('currencies', CurrencyController::class);
    Route::resource('products', ProductController::class);
    Route::post('contractors/fetch-gus', [ContractorController::class, 'fetchGus'])->name('contractors.fetch_gus');
    Route::post('contractors/check-vat-registry', [ContractorController::class, 'checkVatRegistry'])->name('contractors.check_vat_registry');
    Route::resource('contractors', ContractorController::class);
    Route::resource('invoices', InvoiceController::class);
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
    Route::get('invoices/{invoice}/xml', [InvoiceController::class, 'downloadXml'])->name('invoices.xml');
    Route::post('invoices/{invoice}/send-to-ksef', [InvoiceController::class, 'sendToKsef'])->name('invoices.send_to_ksef');

    // Purchase Invoices
    Route::get('purchase-invoices', [App\Http\Controllers\PurchaseInvoiceController::class, 'index'])->name('purchase_invoices.index');
    Route::post('purchase-invoices/fetch', [App\Http\Controllers\PurchaseInvoiceController::class, 'fetch'])->name('purchase_invoices.fetch');
    Route::get('purchase-invoices/{invoice}', [App\Http\Controllers\PurchaseInvoiceController::class, 'show'])->name('purchase_invoices.show');
    Route::get('purchase-invoices/{invoice}/pdf', [App\Http\Controllers\PurchaseInvoiceController::class, 'downloadPdf'])->name('purchase_invoices.pdf');
    Route::get('purchase-invoices/{invoice}/xml', [App\Http\Controllers\PurchaseInvoiceController::class, 'downloadXml'])->name('purchase_invoices.xml');

    // Recurring Invoices
    Route::resource('recurring_invoices', App\Http\Controllers\RecurringInvoiceController::class);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User Management
    Route::resource('users', \App\Http\Controllers\UserController::class);

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
});

// Language Switcher
Route::get('lang/{lang}', [\App\Http\Controllers\LanguageController::class, 'switch'])->name('lang.switch');

require __DIR__.'/auth.php';
