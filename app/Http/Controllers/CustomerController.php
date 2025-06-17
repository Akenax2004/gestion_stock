<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session; // NOUVEAU : Importez la façade Session

class CustomerController extends Controller
{
    /**
     * Affiche une liste de tous les clients de l'utilisateur connecté et de l'entreprise active.
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
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise pour gérer les clients.');
        }

        // Récupère uniquement les clients appartenant à l'utilisateur connecté ET à l'entreprise active
        $customers = Customer::where('user_id', $userId)
                             ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                             ->get();

        return view('customers.index', [
            'customers' => $customers
        ]);
    }

    /**
     * Affiche le formulaire de création d'un nouveau client.
     */
    public function create()
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $activeCompanyId = Session::get('active_company_id');

        // Redirige si aucune entreprise n'est sélectionnée
        if (!$activeCompanyId) {
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise avant de créer un client.');
        }

        return view('customers.create');
    }

    /**
     * Stocke un nouveau client dans la base de données, l'associant à l'utilisateur connecté et à l'entreprise active.
     */
    public function store(StoreCustomerRequest $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        // Redirige si aucune entreprise n'est sélectionnée
        if (!$activeCompanyId) {
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise avant de créer un client.');
        }

        // Crée le client en ajoutant l'ID de l'utilisateur et l'ID de l'entreprise active.
        $customer = Customer::create(array_merge($request->validated(), [
            'user_id' => $userId, // Associe le client à l'utilisateur connecté
            'company_id' => $activeCompanyId, // AJOUT : Associe le client à l'entreprise active
        ]));

        /**
         * Gère le téléchargement d'une image
         */
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();

            // Stocke l'image dans le dossier 'customers' du disque 'public'
            $file->storeAs('customers', $filename, 'public');
            $customer->update([
                'photo' => $filename
            ]);
        }

        return redirect()
            ->route('customers.index')
            ->with('success', 'Nouveau client a été créé avec succès!');
    }

    /**
     * Affiche les détails d'un client spécifique.
     * S'assure que seul le propriétaire et l'entreprise active peuvent le voir.
     */
    public function show(Customer $customer)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du client ET si elle appartient à l'entreprise active.
        if (!Auth::check() || $customer->user_id !== Auth::id() || $customer->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à ce client.');
        }

        // Charge les relations 'quotations' et 'orders'.
        // Ces relations devraient être configurées dans le modèle Customer pour également filtrer par company_id
        // si les données sont sensibles à l'entreprise.
        $customer->loadMissing(['quotations', 'orders']);

        return view('customers.show', [
            'customer' => $customer
        ]);
    }

    /**
     * Affiche le formulaire de modification d'un client.
     * S'assure que seul le propriétaire et l'entreprise active peuvent le modifier.
     */
    public function edit(Customer $customer)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du client ET si elle appartient à l'entreprise active.
        if (!Auth::check() || $customer->user_id !== Auth::id() || $customer->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à modifier ce client.');
        }

        return view('customers.edit', [
            'customer' => $customer
        ]);
    }

    /**
     * Met à jour un client existant.
     * S'assure que seul le propriétaire et l'entreprise active peuvent le modifier.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du client ET si elle appartient à l'entreprise active.
        if (!Auth::check() || $customer->user_id !== Auth::id() || $customer->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à mettre à jour ce client.');
        }

        // Mettre à jour les données du client.
        // Les règles d'unicité dans UpdateCustomerRequest devraient être mises à jour pour inclure company_id
        // Par exemple: 'email' => 'nullable|email|unique:customers,email,' . $customer->id . ',id,company_id,' . Session::get('active_company_id'),
        $customer->update($request->validated());

        if ($request->hasFile('photo')) {
            // Supprimer l'ancienne photo si elle existe
            if ($customer->photo) {
                Storage::disk('public')->delete('customers/' . $customer->photo);
            }

            // Préparer la nouvelle photo
            $file = $request->file('photo');
            $fileName = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();

            // Stocker l'image dans le stockage public
            $file->storeAs('customers', $fileName, 'public');

            // Enregistrer le nom de la nouvelle photo en base de données
            $customer->update([
                'photo' => $fileName
            ]);
        } elseif ($request->input('photo_removed')) { // Gère la suppression explicite de la photo
             if ($customer->photo) {
                Storage::disk('public')->delete('customers/' . $customer->photo);
                $customer->update(['photo' => null]);
            }
        }

        return redirect()
            ->route('customers.index')
            ->with('success', 'Le client a été mis à jour avec succès!');
    }

    /**
     * Supprime un client.
     * S'assure que seul le propriétaire et l'entreprise active peuvent le supprimer.
     */
    public function destroy(Customer $customer)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du client ET si elle appartient à l'entreprise active.
        if (!Auth::check() || $customer->user_id !== Auth::id() || $customer->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à supprimer ce client.');
        }

        // Supprime la photo du client du stockage si elle existe
        if ($customer->photo) {
            Storage::disk('public')->delete('customers/' . $customer->photo);
        }

        // Supprime le client de la base de données
        $customer->delete();

        return redirect()
            ->back()
            ->with('success', 'Le client a été supprimé avec succès!');
    }
}
