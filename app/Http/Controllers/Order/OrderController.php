<?php

namespace App\Http\Controllers\Order;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\OrderStoreRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Product;
use Carbon\Carbon;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session; // NOUVEAU : Importez la façade Session
use Haruncpi\LaravelIdGenerator\IdGenerator; // Ajoutez l'import pour IdGenerator si ce n'est pas déjà fait

class OrderController extends Controller
{
    /**
     * Affiche une liste des commandes pour l'utilisateur connecté et l'entreprise active.
     */
    public function index()
    {
        // Vérifie si un utilisateur est authentifié
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        // Redirige si aucune entreprise n'est sélectionnée
        if (!$activeCompanyId) {
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise pour gérer les commandes.');
        }

        // Récupère uniquement les commandes appartenant à l'utilisateur et à l'entreprise active.
        $orders = Order::where('user_id', $userId)
                       ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                       ->latest()
                       ->get();

        return view('orders.index', [
            'orders' => $orders,
        ]);
    }

    /**
     * Affiche le formulaire de création d'une nouvelle commande.
     * Filtre les clients et produits par l'utilisateur et l'entreprise active.
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
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise avant de créer une commande.');
        }

        // Détruit le panier 'order' avant de créer une nouvelle commande
        Cart::instance('order')->destroy();

        return view('orders.create', [
            'carts' => Cart::content(),
            // Filtre les clients par l'utilisateur ET l'entreprise active
            'customers' => Customer::where('user_id', $userId)
                                   ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                                   ->get(['id', 'name']),
            // Filtre les produits par l'utilisateur ET l'entreprise active
            'products' => Product::where('user_id', $userId)
                                 ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                                 ->with(['category', 'unit'])
                                 ->get(),
        ]);
    }

    /**
     * Stocke une nouvelle commande dans la base de données, l'associant à l'utilisateur connecté et à l'entreprise active.
     */
    public function store(OrderStoreRequest $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        // Redirige si aucune entreprise n'est sélectionnée
        if (!$activeCompanyId) {
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise avant de créer une commande.');
        }

        DB::beginTransaction();

        try {
            // Vérification de sécurité pour le client : doit appartenir à l'utilisateur ET à l'entreprise active
            $customer = Customer::where('id', $request->customer_id)
                                ->where('user_id', $userId)
                                ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                                ->first();

            if (!$customer) {
                DB::rollBack();
                return redirect()->back()->withErrors('Le client sélectionné n\'existe pas ou ne vous appartient pas / n\'appartient pas à l\'entreprise active.');
            }

            // Générer le numéro de facture ici si ce n'est pas fait dans le FormRequest ou si le FormRequest est mis à jour pour cela
            $invoiceNo = IdGenerator::generate([
                'table' => 'orders',
                'field' => 'invoice_no',
                'length' => 10,
                'prefix' => 'INV-',
            ]);

            // Calculer les totaux basés sur le panier ou sur les données du formulaire si vous avez changé la logique
            // Pour l'instant, je vais me baser sur le panier pour rester proche de votre code initial.
            $subTotalCalculated = Cart::instance('order')->subtotal();
            $vatCalculated = Cart::instance('order')->tax();
            $totalCalculated = Cart::instance('order')->total();
            $totalProductsCount = Cart::instance('order')->count(); // Si c'est le nombre total de produits (quantité cumulée)

            // Calcul du "due" basé sur le total et le montant "payé" par l'utilisateur dans le formulaire
            $paidAmount = $request->pay;
            $dueAmount = $totalCalculated - $paidAmount;


            $order = Order::create([
                'user_id' => $userId,
                'company_id' => $activeCompanyId, // AJOUT : Associe la commande à l'entreprise active
                'customer_id' => $customer->id,
                'order_date' => Carbon::now()->format('Y-m-d'),
                'order_status' => OrderStatus::PENDING, // Utilise l'Enum directement
                'total_products' => $totalProductsCount,
                'sub_total' => $subTotalCalculated,
                'vat' => $vatCalculated,
                'total' => $totalCalculated,
                'invoice_no' => $invoiceNo,
                'payment_type' => $request->payment_type,
                'pay' => $paidAmount,
                'due' => $dueAmount,
            ]);

            // Crée les détails de la commande
            $contents = Cart::instance('order')->content();
            
            if ($contents->isEmpty()) {
                DB::rollBack();
                return redirect()->back()->withErrors('Le panier est vide. Veuillez ajouter des produits pour créer une commande.');
            }

            $oDetailsToInsert = [];
            foreach ($contents as $content) {
                // Vérifier que le produit appartient à l'entreprise active et à l'utilisateur
                $product = Product::where('id', $content->id)
                                  ->where('user_id', $userId)
                                  ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                                  ->first();

                if (!$product) {
                    DB::rollBack();
                    return redirect()->back()->withErrors('Un produit dans le panier n\'existe pas ou ne vous appartient pas / n\'appartient pas à l\'entreprise active.');
                }

                // Mise à jour de la quantité du produit en stock
                $product->update(['quantity' => DB::raw('quantity - ' . $content->qty)]);

                $oDetailsToInsert[] = [
                    'order_id' => $order->id,
                    'product_id' => $content->id,
                    'quantity' => $content->qty,
                    'unitcost' => $content->price,
                    'total' => $content->subtotal,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(), // Ajout de updated_at
                ];
            }
            OrderDetails::insert($oDetailsToInsert);

            // Détruit le panier après l'enregistrement des détails
            Cart::instance('order')->destroy();

            DB::commit(); // Valide la transaction

            return redirect()
                ->route('orders.index')
                ->with('success', 'La commande a été créée avec succès !');

        } catch (\Exception $e) {
            DB::rollBack(); // Annule la transaction
            \Log::error("Erreur lors de la création de la commande: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la création de la commande. Veuillez réessayer.');
        }
    }

    /**
     * Gère l'ajout d'un dépôt (paiement partiel) pour une commande.
     * S'assure que seul le propriétaire de la commande de l'entreprise active peut ajouter un dépôt.
     */
    public function makeDeposit(Request $request, Order $order)
    {
        // 1. Vérification de l'authentification et de la propriété de la commande et de l'entreprise
        if (!Auth::check() || $order->user_id !== Auth::id() || $order->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à effectuer un dépôt sur cette commande.');
        }

        // 2. Validation du montant du dépôt
        $request->validate([
            'deposit_amount' => 'required|numeric|min:0.01',
        ]);

        $depositAmount = floatval($request->input('deposit_amount'));

        DB::beginTransaction();
        try {
            // 3. Calcul du nouveau montant payé
            $newPaidAmount = $order->pay + $depositAmount;

            // 4. S'assurer que le montant payé ne dépasse pas le total de la commande
            if ($newPaidAmount > $order->total) {
                $newPaidAmount = $order->total;
                $depositAmount = $order->total - $order->pay; // Ajuste le dépôt si le total est dépassé
            }

            // 5. Mettre à jour les champs 'pay' et 'due'
            $order->update([
                'pay' => $newPaidAmount,
                'due' => $order->total - $newPaidAmount, // Recalcule le dû
            ]);

            // Optionnel: Si le paiement atteint le total, vous pouvez changer le statut
            if ($order->due <= 0 && $order->order_status !== OrderStatus::COMPLETE->value) {
                $order->update(['order_status' => OrderStatus::COMPLETE]); // Utilise l'Enum directement
            }
            
            DB::commit();

            return redirect()
                ->back()
                ->with('success', 'Dépôt de ' . $depositAmount . ' ajouté avec succès. Montant dû restant : ' . $order->due);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Erreur lors de l'ajout d'un dépôt à la commande ID: {$order->id} : " . $e->getMessage() . " - Trace: " . $e->getTraceAsString());
            return redirect()->back()->withErrors('Une erreur est survenue lors de l\'ajout du dépôt. Veuillez réessayer. (Détails: ' . $e->getMessage() . ')');
        }
    }


    /**
     * Affiche les détails d'une commande spécifique, uniquement si elle appartient à l'utilisateur et à l'entreprise active.
     */
    public function show(Order $order)
    {
        // Vérifie si la commande appartient bien à l'utilisateur connecté et à l'entreprise active.
        if (!Auth::check() || $order->user_id !== Auth::id() || $order->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à cette commande.');
        }

        $order->loadMissing(['customer', 'details']);

        return view('orders.show', [
            'order' => $order,
        ]);
    }

    /**
     * Met à jour le statut d'une commande spécifique, uniquement si elle appartient à l'utilisateur et à l'entreprise active.
     */
    public function update(Order $order, Request $request)
    {
        // Il est crucial d'ajouter une vérification d'autorisation ici aussi.
        if (!Auth::check() || $order->user_id !== Auth::id() || $order->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à la mise à jour de cette commande.');
        }

        DB::beginTransaction();

        try {
            // Réduit le stock des produits liés à cette commande
            $orderDetails = OrderDetails::where('order_id', $order->id)->get();

            foreach ($orderDetails as $detail) {
                // S'assurer que le produit appartient à l'utilisateur et à l'entreprise active
                $product = Product::where('id', $detail->product_id)
                                  ->where('user_id', Auth::id())
                                  ->where('company_id', Session::get('active_company_id')) // FILTRAGE PAR ENTREPRISE
                                  ->first();

                if (!$product) {
                    DB::rollBack();
                    return redirect()->back()->withErrors('Un produit lié à cette commande n\'existe pas ou ne vous appartient pas / n\'appartient pas à l\'entreprise active.');
                }

                // Vérification de stock avant de réduire
                if ($product->quantity < $detail->quantity) {
                    DB::rollBack();
                    return redirect()->back()->withErrors('Quantité insuffisante en stock pour le produit : ' . $product->name);
                }

                $product->update(['quantity' => DB::raw('quantity-' . $detail->quantity)]);
            }

            // Met à jour le statut de la commande à "Complète"
            $order->update([
                'order_status' => OrderStatus::COMPLETE, // Utilise l'Enum directement
            ]);

            DB::commit();

            return redirect()
                ->route('orders.index') // Redirection vers l'index des commandes
                ->with('success', 'La commande a été finalisée avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Erreur lors de la finalisation de la commande: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Une erreur est survenue lors de la finalisation de la commande. Veuillez réessayer.');
        }
    }

    /**
     * Supprime une commande spécifique, uniquement si elle appartient à l'utilisateur et à l'entreprise active.
     */
    public function destroy(Order $order)
    {
        // Vérifie si la commande appartient à l'utilisateur connecté et à l'entreprise active avant de la supprimer.
        if (!Auth::check() || $order->user_id !== Auth::id() || $order->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à supprimer cette commande.');
        }
        
        // Supprime les détails de la commande avant la commande elle-même (si non géré par cascadeOnDelete sur order_id dans OrderDetails)
        // OrderDetails::where('order_id', $order->id)->delete();

        $order->delete();

        return redirect()
            ->route('orders.index')
            ->with('success', 'La commande a été supprimée avec succès !');
    }

    /**
     * Télécharge la facture d'une commande spécifique.
     * S'assure que l'utilisateur est autorisé et que la commande appartient à l'entreprise active.
     */
    public function downloadInvoice($orderId)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        // Redirige si aucune entreprise n'est sélectionnée
        if (!$activeCompanyId) {
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise avant de télécharger une facture.');
        }

        // Assurez-vous que l'utilisateur est autorisé à télécharger cette facture et qu'elle appartient à l'entreprise active
        $order = Order::with(['customer', 'details'])
            ->where('id', $orderId)
            ->where('user_id', $userId)
            ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
            ->firstOrFail(); // Utilisez firstOrFail pour lever une 404 si non trouvé

        // Vérification de sécurité supplémentaire pour le client de la commande (devrait être redondant si les filtres ci-dessus sont corrects)
        if ($order->customer && ($order->customer->user_id !== $userId || $order->customer->company_id !== $activeCompanyId)) {
            abort(403, 'Accès non autorisé à cette facture (client non propriétaire ou hors entreprise active).');
        }

        return view('orders.print-invoice', [
            'order' => $order,
        ]);
    }
}
