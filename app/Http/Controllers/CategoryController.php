<?php
// app/Http/Controllers/CategoryController.php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session; // Importez Session
use Illuminate\Validation\Rule;
use Illuminate\Support\Str; // Importez Str

class CategoryController extends Controller
{
    public function index()
    {
        $companyId = Session::get('active_company_id');
        // Redirige si aucune entreprise n'est sélectionnée (devrait être géré par middleware aussi)
        if (!$companyId) {
            return redirect()->route('companies.index')->with('error', 'Veuillez sélectionner une entreprise.');
        }

        $categories = Category::where('company_id', $companyId)->get(); // Filtrer par company_id
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        $companyId = Session::get('active_company_id');
        if (!$companyId) {
            return redirect()->route('companies.index')->with('error', 'Veuillez sélectionner une entreprise avant de créer une catégorie.');
        }
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $companyId = Session::get('active_company_id');
        if (!$companyId) {
            return redirect()->back()->withErrors('Veuillez sélectionner une entreprise.');
        }

        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                // S'assurer que le nom est unique pour cette entreprise
                Rule::unique('categories')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('categories')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
        ]);

        // Générer le slug si non fourni ou vide
        if (empty($validatedData['slug'])) {
            $validatedData['slug'] = Str::slug($validatedData['name']);
        }

        // Assurez-vous que le slug est unique pour l'entreprise, même après génération
        $originalSlug = $validatedData['slug'];
        $count = 0;
        do {
            $slugToCheck = $count > 0 ? $originalSlug . '-' . $count : $originalSlug;
            $exists = Category::where('company_id', $companyId)
                              ->where('slug', $slugToCheck)
                              ->exists();
            if (!$exists) {
                $validatedData['slug'] = $slugToCheck;
                break;
            }
            $count++;
        } while ($exists);

        Category::create(array_merge($validatedData, ['company_id' => $companyId])); // Associer à company_id

        return redirect()->route('categories.index')->with('success', 'Catégorie créée avec succès!');
    }

    public function show(Category $category)
    {
        // Vérifier que la catégorie appartient à l'entreprise active de l'utilisateur
        if ($category->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à cette catégorie.');
        }
        return view('categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        // Vérifier que la catégorie appartient à l'entreprise active de l'utilisateur
        if ($category->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à modifier cette catégorie.');
        }
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        // Vérifier que la catégorie appartient à l'entreprise active de l'utilisateur
        if ($category->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à mettre à jour cette catégorie.');
        }

        $companyId = Session::get('active_company_id');
        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })->ignore($category->id),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('categories')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })->ignore($category->id),
            ],
        ]);

        // Générer le slug si non fourni ou vide
        if (empty($validatedData['slug'])) {
            $validatedData['slug'] = Str::slug($validatedData['name']);
        }

        // Assurez-vous que le slug est unique pour l'entreprise, même après génération
        $originalSlug = $validatedData['slug'];
        $count = 0;
        do {
            $slugToCheck = $count > 0 ? $originalSlug . '-' . $count : $originalSlug;
            $exists = Category::where('company_id', $companyId)
                              ->where('slug', $slugToCheck)
                              ->where('id', '!=', $category->id) // Exclure la catégorie actuelle lors de la vérification de l'unicité
                              ->exists();
            if (!$exists) {
                $validatedData['slug'] = $slugToCheck;
                break;
            }
            $count++;
        } while ($exists);

        $category->update($validatedData);

        return redirect()->route('categories.index')->with('success', 'Catégorie mise à jour avec succès!');
    }

    public function destroy(Category $category)
    {
        // Vérifier que la catégorie appartient à l'entreprise active de l'utilisateur
        if ($category->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à supprimer cette catégorie.');
        }
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Catégorie supprimée avec succès!');
    }
}
