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
use Illuminate\Support\Facades\Auth; // Importez la façade Auth
use Illuminate\Http\Request; // Importez Request si ce n'est pas déjà fait pour update, edit, show, destroy

class QuotationController extends Controller
{
    /**
     * Affiche une liste de tous les devis de l'utilisateur connecté.
     */
    public function index()
    {
        // Vérifie si un utilisateur est authentifié
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();

        // Filtre les devis pour n'afficher que ceux de l'utilisateur connecté
        $quotations = Quotation::with(['customer'])
            ->where('user_id', $userId)
            ->get();

        return view('quotations.index', [
            'quotations' => $quotations,
        ]);
    }

    /**
     * Affiche le formulaire de création d'un nouveau devis.
     * Les produits et clients listés sont également filtrés par l'utilisateur.
     */
    public function create()
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();

        Cart::instance('quotation')
            ->destroy();

        return view('quotations.create', [
            'cart' => Cart::content('quotation'),
            // Filtre les produits pour n'afficher que ceux de l'utilisateur connecté
            'products' => Product::where('user_id', $userId)->get(),
            // Filtre les clients pour n'afficher que ceux de l'utilisateur connecté
            'customers' => Customer::where('user_id', $userId)->get(),
            // 'statuses' => QuotationStatus::cases() // Décommentez si vous utilisez cet Enum
        ]);
    }

    /**
     * Stocke un nouveau devis dans la base de données, l'associant à l'utilisateur connecté.
     */
    public function store(StoreQuotationRequest $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();

        // Vérification de sécurité : Assurer que le client soumis appartient bien à l'utilisateur
        $customer = Customer::where('id', $request->customer_id)->where('user_id', $userId)->first();
        if (!$customer) {
            return back()->withErrors(['customer_id' => 'Le client sélectionné n\'existe pas ou ne vous appartient pas.']);
        }

        DB::transaction(function () use ($request, $userId, $customer) {
            $quotation = Quotation::create([
                'date' => $request->date,
                'reference' => $request->reference,
                'customer_id' => $customer->id, // Utilise l'ID du client vérifié
                'customer_name' => $customer->name, // Utilise le nom du client vérifié
                'tax_percentage' => $request->tax_percentage,
                'discount_percentage' => $request->discount_percentage,
                'shipping_amount' => $request->shipping_amount,
                'total_amount' => $request->total_amount,
                'status' => $request->status,
                'note' => $request->note,
                'tax_amount' => Cart::instance('quotation')->tax(),
                'discount_amount' => Cart::instance('quotation')->discount(),
                'user_id' => $userId, // Associe le devis à l'utilisateur connecté
            ]);

            foreach (Cart::instance('quotation')->content() as $cart_item) {
                // Vérification de sécurité : Assurer que le produit dans le panier appartient bien à l'utilisateur
                $product = Product::where('id', $cart_item->id)->where('user_id', $userId)->first();
                if (!$product) {
                    DB::rollBack(); // Annule la création du devis si un produit n'est pas valide
                    return back()->withErrors('Un produit dans le panier n\'existe pas ou ne vous appartient pas.');
                }

                QuotationDetails::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $product->id, // Utilise l'ID du produit vérifié
                    'product_name' => $product->name, // Utilise le nom du produit vérifié
                    'product_code' => $product->code, // Utilise le code du produit vérifié
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
     * S'assure que seul le propriétaire peut le voir.
     */
    public function show(Quotation $quotation)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du devis.
        if (!Auth::check() || $quotation->user_id !== Auth::id()) {
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
     * S'assure que seul le propriétaire peut le modifier.
     */
    public function edit(Quotation $quotation)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du devis.
        if (!Auth::check() || $quotation->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à modifier ce devis.');
        }

        $userId = Auth::id();

        return view('quotations.edit', [
            'quotation' => $quotation,
            'customers' => Customer::where('user_id', $userId)->get(), // Filtrer par utilisateur
            'products' => Product::where('user_id', $userId)->get(),   // Filtrer par utilisateur
            // 'statuses' => QuotationStatus::cases() // Décommentez si vous utilisez cet Enum
        ]);
    }

    /**
     * Met à jour un devis existant.
     * S'assure que seul le propriétaire peut le modifier.
     */
    public function update(Request $request, Quotation $quotation)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du devis.
        if (!Auth::check() || $quotation->user_id !== Auth::id()) {
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

        // Vérification de sécurité : Assurer que le client soumis appartient bien à l'utilisateur
        $customer = Customer::where('id', $validatedData['customer_id'])->where('user_id', $userId)->first();
        if (!$customer) {
            return back()->withErrors(['customer_id' => 'Le client sélectionné n\'existe pas ou ne vous appartient pas.']);
        }

        // Mise à jour du devis
        $quotation->update(array_merge($validatedData, [
            'customer_name' => $customer->name, // Met à jour le nom du client au cas où
        ]));

        // TODO: Gérer la mise à jour des QuotationDetails si nécessaire
        // Cela implique de supprimer les anciens détails et d'en recréer de nouveaux
        // ou de les mettre à jour individuellement.

        return redirect()
            ->route('quotations.index')
            ->with('success', 'Devis mis à jour avec succès!');
    }


    /**
     * Supprime un devis.
     * S'assure que seul le propriétaire peut le supprimer.
     */
    public function destroy(Quotation $quotation)
    {
        // Vérifie si l'utilisateur connecté est le propriétaire du devis.
        if (!Auth::check() || $quotation->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à supprimer ce devis.');
        }

        $quotation->delete();

        return redirect()
            ->route('quotations.index') // Conserver la redirection vers l'index des devis
            ->with('success', 'Devis supprimé avec succès!');
    }
}
