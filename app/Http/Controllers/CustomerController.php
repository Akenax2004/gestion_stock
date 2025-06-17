<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use Illuminate\Support\Facades\Auth; // N'oubliez pas d'importer la façade Auth
use Illuminate\Support\Facades\Storage; // Pour gérer la suppression de l'image via Storage

class CustomerController extends Controller
{
    /**
     * Affiche une liste de tous les clients de l'utilisateur connecté.
     */
    public function index()
    {
        // Récupère l'ID de l'utilisateur actuellement connecté.
        // Puisque la route est sous middleware 'auth', nous savons qu'un utilisateur est connecté.
        $userId = Auth::id();

        // Récupère uniquement les clients appartenant à l'utilisateur connecté
        $customers = Customer::where('user_id', $userId)->get();

        return view('customers.index', [
            'customers' => $customers
        ]);
    }

    /**
     * Affiche le formulaire de création d'un nouveau client.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Stocke un nouveau client dans la base de données, l'associant à l'utilisateur connecté.
     */
    public function store(StoreCustomerRequest $request)
    {
        // Récupère l'ID de l'utilisateur connecté pour l'associer au nouveau client.
        $userId = Auth::id();

        // Crée le client en ajoutant l'ID de l'utilisateur.
        $customer = Customer::create(array_merge($request->validated(), [
            'user_id' => $userId, // Associe le client à l'utilisateur connecté
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
     * S'assure que seul le propriétaire peut le voir.
     */
    public function show(Customer $customer)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du client.
        // Si ce n'est pas le cas, renvoie une erreur 403 (Forbidden).
        if ($customer->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à ce client.');
        }

        // Charge les relations 'quotations' et 'orders'.
        // Le .get() est superflu après loadMissing, car loadMissing retourne l'instance du modèle.
        $customer->loadMissing(['quotations', 'orders']);

        return view('customers.show', [
            'customer' => $customer
        ]);
    }

    /**
     * Affiche le formulaire de modification d'un client.
     * S'assure que seul le propriétaire peut le modifier.
     */
    public function edit(Customer $customer)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du client.
        if ($customer->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à modifier ce client.');
        }

        return view('customers.edit', [
            'customer' => $customer
        ]);
    }

    /**
     * Met à jour un client existant.
     * S'assure que seul le propriétaire peut le modifier.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du client.
        if ($customer->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à mettre à jour ce client.');
        }

        // Mettre à jour les données du client, en excluant le champ 'photo' pour le moment.
        $customer->update($request->validated()); // Utilisez $request->validated() si vous utilisez FormRequest

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
        }

        return redirect()
            ->route('customers.index')
            ->with('success', 'Le client a été mis à jour avec succès!');
    }

    /**
     * Supprime un client.
     * S'assure que seul le propriétaire peut le supprimer.
     */
    public function destroy(Customer $customer)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du client.
        if ($customer->user_id !== Auth::id()) {
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
