<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use Illuminate\Support\Facades\Auth;    // NOUVEAU : Importez la façade Auth
use Illuminate\Support\Facades\Session; // NOUVEAU : Importez la façade Session
use Illuminate\Support\Facades\Storage; // NOUVEAU : Importez la façade Storage pour une meilleure gestion des fichiers

class SupplierController extends Controller
{
    /**
     * Affiche une liste de tous les fournisseurs de l'utilisateur connecté et de l'entreprise active.
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
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise pour gérer les fournisseurs.');
        }

        // Récupère uniquement les fournisseurs appartenant à l'utilisateur connecté ET à l'entreprise active
        $suppliers = Supplier::where('user_id', $userId)
                             ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                             ->get();

        return view('suppliers.index', [
            'suppliers' => $suppliers
        ]);
    }

    /**
     * Affiche le formulaire de création d'un nouveau fournisseur.
     */
    public function create()
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $activeCompanyId = Session::get('active_company_id');

        // Redirige si aucune entreprise n'est sélectionnée
        if (!$activeCompanyId) {
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise avant de créer un fournisseur.');
        }

        return view('suppliers.create');
    }

    /**
     * Stocke un nouveau fournisseur dans la base de données, l'associant à l'utilisateur connecté et à l'entreprise active.
     */
    public function store(StoreSupplierRequest $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        // Redirige si aucune entreprise n'est sélectionnée
        if (!$activeCompanyId) {
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise avant de créer un fournisseur.');
        }

        // Crée le fournisseur en ajoutant l'ID de l'utilisateur et l'ID de l'entreprise active.
        $supplier = Supplier::create(array_merge($request->validated(), [
            'user_id' => $userId, // Associe le fournisseur à l'utilisateur connecté
            'company_id' => $activeCompanyId, // AJOUT : Associe le fournisseur à l'entreprise active
        ]));

        /**
         * Gère le téléchargement d'une image
         */
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = hexdec(uniqid()).'.'.$file->getClientOriginalExtension();

            $file->storeAs('suppliers/', $filename, 'public');
            $supplier->update([
                'photo' => $filename
            ]);
        }

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Nouveau fournisseur a été créé avec succès!');
    }

    /**
     * Affiche les détails d'un fournisseur spécifique.
     * S'assure que seul le propriétaire et l'entreprise active peuvent le voir.
     */
    public function show(Supplier $supplier)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du fournisseur ET s'il appartient à l'entreprise active.
        if (!Auth::check() || $supplier->user_id !== Auth::id() || $supplier->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à ce fournisseur.');
        }

        // Charge les relations 'purchases'.
        // Le '.get()' est superflu après loadMissing, car loadMissing retourne l'instance du modèle.
        // Les achats ('purchases') devraient être filtrés par company_id et user_id dans leur propre contrôleur ou via un Global Scope.
        $supplier->loadMissing('purchases');

        return view('suppliers.show', [
            'supplier' => $supplier
        ]);
    }

    /**
     * Affiche le formulaire de modification d'un fournisseur.
     * S'assure que seul le propriétaire et l'entreprise active peuvent le modifier.
     */
    public function edit(Supplier $supplier)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du fournisseur ET s'il appartient à l'entreprise active.
        if (!Auth::check() || $supplier->user_id !== Auth::id() || $supplier->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à modifier ce fournisseur.');
        }

        return view('suppliers.edit', [
            'supplier' => $supplier
        ]);
    }

    /**
     * Met à jour un fournisseur existant.
     * S'assure que seul le propriétaire et l'entreprise active peuvent le modifier.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du fournisseur ET s'il appartient à l'entreprise active.
        if (!Auth::check() || $supplier->user_id !== Auth::id() || $supplier->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à mettre à jour ce fournisseur.');
        }

        // Met à jour les données du fournisseur. Les règles de validation dans UpdateSupplierRequest
        // devraient être mises à jour pour inclure company_id pour les champs uniques (ex: email, phone).
        $supplier->update($request->validated()); // Utilisez validated() plutôt que except('photo')

        /**
         * Gère le téléchargement ou la suppression d'une image
         */
        if ($request->hasFile('photo')) {
            // Supprime l'ancienne photo si elle existe
            if ($supplier->photo) {
                Storage::disk('public')->delete('suppliers/' . $supplier->photo); // Utilisation de Storage
            }

            // Prépare la nouvelle photo
            $file = $request->file('photo');
            $fileName = hexdec(uniqid()).'.'.$file->getClientOriginalExtension();

            // Stocke l'image dans le stockage public
            $file->storeAs('suppliers/', $fileName, 'public');

            // Enregistre le nom de la nouvelle photo en base de données
            $supplier->update([
                'photo' => $fileName
            ]);
        } elseif ($request->input('photo_removed')) { // Gère la suppression explicite de la photo (ex: via checkbox)
            if ($supplier->photo) {
                Storage::disk('public')->delete('suppliers/' . $supplier->photo);
                $supplier->update(['photo' => null]);
            }
        }

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Le fournisseur a été mis à jour avec succès!');
    }

    /**
     * Supprime un fournisseur.
     * S'assure que seul le propriétaire et l'entreprise active peuvent le supprimer.
     */
    public function destroy(Supplier $supplier)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du fournisseur ET s'il appartient à l'entreprise active.
        if (!Auth::check() || $supplier->user_id !== Auth::id() || $supplier->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à supprimer ce fournisseur.');
        }

        /**
         * Supprime la photo si elle existe.
         */
        if($supplier->photo){
            Storage::disk('public')->delete('suppliers/' . $supplier->photo); // Utilisation de Storage
        }

        $supplier->delete();

        return redirect()
            ->route('suppliers.index') // Conserve la redirection vers l'index des fournisseurs
            ->with('success', 'Le fournisseur a été supprimé avec succès!');
    }
}
