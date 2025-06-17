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
use Illuminate\Support\Facades\Auth; // Important : Importer la façade Auth

class OrderController extends Controller
{
    /**
     * Affiche une liste des commandes pour l'utilisateur connecté.
     */
    public function index()
    {
        // Récupère uniquement les commandes appartenant à l'utilisateur actuellement authentifié.
        $orders = Order::where('user_id', Auth::id())->latest()->get();

        return view('orders.index', [
            'orders' => $orders,
        ]);
    }

    /**
     * Affiche le formulaire de création d'une nouvelle commande.
     */
    public function create()
    {
        // Détruit le panier 'order' avant de créer une nouvelle commande
        Cart::instance('order')
            ->destroy();

        return view('orders.create', [
            'carts' => Cart::content(),
            'customers' => Customer::all(['id', 'name']),
            'products' => Product::with(['category', 'unit'])->get(),
        ]);
    }

    /**
     * Stocke une nouvelle commande dans la base de données.
     */
    public function store(OrderStoreRequest $request)
{
    DB::beginTransaction(); // Démarre la transaction

    try {
        $orderData = $request->all();
        $orderData['user_id'] = Auth::id();
        $order = Order::create($orderData);

        // Crée les détails de la commande
        $contents = Cart::instance('order')->content();
        $oDetails = [];

        foreach ($contents as $content) {
            $oDetails['order_id'] = $order->id; // Utilisez $order->id car $order est un objet Eloquent
            $oDetails['product_id'] = $content->id;
            $oDetails['quantity'] = $content->qty;
            $oDetails['unitcost'] = $content->price;
            $oDetails['total'] = $content->subtotal;
            $oDetails['created_at'] = Carbon::now();

            OrderDetails::insert($oDetails); // Ou mieux : OrderDetails::create($oDetails); si $fillable est configuré
        }

        // Vérifiez que le panier est bien détruit APRÈS que les détails sont enregistrés
        Cart::instance('order')->destroy();

        DB::commit(); // Valide la transaction si tout s'est bien passé

        return redirect()
            ->route('orders.index')
            ->with('success', 'La commande a été créée avec succès !');

    } catch (\Exception $e) {
        DB::rollBack(); // Annule la transaction si une erreur survient
        // Ici, vous pouvez loguer l'erreur ou la retourner pour la voir
        // Par exemple: return back()->with('error', 'Erreur lors de la création de la commande: ' . $e->getMessage());
        \Log::error("Erreur lors de la création de la commande: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'Une erreur est survenue lors de la création de la commande. Veuillez réessayer.');
    }
}

    /**
     * Affiche les détails d'une commande spécifique, uniquement si elle appartient à l'utilisateur.
     */
    public function show(Order $order)
    {
        // Vérifie si la commande appartient bien à l'utilisateur connecté.
        // Si ce n'est pas le cas, ou si la commande n'existe pas, cela lèvera une exception 404.
        $order = Order::where('user_id', Auth::id())
            ->where('id', $order->id)
            ->with(['customer', 'details'])
            ->firstOrFail();

        return view('orders.show', [
            'order' => $order,
        ]);
    }

    /**
     * Met à jour le statut d'une commande spécifique, uniquement si elle appartient à l'utilisateur.
     */
    public function update(Order $order, Request $request)
    {
        // Il est crucial d'ajouter une vérification d'autorisation ici aussi.
        // Pour une approche simple, on peut vérifier l'ID utilisateur :
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à la mise à jour de cette commande.');
        }

        // Réduit le stock des produits liés à cette commande
        $products = OrderDetails::where('order_id', $order->id)->get();

        foreach ($products as $product) {
            Product::where('id', $product->product_id)
                ->update(['quantity' => DB::raw('quantity-' . $product->quantity)]);
        }

        // Met à jour le statut de la commande à "Complète"
        $order->update([
            'order_status' => OrderStatus::COMPLETE,
        ]);

        return redirect()
            ->route('orders.complete')
            ->with('success', 'La commande a été finalisée avec succès !');
    }

    /**
     * Supprime une commande spécifique, uniquement si elle appartient à l'utilisateur.
     */
    public function destroy(Order $order)
    {
        // Vérifie si la commande appartient à l'utilisateur connecté avant de la supprimer.
        Order::where('user_id', Auth::id())
            ->where('id', $order->id)
            ->delete();

        return redirect()
            ->route('orders.index')
            ->with('success', 'La commande a été supprimée avec succès !');
    }

    /**
     * Télécharge la facture d'une commande spécifique.
     * Une vérification d'autorisation devrait être ajoutée ici aussi.
     */
    public function downloadInvoice($orderId) // Renommé pour clarté
    {
        // Assurez-vous que l'utilisateur est autorisé à télécharger cette facture
        $order = Order::with(['customer', 'details'])
            ->where('id', $orderId)
            ->where('user_id', Auth::id()) // S'assurer que c'est la commande de l'utilisateur
            ->firstOrFail(); // Utilisez firstOrFail pour lever une 404 si non trouvé

        return view('orders.print-invoice', [
            'order' => $order,
        ]);
    }
}