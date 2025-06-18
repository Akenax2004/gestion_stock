<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $companyId = Session::get('active_company_id');
        if (!$companyId) {
            return redirect()->route('companies.index')->with('error', 'Veuillez sélectionner une entreprise.')->withInput();
        }
        $products = Product::where('company_id', $companyId)->get();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        $companyId = Session::get('active_company_id');
        if (!$companyId) {
            return redirect()->route('companies.index')->with('error', 'Veuillez sélectionner une entreprise avant de créer un produit.')->withInput();
        }

        $categories = Category::where('company_id', $companyId)->get();
        $units = Unit::where('company_id', $companyId)->get();

        return view('products.create', compact('categories', 'units'));
    }

    public function store(Request $request)
    {
        $companyId = Session::get('active_company_id');
        if (!$companyId) {
            return redirect()->back()->withErrors('Veuillez sélectionner une entreprise.')->withInput();
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'buying_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'quantity_alert' => 'required|integer|min:0',
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'unit_id' => [
                'required',
                'integer',
                Rule::exists('units', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'tax' => 'nullable|numeric|min:0',
            'tax_type' => 'nullable|string',
            'notes' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        // --- DÉBUT DE LA LOGIQUE DE GÉNÉRATION DU CODE PRODUIT ---

        // Récupérer la catégorie sélectionnée
        $category = Category::findOrFail($validatedData['category_id']);

        // Générer un préfixe basé sur les DEUX premières lettres du slug de la catégorie
        // Convertir en majuscules pour le code
        $categoryPrefix = strtoupper(substr(Str::slug($category->name), 0, 2));
        if (empty($categoryPrefix)) {
            // Fallback si le slug est trop court ou vide (improbable pour des noms de catégorie valides)
            $categoryPrefix = 'PR'; // Préfixe par défaut si la catégorie ne fournit pas assez de lettres
        }

        // Trouver le dernier code produit existant avec ce préfixe dans la même entreprise
        // On utilise 'orderByDesc('code')' pour s'assurer que le numéro le plus élevé est pris
        $lastProduct = Product::where('company_id', $companyId)
                              ->where('code', 'like', $categoryPrefix . '-%')
                              ->orderByDesc('code')
                              ->first();

        $nextNumber = 1;
        if ($lastProduct) {
            // Extraire la partie numérique du code (ex: de 'EL-001' obtenir '001')
            $parts = explode('-', $lastProduct->code);
            // S'assurer qu'il y a une partie numérique et qu'elle est bien un nombre
            if (count($parts) > 1 && is_numeric(end($parts))) {
                $nextNumber = (int)end($parts) + 1;
            }
        }

        // Formater le prochain numéro avec des zéros en tête (ex: 001, 010, 123)
        // J'ai conservé 3 chiffres pour le suffixe numérique, mais vous pouvez ajuster
        $productCode = $categoryPrefix . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        // Ajouter le code généré aux données validées
        $validatedData['code'] = $productCode;

        // --- FIN DE LA LOGIQUE DE GÉNÉRATION DU CODE PRODUIT ---

        $product = Product::create(array_merge($validatedData, [
            'company_id' => $companyId, // Associe le produit à l'entreprise active
            'slug' => Str::slug($validatedData['name']), // Assigner le slug du produit (basé sur le nom du produit)
        ]));

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('products/images', $filename, 'public');
            $product->update(['product_image' => $filename]);
        }

        return redirect()->route('products.index')->with('success', 'Produit créé avec succès!');
    }

    public function show(Product $product)
    {
        if ($product->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à ce produit.');
        }
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        if ($product->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à modifier ce produit.');
        }

        $companyId = Session::get('active_company_id');
        $categories = Category::where('company_id', $companyId)->get();
        $units = Unit::where('company_id', $companyId)->get();

        return view('products.edit', compact('product', 'categories', 'units'));
    }

    public function update(Request $request, Product $product)
    {
        if ($product->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à mettre à jour ce produit.');
        }

        $companyId = Session::get('active_company_id');
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'buying_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'quantity_alert' => 'required|integer|min:0',
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'unit_id' => [
                'required',
                'integer',
                Rule::exists('units', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'tax' => 'nullable|numeric|min:0',
            'tax_type' => 'nullable|string',
            'notes' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            // Le code du produit n'est généralement pas modifiable après la création s'il est généré.
            // Si vous souhaitez le rendre modifiable, ajoutez une validation ici,
            // potentiellement avec une règle unique qui ignore l'ID actuel du produit.
            // 'code' => ['required', 'string', Rule::unique('products')->ignore($product->id)->where(function ($query) use ($companyId) {
            //     return $query->where('company_id', $companyId);
            // })],
        ]);

        // Génération du slug du produit à partir du nouveau nom (si le nom a changé)
        if ($request->input('name') !== $product->name) {
            $slug = Str::slug($validatedData['name']);
            $validatedData['slug'] = $slug;
        }

        $product->update($validatedData);

        if ($request->hasFile('image')) {
            if ($product->product_image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete('products/images/' . $product->product_image);
            }
            $file = $request->file('image');
            $filename = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('products/images', $filename, 'public');
            $product->update(['product_image' => $filename]);
        }

        return redirect()->route('products.index')->with('success', 'Produit mis à jour avec succès!');
    }

    public function destroy(Product $product)
    {
        if ($product->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à supprimer ce produit.');
        }

        if ($product->product_image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete('products/images/' . $product->product_image);
        }
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Produit supprimé avec succès!');
    }
}