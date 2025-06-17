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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session; // NOUVEAU : Importez la façade Session

class ProductController extends Controller
{
    /**
     * Affiche une liste de tous les produits de l'utilisateur connecté et de l'entreprise active.
     */
    public function index()
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        // Redirige si aucune entreprise n'est sélectionnée
        if (!$activeCompanyId) {
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise pour gérer les produits.');
        }

        // Récupère tous les produits appartenant à l'utilisateur connecté ET à l'entreprise active
        $products = Product::where('user_id', $userId)
                           ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                           ->get();

        return view('products.index', [
            'products' => $products,
        ]);
    }

    /**
     * Affiche le formulaire de création d'un nouveau produit.
     * Les catégories et unités listées sont également filtrées par l'utilisateur connecté et l'entreprise active.
     */
    public function create(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        // Redirige si aucune entreprise n'est sélectionnée
        if (!$activeCompanyId) {
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise avant de créer un produit.');
        }

        // Récupère les catégories appartenant à l'utilisateur connecté ET à l'entreprise active
        $categories = Category::where('user_id', $userId)
                                ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                                ->get(['id', 'name']);
        // Récupère les unités appartenant à l'utilisateur connecté ET à l'entreprise active
        $units = Unit::where('user_id', $userId)
                      ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                      ->get(['id', 'name']);

        // Les filtres existants par 'category' ou 'unit' dans la requête GET
        if ($request->has('category')) {
            $categories = Category::where('user_id', $userId)
                                  ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                                  ->whereSlug($request->get('category'))
                                  ->get();
        }

        if ($request->has('unit')) {
            $units = Unit::where('user_id', $userId)
                          ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                          ->whereSlug($request->get('unit'))
                          ->get();
        }

        return view('products.create', [
            'categories' => $categories,
            'units' => $units,
        ]);
    }

    /**
     * Stocke un nouveau produit dans la base de données, l'associant à l'utilisateur connecté et à l'entreprise active.
     */
    public function store(StoreProductRequest $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        // Redirige si aucune entreprise n'est sélectionnée
        if (!$activeCompanyId) {
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise avant de créer un produit.');
        }

        $code = $request->get('code');

        // Vérification de sécurité : Assurer que la catégorie et l'unité soumises appartiennent bien à l'utilisateur ET à l'entreprise active
        $category = null;
        if ($request->has('category_id') && $request->category_id) {
            $category = Category::where('id', $request->category_id)
                                ->where('user_id', $userId)
                                ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                                ->first();
            if (!$category) {
                return back()->withErrors(['category_id' => 'La catégorie sélectionnée n\'existe pas ou ne vous appartient pas / n\'appartient pas à l\'entreprise active.']);
            }
        }

        $unit = Unit::where('id', $request->unit_id)
                     ->where('user_id', $userId)
                     ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                     ->first();
        if (!$unit) {
            return back()->withErrors(['unit_id' => 'L\'unité sélectionnée n\'existe pas ou ne vous appartient pas / n\'appartient pas à l\'entreprise active.']);
        }

        // Si le code du produit existe déjà pour cet utilisateur et cette entreprise, générer un nouveau code unique
        $existingProduct = Product::where('code', $code)
                                  ->where('user_id', $userId)
                                  ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                                  ->first();
        if ($existingProduct) {
            $newCode = $this->generateUniqueCode($userId, $activeCompanyId); // Passer l'ID utilisateur ET l'ID entreprise
            $request->merge(['code' => $newCode]);
        }
        
        try {
            // Fusionne les données validées avec l'ID de l'utilisateur, l'ID de l'entreprise active et les IDs de catégorie/unité vérifiés
            $productData = array_merge($request->validated(), [
                'user_id' => $userId, // Associe le produit à l'utilisateur connecté
                'company_id' => $activeCompanyId, // AJOUT : Associe le produit à l'entreprise active
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
            \Log::error($e->getMessage() . " - " . $e->getTraceAsString()); // Log de l'erreur
            return back()->withErrors(['error' => 'Une erreur est survenue lors de la création du produit.']);
        }
    }

    /**
     * Méthode d'aide pour générer un code produit unique pour un utilisateur et une entreprise donnés.
     */
    private function generateUniqueCode($userId, $companyId) // NOUVEAU : prend companyId en paramètre
    {
        do {
            $code = 'PC' . strtoupper(uniqid());
            // Vérifie l'unicité du code UNIQUEMENT pour les produits de cet utilisateur ET de cette entreprise
        } while (Product::where('code', $code)->where('user_id', $userId)->where('company_id', $companyId)->exists());

        return $code;
    }

    /**
     * Affiche les détails d'un produit spécifique.
     * S'assure que seul le propriétaire et l'entreprise active peuvent le voir.
     */
    public function show(Product $product)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du produit ET s'il appartient à l'entreprise active.
        if (!Auth::check() || $product->user_id !== Auth::id() || $product->company_id !== Session::get('active_company_id')) {
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
     * S'assure que seul le propriétaire et l'entreprise active peuvent le modifier.
     */
    public function edit(Product $product)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du produit ET s'il appartient à l'entreprise active.
        if (!Auth::check() || $product->user_id !== Auth::id() || $product->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à modifier ce produit.');
        }

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        // Récupère les catégories et unités appartenant à l'utilisateur ET à l'entreprise active
        return view('products.edit', [
            'categories' => Category::where('user_id', $userId)
                                     ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                                     ->get(),
            'units' => Unit::where('user_id', $userId)
                            ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                            ->get(),
            'product' => $product
        ]);
    }

    /**
     * Met à jour un produit existant.
     * S'assure que seul le propriétaire et l'entreprise active peuvent le modifier.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du produit ET s'il appartient à l'entreprise active.
        if (!Auth::check() || $product->user_id !== Auth::id() || $product->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à mettre à jour ce produit.');
        }

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        // Vérification de sécurité pour la catégorie et l'unité : doivent appartenir à l'utilisateur ET à l'entreprise active
        $category = null;
        if ($request->has('category_id') && $request->category_id) {
            $category = Category::where('id', $request->category_id)
                                ->where('user_id', $userId)
                                ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                                ->first();
            if (!$category) {
                return back()->withErrors(['category_id' => 'La catégorie sélectionnée n\'existe pas ou ne vous appartient pas / n\'appartient pas à l\'entreprise active.']);
            }
        }

        $unit = Unit::where('id', $request->unit_id)
                     ->where('user_id', $userId)
                     ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                     ->first();
        if (!$unit) {
            return back()->withErrors(['unit_id' => 'L\'unité sélectionnée n\'existe pas ou ne vous appartient pas / n\'appartient pas à l\'entreprise active.']);
        }

        // Gérer la validation 'unique' pour le code produit: il doit être unique par entreprise.
        // C'est souvent géré dans le FormRequest (UpdateProductRequest), mais si non, il faudrait le faire ici
        // $request->validate([
        //     'code' => 'nullable|string|unique:products,code,'.$product->id.',id,company_id,'.$activeCompanyId,
        // ]);

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
        } elseif ($request->input('product_image_removed')) { // Gère la suppression explicite de l'image
            if ($product->product_image) {
                Storage::disk('public')->delete('products/' . $product->product_image);
                $product->update(['product_image' => null]);
            }
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'Le produit a été mis à jour avec succès!');
    }

    /**
     * Supprime un produit.
     * S'assure que seul le propriétaire et l'entreprise active peuvent le supprimer.
     */
    public function destroy(Product $product)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du produit ET s'il appartient à l'entreprise active.
        if (!Auth::check() || $product->user_id !== Auth::id() || $product->company_id !== Session::get('active_company_id')) {
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
