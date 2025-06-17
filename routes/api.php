<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Ici, vous pouvez enregistrer les routes API pour votre application. Ces
| routes sont chargées par le RouteServiceProvider et toutes
| seront assignées au groupe de middleware "api". Faites quelque chose de génial !
|
*/

// Route pour l'utilisateur authentifié, généralement utilisée pour récupérer les détails de l'utilisateur
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Groupez toutes les routes des produits sous le middleware d'authentification Sanctum.
// Cela garantit que seul un utilisateur authentifié peut accéder à ces endpoints.
Route::middleware('auth:sanctum')->group(function () {
    // Utilisation de Route::apiResource pour gérer toutes les opérations CRUD
    // (index, store, show, update, destroy) pour les produits.
    // Chaque opération dans ProductController sera automatiquement protégée.
    Route::apiResource('products', ProductController::class);

    // Si vous aviez d'autres routes spécifiques qui nécessitent une authentification,
    // elles devraient également être placées ici.
    // Par exemple, si vous aviez une route spécifique pour importer les produits API :
    // Route::post('/products/api-import', [ProductImportApiController::class, 'store']);
});

// Si vous avez des routes API qui ne nécessitent PAS d'authentification,
// elles devraient être placées en dehors du groupe middleware 'auth:sanctum'.
// Par exemple: Route::get('public/products', [ProductController::class, 'publicIndex']);
