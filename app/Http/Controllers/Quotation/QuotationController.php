<?php

namespace App\Http\Controllers\Quotation;

use App\Enums\QuotationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Quotation\StoreQuotationRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationDetails;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session; // NOUVEAU : Importez la façade Session

class QuotationController extends Controller
{
    /**
     * Affiche une liste de tous les devis de l'utilisateur connecté et de l'entreprise active.
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
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise pour gérer les devis.');
        }

        // Filtre les devis pour n'afficher que ceux de l'utilisateur connecté ET de l'entreprise active
        $quotations = Quotation::with(['customer'])
            ->where('user_id', $userId)
            ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
            ->get();

        return view('quotations.index', [
            'quotations' => $quotations,
        ]);
    }

    /**
     * Affiche le formulaire de création d'un nouveau devis.
     * Les produits et clients listés sont également filtrés par l'utilisateur et l'entreprise active.
     */
    public function create()
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        // Redirige si aucune entreprise n'est sélectionnée
        if (!$activeCompanyId) {
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise avant de créer un devis.');
        }

        Cart::instance('quotation')->destroy();

        return view('quotations.create', [
            'cart' => Cart::content('quotation'),
            // Filtre les produits pour n'afficher que ceux de l'utilisateur connecté ET de l'entreprise active
            'products' => Product::where('user_id', $userId)
                                 ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                                 ->get(),
            // Filtre les clients pour n'afficher que ceux de l'utilisateur connecté ET de l'entreprise active
            'customers' => Customer::where('user_id', $userId)
                                   ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                                   ->get(),
            // 'statuses' => QuotationStatus::cases() // Décommentez si vous utilisez cet Enum
        ]);
    }

    /**
     * Stocke un nouveau devis dans la base de données, l'associant à l'utilisateur connecté et à l'entreprise active.
     */
    public function store(StoreQuotationRequest $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        // Redirige si aucune entreprise n'est sélectionnée
        if (!$activeCompanyId) {
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise avant de créer un devis.');
        }

        // Vérification de sécurité : Assurer que le client soumis appartient bien à l'utilisateur ET à l'entreprise active
        $customer = Customer::where('id', $request->customer_id)
                            ->where('user_id', $userId)
                            ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                            ->first();
        if (!$customer) {
            return back()->withErrors(['customer_id' => 'Le client sélectionné n\'existe pas ou ne vous appartient pas / n\'appartient pas à l\'entreprise active.']);
        }

        DB::transaction(function () use ($request, $userId, $activeCompanyId, $customer) { // AJOUT DE $activeCompanyId dans use
            $quotation = Quotation::create([
                'date' => $request->date,
                'reference' => $request->reference,
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'tax_percentage' => $request->tax_percentage,
                'discount_percentage' => $request->discount_percentage,
                'shipping_amount' => $request->shipping_amount,
                'total_amount' => $request->total_amount,
                'status' => $request->status,
                'note' => $request->note,
                'tax_amount' => Cart::instance('quotation')->tax(),
                'discount_amount' => Cart::instance('quotation')->discount(),
                'user_id' => $userId,
                'company_id' => $activeCompanyId, // AJOUT : Associe le devis à l'entreprise active
            ]);

            foreach (Cart::instance('quotation')->content() as $cart_item) {
                // Vérification de sécurité : Assurer que le produit dans le panier appartient bien à l'utilisateur ET à l'entreprise active
                $product = Product::where('id', $cart_item->id)
                                  ->where('user_id', $userId)
                                  ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                                  ->first();
                if (!$product) {
                    DB::rollBack();
                    return back()->withErrors('Un produit dans le panier n\'existe pas ou ne vous appartient pas / n\'appartient pas à l\'entreprise active.');
                }

                QuotationDetails::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_code' => $product->code,
                    'quantity' => $cart_item->qty,
                    'price' => $cart_item->price,
                    'unit_price' => $cart_item->options->unit_price,
                    'sub_total' => $cart_item->options->sub_total,
                    'product_discount_amount' => $cart_item->options->product_discount,
                    'product_discount_type' => $cart_item->options->product_discount_type,
                    'product_tax_amount' => $cart_item->options->product_tax,
                ]);
            }

            Cart::instance('quotation')->destroy();
        });

        //toast('Quotation Created!', 'success'); // Décommentez si vous utilisez ce package

        return redirect()
            ->route('quotations.index')
            ->with('success', 'Devis créé avec succès!');
    }

    /**
     * Affiche les détails d'un devis spécifique.
     * S'assure que seul le propriétaire et l'entreprise active peuvent le voir.
     */
    public function show(Quotation $quotation)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du devis ET s'il appartient à l'entreprise active.
        if (!Auth::check() || $quotation->user_id !== Auth::id() || $quotation->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à ce devis.');
        }

        // Charger les relations nécessaires
        $quotation->loadMissing(['customer', 'details']);

        return view('quotations.show', [
            'quotation' => $quotation,
        ]);
    }

    /**
     * Affiche le formulaire de modification d'un devis.
     * S'assure que seul le propriétaire et l'entreprise active peuvent le modifier.
     */
    public function edit(Quotation $quotation)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du devis ET s'il appartient à l'entreprise active.
        if (!Auth::check() || $quotation->user_id !== Auth::id() || $quotation->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à modifier ce devis.');
        }

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        return view('quotations.edit', [
            'quotation' => $quotation,
            // Filtre les clients pour n'afficher que ceux de l'utilisateur connecté ET de l'entreprise active
            'customers' => Customer::where('user_id', $userId)
                                   ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                                   ->get(),
            // Filtre les produits pour n'afficher que ceux de l'utilisateur connecté ET de l'entreprise active
            'products' => Product::where('user_id', $userId)
                                 ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                                 ->get(),
            // 'statuses' => QuotationStatus::cases() // Décommentez si vous utilisez cet Enum
        ]);
    }

    /**
     * Met à jour un devis existant.
     * S'assure que seul le propriétaire et l'entreprise active peuvent le modifier.
     */
    public function update(Request $request, Quotation $quotation)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du devis ET s'il appartient à l'entreprise active.
        if (!Auth::check() || $quotation->user_id !== Auth::id() || $quotation->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à mettre à jour ce devis.');
        }

        // Validation (vous pouvez utiliser un UpdateQuotationRequest si vous en avez un)
        $validatedData = $request->validate([
            'date' => 'required|date',
            'reference' => 'required|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'tax_percentage' => 'required|integer',
            'discount_percentage' => 'required|integer',
            'shipping_amount' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'status' => 'required|integer', // Adapter le type selon votre Enum ou TinyInteger
            'note' => 'nullable|string',
        ]);

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        // Vérification de sécurité : Assurer que le client soumis appartient bien à l'utilisateur ET à l'entreprise active
        $customer = Customer::where('id', $validatedData['customer_id'])
                            ->where('user_id', $userId)
                            ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                            ->first();
        if (!$customer) {
            return back()->withErrors(['customer_id' => 'Le client sélectionné n\'existe pas ou ne vous appartient pas / n\'appartient pas à l\'entreprise active.']);
        }

        // Mise à jour du devis
        $quotation->update(array_merge($validatedData, [
            'customer_name' => $customer->name, // Met à jour le nom du client au cas où
            // Pas besoin de mettre à jour user_id ou company_id ici si la ressource existe déjà
        ]));

        // TODO: Gérer la mise à jour des QuotationDetails si nécessaire
        // Cela implique de supprimer les anciens détails et d'en recréer de nouveaux
        // ou de les mettre à jour individuellement. (Cette logique est complexe et dépend de vos besoins exacts)

        return redirect()
            ->route('quotations.index')
            ->with('success', 'Devis mis à jour avec succès!');
    }


    /**
     * Supprime un devis.
     * S'assure que seul le propriétaire et l'entreprise active peuvent le supprimer.
     */
    public function destroy(Quotation $quotation)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du devis ET s'il appartient à l'entreprise active.
        if (!Auth::check() || $quotation->user_id !== Auth::id() || $quotation->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à supprimer ce devis.');
        }

        $quotation->delete();

        return redirect()
            ->route('quotations.index') // Conserver la redirection vers l'index des devis
            ->with('success', 'Devis supprimé avec succès!');
    }
}
