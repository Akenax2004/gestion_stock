<?php
// app/Http/Controllers/UnitController.php
namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session; // Importez Session
use Illuminate\Validation\Rule;
use Illuminate\Support\Str; // <-- AJOUTEZ CETTE LIGNE

class UnitController extends Controller
{
    public function index()
    {
        $companyId = Session::get('active_company_id');
        // Redirige si aucune entreprise n'est sélectionnée
        if (!$companyId) {
            return redirect()->route('companies.index')->with('error', 'Veuillez sélectionner une entreprise.');
        }

         // CORRECTION : Utilisez 'products' (au pluriel) car c'est le nom de la relation dans le modèle Unit
         $units = Unit::where('company_id', $companyId)->with('products')->get(); //

        return view('units.index', compact('units'));
    }

    public function create()
    {
        $companyId = Session::get('active_company_id');
        if (!$companyId) {
            return redirect()->route('companies.index')->with('error', 'Veuillez sélectionner une entreprise avant de créer une unité.');
        }
        return view('units.create');
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
                Rule::unique('units')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'short_code' => [
                'required',
                'string',
                'max:25',
                Rule::unique('units')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
        ]);

        // Génération du slug à partir du nom
        $slug = Str::slug($validatedData['name']);

        Unit::create(array_merge($validatedData, [
            'company_id' => $companyId,
            'slug' => $slug, // Assigner le slug
        ]));

        return redirect()->route('units.index')->with('success', 'Unité créée avec succès!');
    }

    public function update(Request $request, Unit $unit)
    {
        // Vérifier que l'unité appartient à l'entreprise active de l'utilisateur
        if ($unit->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à mettre à jour cette unité.');
        }

        $companyId = Session::get('active_company_id');
        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })->ignore($unit->id),
            ],
            'short_code' => [
                'required',
                'string',
                'max:25',
                Rule::unique('units')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })->ignore($unit->id),
            ],
        ]);

        // Génération du slug à partir du nouveau nom
        $slug = Str::slug($validatedData['name']);

        $unit->update(array_merge($validatedData, [
            'slug' => $slug, // Assigner le slug mis à jour
        ]));

        return redirect()->route('units.index')->with('success', 'Unité mise à jour avec succès!');
    }


    public function show(Unit $unit)
    {
        // Vérifier que l'unité appartient à l'entreprise active de l'utilisateur
        if ($unit->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à cette unité.');
        }
        return view('units.show', compact('unit'));
    }

    public function edit(Unit $unit)
    {
        // Vérifier que l'unité appartient à l'entreprise active de l'utilisateur
        if ($unit->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à modifier cette unité.');
        }
        return view('units.edit', compact('unit'));
    }

    public function destroy(Unit $unit)
    {
        // Vérifier que l'unité appartient à l'entreprise active de l'utilisateur
        if ($unit->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à supprimer cette unité.');
        }
        $unit->delete();
        return redirect()->route('units.index')->with('success', 'Unité supprimée avec succès!');
    }
}
