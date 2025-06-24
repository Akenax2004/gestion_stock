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
use App\Http\Controllers\LicenceController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Company;

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

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    // Routes pour la gestion des licences (accessibles par l'admin_principal)
    Route::get('/choose-license', [LicenceController::class, 'chooseLicense'])->name('choose-license');
    Route::post('/process-purchase', [LicenceController::class, 'processPurchase'])->name('process-purchase');

    // Les routes de gestion des entreprises (index, create, store)
    // Ces routes sont destinées à l'admin_principal et gèrent la création/liste des entreprises de l'utilisateur.
    Route::get('/companies', [CompanyController::class, 'index'])->name('manage.companies.index');
    Route::get('/companies/create', [CompanyController::class, 'create'])->name('manage.companies.create');
    Route::post('/companies', [CompanyController::class, 'store'])->name('manage.companies.store');

    // Route pour sélectionner une entreprise - DOIT ÊTRE ICI pour fonctionner avant 'company.selected'
    Route::get('/companies/{company}/select', [CompanyController::class, 'select'])->name('companies.select');

    // Le dashboard par défaut de Laravel (après connexion)
    // Agit comme un point d'entrée qui redirige selon le statut de l'utilisateur
    Route::get('/dashboard', function () {
        $user = Auth::user();
        if ($user->isAdminPrincipal()) {
            // Si admin principal n'a pas de licence, rediriger vers la page de choix de licence
            if (!$user->licence || !$user->licence->isActive()) {
                return redirect()->route('choose-license');
            }
            // Si admin principal a une licence active, mais pas d'entreprise sélectionnée en session
            // ou si l'entreprise en session n'est plus valide/n'appartient pas à l'utilisateur
            if (!Session::has('active_company_id') || !Session::get('active_company_id') ||
                !Company::where('id', Session::get('active_company_id'))->where('user_id', $user->id)->exists()) {
                return redirect()->route('manage.companies.index'); // Redirige vers la liste des entreprises
            }
            // Si tout est bon (licence active ET entreprise sélectionnée),
            // rediriger vers le VRAI tableau de bord de l'entreprise
            return redirect()->route('company.dashboard');
        }
        // Pour les utilisateurs secondaires, ils seront redirigés par le middleware 'company.selected'
        // vers leur dashboard d'entreprise (via la route 'company.dashboard')
        return redirect()->route('company.dashboard');
    })->name('dashboard');

    // Routes de profil utilisateur (accessibles par tous les utilisateurs authentifiés)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/settings', [ProfileController::class, 'settings'])->name('profile.settings');


    // ---------- DÉBUT DES ROUTES NÉCESSITANT UNE LICENCE ACTIVE ET UNE ENTREPRISE SÉLECTIONNÉE ----------
    // Ces routes sont protégées par le middleware 'has.license' (vérif licence)
    // ET par le middleware 'company.selected' (vérif entreprise sélectionnée et attache l'objet Company à la requête)
    Route::middleware(['has.license', 'company.selected'])->group(function () {

        // Le VRAI tableau de bord de l'entreprise (où DashboardController@index est appelé)
        Route::get('/company-dashboard', [DashboardController::class, 'index'])->name('company.dashboard');


        // CRUD pour les entreprises (show, edit, update, delete)
        // Seuls l'admin principal devrait avoir accès à ces routes
        Route::middleware(['can:manage-companies'])->group(function () {
            Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('manage.companies.show');
            Route::get('/companies/{company}/edit', [CompanyController::class, 'edit'])->name('manage.companies.edit');
            Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('manage.companies.update');
            Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->name('manage.companies.destroy');
        });


        // User Management (Protégé par une permission)
        // Seuls l'admin principal peut gérer les utilisateurs de l'entreprise
        Route::middleware(['can:manage-users-per-company'])->group(function () {
            Route::resource('/users', UserController::class); // Le UserController gère déjà la logique admin_principal
            Route::put('/user/change-password/{username}', [UserController::class, 'updatePassword'])->name('users.updatePassword');
        });


        // Quotidien
        Route::resource('/quotations', QuotationController::class)->middleware(['can:manage-quotations']);
        // Clients et Fournisseurs sous 'Pages' dans votre navbar
        Route::resource('/customers', CustomerController::class)->middleware(['can:manage-customers']);
        Route::resource('/suppliers', SupplierController::class)->middleware(['can:manage-suppliers']);


        // Stock & Produits
        // Permissions pour créer les produits (formulaire et soumission)
        Route::middleware(['can:create-product'])->group(function () {
            Route::get('/products/create', [ProductController::class, 'create'])->name('products.create'); // La route manquante !
            Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        });
        // Permission pour voir les produits (index et show)
        Route::middleware(['can:view-products'])->group(function () {
            Route::get('/products', [ProductController::class, 'index'])->name('products.index');
            Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
        });
        // Permissions pour modifier les produits
        Route::middleware(['can:edit-product'])->group(function () {
            Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
            Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        });
        // Permissions pour supprimer les produits
        Route::middleware(['can:delete-product'])->group(function () {
            Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.delete');
        });
        // Permissions pour l'import/export de produits (gestion du stock)
        Route::middleware(['can:manage-stock-in'])->group(function () {
             Route::get('/products/import', [ProductImportController::class, 'create'])->name('products.import.view');
             Route::post('/products/import', [ProductImportController::class, 'store'])->name('products.import.store');
        });
        Route::middleware(['can:manage-stock-out'])->group(function () {
             Route::get('/products/export', [ProductExportController::class, 'create'])->name('products.export.store');
        });

        // Commandes (Ventes)
        // Permission pour créer des commandes
        Route::middleware(['can:create-order'])->group(function () {
            Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
            Route::post('/orders/store', [OrderController::class, 'store'])->name('orders.store');
            Route::post('/invoice/create', [InvoiceController::class, 'create'])->name('invoice.create'); // Création de facture liée à la commande
        });
        // Permissions pour voir les commandes
        Route::middleware(['can:view-orders'])->group(function () {
            Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/pending', OrderPendingController::class)->name('orders.pending');
            Route::get('/orders/complete', OrderCompleteController::class)->name('orders.complete');
            Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
            Route::get('/due/orders/', [DueOrderController::class, 'index'])->name('due.index');
            Route::get('/due/order/view/{order}', [DueOrderController::class, 'show'])->name('due.show');
            Route::get('/orders/details/{order_id}/download', [OrderController::class, 'downloadInvoice'])->name('order.downloadInvoice');
        });
        // Permissions pour modifier les commandes
        Route::middleware(['can:edit-order'])->group(function () {
            Route::put('/orders/update/{order}', [OrderController::class, 'update'])->name('orders.update');
            Route::get('/due/order/edit/{order}', [DueOrderController::class, 'edit'])->name('due.edit');
            Route::put('/due/order/update/{order}', [DueOrderController::class, 'update'])->name('due.update');
        });

        // Achats
        Route::middleware(['can:manage-purchases'])->group(function () {
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
        });


        // Paramètres (Catégories et Unités)
        Route::middleware(['can:manage-categories'])->group(function () {
            Route::resource('/categories', CategoryController::class);
        });
        Route::middleware(['can:manage-units'])->group(function () {
            Route::resource('/units', UnitController::class);
        });

    });
});


// Route pour le webhook de paiement (peut être accessible sans authentification si c'est un webhook externe)
Route::post('/licences/webhook/{provider}', [LicenceController::class, 'handleWebhook'])->name('licences.webhook');

Route::get('test/', function (){
    return view('orders.create');
});

Route::get('propos', function () {
    return view('propos');
})->name('propos');
