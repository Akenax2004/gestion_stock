<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\DueOrderController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Purchase\PurchaseController;
use App\Http\Controllers\Order\OrderPendingController;
use App\Http\Controllers\Order\OrderCompleteController;
use App\Http\Controllers\Quotation\QuotationController;
use App\Http\Controllers\Dashboards\DashboardController;
use App\Http\Controllers\Product\ProductExportController;
use App\Http\Controllers\Product\ProductImportController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\LicenceController; // Importez le LicenceController

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('php/', function () {
    return phpinfo();
});

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    // Routes qui NE NÉCESSITENT PAS une entreprise sélectionnée (gestion des entreprises elles-mêmes)
    // Route::resource('companies', CompanyController::class); // Cette ligne peut être problématique car elle crée aussi show/edit/update/destroy
    // Mieux vaut définir les routes de manière plus explicite pour éviter les conflits si vous avez deux groupes.
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/create', [CompanyController::class, 'create'])->name('companies.create');
    Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');

    Route::get('/companies/{company}/select', [CompanyController::class, 'select'])->name('companies.select');

    // Routes pour la gestion des licences
    // Ces routes doivent être accessibles même si la licence est expirée pour permettre le paiement
    Route::get('/licences', [LicenceController::class, 'showLicencePlans'])->name('licences.show');
    Route::post('/licences/purchase', [LicenceController::class, 'processPurchase'])->name('licences.purchase');
    // Route pour le webhook de paiement (doit être accessible sans authentification si c'est un webhook externe)
    Route::post('/licences/webhook/{provider}', [LicenceController::class, 'handleWebhook'])->name('licences.webhook');

    // Toutes les routes ci-dessous nécessitent une entreprise active ET une licence valide
    Route::middleware(['company.selected', 'check.licence'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // CRUD pour les entreprises (si vous les protégez par la licence)
        Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
        Route::get('/companies/{company}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
        Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
        Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');

        // User Management
        Route::resource('/users', UserController::class); //->except(['show']);
        Route::put('/user/change-password/{username}', [UserController::class, 'updatePassword'])->name('users.updatePassword');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::get('/profile/settings', [ProfileController::class, 'settings'])->name('profile.settings');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::resource('/quotations', QuotationController::class);
        Route::resource('/customers', CustomerController::class);
        Route::resource('/suppliers', SupplierController::class);
        Route::resource('/categories', CategoryController::class);
        Route::resource('/units', UnitController::class);

        // Route Products
        Route::get('/products/import', [ProductImportController::class, 'create'])->name('products.import.view');
        Route::post('/products/import', [ProductImportController::class, 'store'])->name('products.import.store');
        Route::get('/products/export', [ProductExportController::class, 'create'])->name('products.export.store');
        Route::resource('/products', ProductController::class);

        // Route Orders
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/pending', OrderPendingController::class)->name('orders.pending');
        Route::get('/orders/complete', OrderCompleteController::class)->name('orders.complete');

        Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
        Route::post('/orders/store', [OrderController::class, 'store'])->name('orders.store');

        Route::post('/invoice/create', [InvoiceController::class, 'create'])->name('invoice.create');

        // SHOW ORDER
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::put('/orders/update/{order}', [OrderController::class, 'update'])->name('orders.update');

        // DUES
        Route::get('/due/orders/', [DueOrderController::class, 'index'])->name('due.index');
        Route::get('/due/order/view/{order}', [DueOrderController::class, 'show'])->name('due.show');
        Route::get('/due/order/edit/{order}', [DueOrderController::class, 'edit'])->name('due.edit');
        Route::put('/due/order/update/{order}', [DueOrderController::class, 'update'])->name('due.update');

        // TODO: Remove from OrderController (Cette ligne peut être retirée si vous avez déjà déplacé la logique)
        Route::get('/orders/details/{order_id}/download', [OrderController::class, 'downloadInvoice'])->name('order.downloadInvoice');

        // Route Purchases
        // CORRECTION ICI : Le nom de la route doit être 'purchases.approvedPurchases'
        Route::get('/purchases/approved', [PurchaseController::class, 'approvedPurchases'])->name('purchases.approvedPurchases');
        Route::get('/purchases/report', [PurchaseController::class, 'dailyPurchaseReport'])->name('purchases.dailyPurchaseReport');
        Route::get('/purchases/report/export', [PurchaseController::class, 'getPurchaseReport'])->name('purchases.getPurchaseReport');
        Route::post('/purchases/report/export', [PurchaseController::class, 'exportPurchaseReport'])->name('purchases.exportPurchaseReport');

        Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');

        Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
        Route::get('/purchases/{purchase}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit');
        Route::put('/purchases/{purchase}/edit', [PurchaseController::class, 'update'])->name('purchases.update');
        Route::delete('/purchases/{purchase}', [PurchaseController::class, 'destroy'])->name('purchases.delete');
    }); // FIN DU GROUPE MIDDLEWARE 'company.selected' et 'check.licence'
});

require __DIR__.'/auth.php';

Route::get('test/', function (){
    return view('orders.create');
});

Route::get('propos', function () {
    return view('propos');
})->name('propos');
