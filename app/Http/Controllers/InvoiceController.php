<?php

namespace App\Http\Controllers;

use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Models\Customer;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Auth;    // NOUVEAU : Importez la façade Auth
use Illuminate\Support\Facades\Session; // NOUVEAU : Importez la façade Session

class InvoiceController extends Controller
{
    /**
     * Affiche la vue de création de facture, en s'assurant que le client appartient
     * à l'utilisateur connecté et à l'entreprise active.
     */
    public function create(StoreInvoiceRequest $request, Customer $customer)
    {
        // Vérifie si un utilisateur est authentifié
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        // Redirige si aucune entreprise n'est sélectionnée
        if (!$activeCompanyId) {
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise avant de créer une facture.');
        }

        // Récupère le client en s'assurant qu'il appartient à l'utilisateur et à l'entreprise active.
        $customer = Customer::where('id', $request->get('customer_id'))
            ->where('user_id', $userId)         // FILTRAGE PAR UTILISATEUR
            ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
            ->first();

        // Si le client n'est pas trouvé (ou n'appartient pas à l'utilisateur/entreprise), redirige avec une erreur.
        if (!$customer) {
            return redirect()->back()->withErrors('Le client sélectionné n\'existe pas ou ne vous appartient pas / n\'appartient pas à l\'entreprise active.');
        }

        return view('invoices.index', [
            'customer' => $customer,
            'carts' => Cart::instance('order')->content(),
        ]);
    }

}
