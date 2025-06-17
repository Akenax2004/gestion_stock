<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGeneratorHTML;
use Illuminate\Support\Facades\Auth;    // Importez la façade Auth
use Illuminate\Support\Facades\Storage; // Importez la façade Storage pour la gestion des fichiers

class ProductController extends Controller
{
    /**
     * Affiche une liste de tous les produits de l'utilisateur connecté.
     */
    public function index()
    {
        // Vérifie si un utilisateur est authentifié
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();

        // Récupère tous les produits appartenant à l'utilisateur connecté
        $products = Product::where('user_id', $userId)->get(); // Supprimé limit(1) pour afficher tous les produits

        return view('products.index', [
            'products' => $products,
        ]);
    }

    /**
     * Affiche le formulaire de création d'un nouveau produit.
     * Les catégories et unités listées sont également filtrées par l'utilisateur connecté.
     */
    public function create(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();

        // Récupère les catégories appartenant à l'utilisateur connecté
        $categories = Category::where('user_id', $userId)->get(['id', 'name']);
        // Récupère les unités appartenant à l'utilisateur connecté
        $units = Unit::where('user_id', $userId)->get(['id', 'name']);

        if ($request->has('category')) {
            // Filtre par catégorie ET par utilisateur
            $categories = Category::where('user_id', $userId)
                                  ->whereSlug($request->get('category'))
                                  ->get();
        }

        if ($request->has('unit')) {
            // Filtre par unité ET par utilisateur
            $units = Unit::where('user_id', $userId)
                         ->whereSlug($request->get('unit'))
                         ->get();
        }

        return view('products.create', [
            'categories' => $categories,
            'units' => $units,
        ]);
    }

    /**
     * Stocke un nouveau produit dans la base de données, l'associant à l'utilisateur connecté.
     */
    public function store(StoreProductRequest $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();
        $code = $request->get('code');

        // Vérification de sécurité : Assurer que la catégorie et l'unité soumises appartiennent bien à l'utilisateur
        $category = null;
        if ($request->has('category_id') && $request->category_id) {
            $category = Category::where('id', $request->category_id)->where('user_id', $userId)->first();
            if (!$category) {
                return back()->withErrors(['category_id' => 'La catégorie sélectionnée n\'existe pas ou ne vous appartient pas.']);
            }
        }

        $unit = Unit::where('id', $request->unit_id)->where('user_id', $userId)->first();
        if (!$unit) {
            return back()->withErrors(['unit_id' => 'L\'unité sélectionnée n\'existe pas ou ne vous appartient pas.']);
        }

        // Si le code du produit existe déjà pour cet utilisateur, générer un nouveau code unique
        $existingProduct = Product::where('code', $code)->where('user_id', $userId)->first();
        if ($existingProduct) {
            $newCode = $this->generateUniqueCode($userId); // Passer l'ID utilisateur
            $request->merge(['code' => $newCode]);
        }
        
        try {
            // Fusionne les données validées avec l'ID de l'utilisateur et les IDs de catégorie/unité vérifiés
            $productData = array_merge($request->validated(), [
                'user_id' => $userId, // Associe le produit à l'utilisateur connecté
                'category_id' => $category ? $category->id : null, // Utilise l'ID de catégorie vérifié
                'unit_id' => $unit->id, // Utilise l'ID d'unité vérifié
            ]);

            $product = Product::create($productData);

            /**
             * Gère le téléchargement d'une image
             */
            if ($request->hasFile('product_image')) {
                $file = $request->file('product_image');
                $filename = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();

                // Valide le fichier avant le téléchargement
                if ($file->isValid()) {
                    $file->storeAs('products/', $filename, 'public');
                    $product->update([
                        'product_image' => $filename
                    ]);
                } else {
                    return back()->withErrors(['product_image' => 'Fichier image invalide.']);
                }
            }

            return redirect()
                ->back()
                ->with('success', 'Le produit a été créé avec le code : ' . $product->code);

        } catch (\Exception $e) {
            // Gérer les erreurs inattendues
            // Il est recommandé de logguer l'erreur : \Log::error($e->getMessage());
            return back()->withErrors(['error' => 'Une erreur est survenue lors de la création du produit.']);
        }
    }

    /**
     * Méthode d'aide pour générer un code produit unique pour un utilisateur donné.
     */
    private function generateUniqueCode($userId)
    {
        do {
            $code = 'PC' . strtoupper(uniqid());
            // Vérifie l'unicité du code UNIQUEMENT pour les produits de cet utilisateur
        } while (Product::where('code', $code)->where('user_id', $userId)->exists());

        return $code;
    }

    /**
     * Affiche les détails d'un produit spécifique.
     * S'assure que seul le propriétaire peut le voir.
     */
    public function show(Product $product)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du produit.
        if (!Auth::check() || $product->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à ce produit.');
        }

        // Génère un code-barres
        $generator = new BarcodeGeneratorHTML();
        $barcode = $generator->getBarcode($product->code, $generator::TYPE_CODE_128);

        return view('products.show', [
            'product' => $product,
            'barcode' => $barcode,
        ]);
    }

    /**
     * Affiche le formulaire de modification d'un produit.
     * S'assure que seul le propriétaire peut le modifier.
     */
    public function edit(Product $product)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du produit.
        if (!Auth::check() || $product->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à modifier ce produit.');
        }

        $userId = Auth::id();

        return view('products.edit', [
            'categories' => Category::where('user_id', $userId)->get(), // Filtrer par utilisateur
            'units' => Unit::where('user_id', $userId)->get(),         // Filtrer par utilisateur
            'product' => $product
        ]);
    }

    /**
     * Met à jour un produit existant.
     * S'assure que seul le propriétaire peut le modifier.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du produit.
        if (!Auth::check() || $product->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à mettre à jour ce produit.');
        }

        // Utilise $request->validated() pour mettre à jour les données validées
        $product->update($request->validated());

        if ($request->hasFile('product_image')) {
            // Supprime l'ancienne image si elle existe
            if ($product->product_image) {
                Storage::disk('public')->delete('products/' . $product->product_image);
            }

            // Prépare la nouvelle image
            $file = $request->file('product_image');
            $fileName = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();

            // Stocke la nouvelle image dans le stockage public
            $file->storeAs('products/', $fileName, 'public');

            // Enregistre le nom de la nouvelle image en base de données
            $product->update([
                'product_image' => $fileName
            ]);
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'Le produit a été mis à jour avec succès!');
    }

    /**
     * Supprime un produit.
     * S'assure que seul le propriétaire peut le supprimer.
     */
    public function destroy(Product $product)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du produit.
        if (!Auth::check() || $product->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à supprimer ce produit.');
        }

        /**
         * Supprime la photo si elle existe.
         */
        if ($product->product_image) {
            Storage::disk('public')->delete('products/' . $product->product_image);
        }

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Le produit a été supprimé avec succès!');
    }
}
