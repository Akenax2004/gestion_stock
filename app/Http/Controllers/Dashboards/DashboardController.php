<?php

namespace App\Http\Controllers\Dashboards;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Quotation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session; // Garder Session pour récupérer l'active_company_id

class DashboardController extends Controller
{
    public function index()
    {
        // Le middleware 'auth' garantit que l'utilisateur est connecté.
        // Le middleware 'company.selected' garantit qu'une entreprise active est en session.

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        // Toutes les requêtes sont désormais filtrées par user_id ET active_company_id
        // grâce aux Global Scopes que vous avez ou devez implémenter sur vos modèles.
        // Si les Global Scopes ne sont pas encore en place, ajoutez les where clauses ici.

        $orders = Order::where('user_id', $userId)
                       ->where('company_id', $activeCompanyId) // Exemple si Global Scope n'est pas actif partout
                       ->count();
        $completedOrders = Order::where('user_id', $userId)
                                ->where('company_id', $activeCompanyId)
                                ->where('order_status', OrderStatus::COMPLETE)
                                ->count();

        $products = Product::where('user_id', $userId)
                           ->where('company_id', $activeCompanyId)
                           ->count();

        $purchases = Purchase::where('created_by', $userId) // created_by pour purchases
                             ->where('company_id', $activeCompanyId)
                             ->count();
        $todayPurchases = Purchase::query()
                                  ->where('created_by', $userId)
                                  ->where('company_id', $activeCompanyId)
                                  ->whereDate('date', today()) // Utiliser whereDate si 'date' est un datetime
                                  ->count(); // Utiliser count() au lieu de get()->count() pour l'efficacité

        $categories = Category::where('user_id', $userId)
                               ->where('company_id', $activeCompanyId)
                               ->count();

        $quotations = Quotation::where('user_id', $userId)
                               ->where('company_id', $activeCompanyId)
                               ->count();
        $todayQuotations = Quotation::query()
                                    ->where('user_id', $userId)
                                    ->where('company_id', $activeCompanyId)
                                    ->whereDate('date', today()) // Utiliser whereDate
                                    ->count(); // Utiliser count()

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
