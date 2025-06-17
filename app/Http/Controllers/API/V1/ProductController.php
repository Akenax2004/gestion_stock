<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Importez la façade Auth

class ProductController
{
    /**
     * Affiche une liste de produits, filtrés par l'utilisateur connecté
     * et optionnellement par category_id.
     */
    public function index(Request $request)
    {
        // Vérifie si un utilisateur est authentifié.
        // Si cette route est protégée par un middleware 'auth', cette vérification est redondante mais peut servir de garde-fou.
        if (!Auth::check()) {
            return response()->json(['message' => 'Non authentifié. Veuillez vous connecter pour voir les produits.'], 401);
        }

        // Récupère l'ID de l'utilisateur actuellement connecté.
        $userId = Auth::id(); // Auth::id() est un raccourci pour Auth::user()->id

        // Commence la requête pour les produits, en filtrant d'abord par l'ID de l'utilisateur connecté.
        $productsQuery = Product::where('user_id', $userId);

        // Si une category_id est présente dans la requête, ajoutez ce filtre.
        if ($request->has('category_id')) {
            $productsQuery->where('category_id', $request->get('category_id'));
        }

        // Exécute la requête et récupère les produits.
        $products = $productsQuery->get();

        // Retourne les produits au format JSON.
        return response()->json($products);
    }

    // Vous devrez également adapter d'autres méthodes (store, update, destroy)
    // pour s'assurer que les produits sont créés/modifiés/supprimés uniquement par l'utilisateur propriétaire.

    /**
     * Stocke un nouveau produit.
     * Assurez-vous d'associer le produit à l'utilisateur connecté.
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        // Valider la requête
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'code' => 'nullable|string|unique:products,code',
            // ... autres règles de validation
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            // ...
        ]);

        // Créer le produit et associer l'ID de l'utilisateur
        $product = Product::create(array_merge($validatedData, [
            'user_id' => Auth::id(), // Associe le produit à l'utilisateur connecté
        ]));

        return response()->json($product, 201); // 201 Created
    }

    /**
     * Affiche un produit spécifique.
     * Assurez-vous que seul l'utilisateur propriétaire peut le voir.
     */
    public function show(Product $product)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        // Vérifie si l'utilisateur connecté est le propriétaire du produit.
        if ($product->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé à voir ce produit.'], 403); // 403 Forbidden
        }

        return response()->json($product);
    }

    /**
     * Met à jour un produit existant.
     * Assurez-vous que seul l'utilisateur propriétaire peut le modifier.
     */
    public function update(Request $request, Product $product)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        // Vérifie si l'utilisateur connecté est le propriétaire du produit.
        if ($product->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé à modifier ce produit.'], 403);
        }

        // Valider la requête
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255',
            'code' => 'nullable|string|unique:products,code,' . $product->id, // Exclure l'ID actuel pour unique
            // ... autres règles de validation
        ]);

        $product->update($validatedData);

        return response()->json($product);
    }

    /**
     * Supprime un produit.
     * Assurez-vous que seul l'utilisateur propriétaire peut le supprimer.
     */
    public function destroy(Product $product)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        // Vérifie si l'utilisateur connecté est le propriétaire du produit.
        if ($product->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé à supprimer ce produit.'], 403);
        }

        $product->delete();

        return response()->json(null, 204); // 204 No Content
    }
}
