<?php

namespace App\Http\Controllers\Dashboards;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Quotation;
use Illuminate\Support\Facades\Auth; // Importez la façade Auth

class DashboardController extends Controller
{
    public function index()
    {
        // Vérifie si un utilisateur est authentifié
        if (!Auth::check()) {
            // Redirige vers la page de connexion si l'utilisateur n'est pas connecté.
            // Le middleware 'auth' sur la route devrait déjà gérer cela, mais c'est un bon garde-fou.
            return redirect('/login')->withErrors('Veuillez vous connecter pour accéder au tableau de bord.');
        }

        // Récupère l'ID de l'utilisateur actuellement connecté
        $userId = Auth::id();

        // FILTRAGE DES DONNÉES PAR L'UTILISATEUR CONNECTÉ
        // Assurez-vous que toutes ces tables ont une colonne 'user_id'
        // et que les modèles Eloquent correspondants ont 'user_id' dans leur propriété $fillable.

        $orders = Order::where('user_id', $userId)->count();
        $completedOrders = Order::where('user_id', $userId)
            ->where('order_status', OrderStatus::COMPLETE)
            ->count();

        $products = Product::where('user_id', $userId)->count();

        $purchases = Purchase::where('user_id', $userId)->count();
        $todayPurchases = Purchase::query()
            ->where('user_id', $userId) // Filtre par utilisateur ici aussi
            ->where('date', today())
            ->get()
            ->count();

        $categories = Category::where('user_id', $userId)->count();

        $quotations = Quotation::where('user_id', $userId)->count();
        $todayQuotations = Quotation::query()
            ->where('user_id', $userId) // Filtre par utilisateur ici aussi
            ->where('date', today()->format('Y-m-d'))
            ->get()
            ->count();

        return view('dashboard', [
            'products' => $products,
            'orders' => $orders,
            'completedOrders' => $completedOrders,
            'purchases' => $purchases,
            'todayPurchases' => $todayPurchases,
            'categories' => $categories,
            'quotations' => $quotations,
            'todayQuotations' => $todayQuotations,
        ]);
    }
}
