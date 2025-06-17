<?php

namespace App\Http\Controllers\Purchase;

use App\Enums\PurchaseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetails;
use App\Models\Supplier;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session; // NOUVEAU : Importez la façade Session
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class PurchaseController extends Controller
{
    /**
     * Affiche une liste de tous les achats de l'utilisateur connecté et de l'entreprise active.
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
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise pour gérer les achats.');
        }

        // Récupère uniquement les achats créés par l'utilisateur connecté ET appartenant à l'entreprise active
        $purchases = Purchase::where('created_by', $userId)
                             ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                             ->latest()
                             ->get();

        return view('purchases.index', [
            'purchases' => $purchases,
        ]);
    }

    /**
     * Affiche une liste des achats approuvés par l'utilisateur connecté et l'entreprise active.
     */
    public function approvedPurchases()
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        // Redirige si aucune entreprise n'est sélectionnée
        if (!$activeCompanyId) {
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise pour voir les achats approuvés.');
        }

        // Récupère les achats approuvés par l'utilisateur connecté ET appartenant à l'entreprise active
        $purchases = Purchase::with(['supplier'])
            ->where('created_by', $userId)
            ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
            ->where('status', PurchaseStatus::APPROVED)
            ->get();

        return view('purchases.approved-purchases', [
            'purchases' => $purchases,
        ]);
    }

    /**
     * Affiche les détails d'un achat spécifique.
     * S'assure que seul le propriétaire et l'entreprise active peuvent le voir.
     */
    public function show(Purchase $purchase)
    {
        // Vérifie si l'utilisateur connecté est le créateur de l'achat ET s'il appartient à l'entreprise active.
        if (!Auth::check() || $purchase->created_by !== Auth::id() || $purchase->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à cet achat.');
        }

        // Charge les relations nécessaires
        $purchase->loadMissing(['supplier', 'details', 'createdBy', 'updatedBy']);

        // Récupère les détails des produits pour cet achat.
        // Puisque $purchase est déjà filtré par company_id, les détails sont implicitement liés.
        $products = PurchaseDetails::where('purchase_id', $purchase->id)->get();

        return view('purchases.details-purchase', [
            'purchase' => $purchase,
            'products' => $products
        ]);
    }

    /**
     * Affiche le formulaire de modification d'un achat.
     * S'assure que seul le propriétaire et l'entreprise active peuvent le modifier.
     */
    public function edit(Purchase $purchase)
    {
        // Vérifie si l'utilisateur connecté est le créateur de l'achat ET s'il appartient à l'entreprise active.
        if (!Auth::check() || $purchase->created_by !== Auth::id() || $purchase->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à modifier cet achat.');
        }

        // Charge les relations nécessaires
        $purchase->loadMissing(['supplier', 'details']);

        return view('purchases.edit', [
            'purchase' => $purchase,
        ]);
    }

    /**
     * Affiche le formulaire de création d'un nouvel achat.
     * Les catégories et fournisseurs listés sont également filtrés par l'utilisateur et l'entreprise active.
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
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise avant de créer un achat.');
        }

        return view('purchases.create', [
            // Filtre les catégories et fournisseurs pour n'afficher que ceux de l'utilisateur connecté ET de l'entreprise active
            'categories' => Category::where('user_id', $userId)
                                    ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                                    ->select(['id', 'name'])
                                    ->get(),
            'suppliers' => Supplier::where('user_id', $userId)
                                   ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                                   ->select(['id', 'name'])
                                   ->get(),
        ]);
    }

    /**
     * Stocke un nouvel achat dans la base de données, l'associant à l'utilisateur connecté et à l'entreprise active.
     */
    public function store(StorePurchaseRequest $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        // Redirige si aucune entreprise n'est sélectionnée
        if (!$activeCompanyId) {
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise avant de créer un achat.');
        }

        // Vérification de sécurité : Assurer que le fournisseur soumis appartient bien à l'utilisateur ET à l'entreprise active
        $supplier = Supplier::where('id', $request->supplier_id)
                             ->where('user_id', $userId)
                             ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                             ->first();
        if (!$supplier) {
            return back()->withErrors(['supplier_id' => 'Le fournisseur sélectionné n\'existe pas ou ne vous appartient pas / n\'appartient pas à l\'entreprise active.']);
        }

        DB::beginTransaction();

        try {
            // Crée l'achat en ajoutant l'ID de l'utilisateur créateur et l'ID de l'entreprise active
            $purchase = Purchase::create(array_merge($request->validated(), [
                'created_by' => $userId,
                'company_id' => $activeCompanyId, // AJOUT : Associe l'achat à l'entreprise active
                'supplier_id' => $supplier->id,
            ]));

            if (!empty($request->invoiceProducts)) {
                $pDetails = [];

                foreach ($request->invoiceProducts as $productData) {
                    // Vérification de sécurité : Assurer que le produit dans la liste appartient bien à l'utilisateur ET à l'entreprise active
                    $product = Product::where('id', $productData['product_id'])
                                        ->where('user_id', $userId)
                                        ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
                                        ->first();
                    if (!$product) {
                        DB::rollBack();
                        return back()->withErrors('Un produit dans la liste n\'existe pas ou ne vous appartient pas / n\'appartient pas à l\'entreprise active.');
                    }

                    $pDetails[] = [
                        'purchase_id' => $purchase->id,
                        'product_id' => $product->id,
                        'quantity' => $productData['quantity'],
                        'unitcost' => $productData['unitcost'],
                        'total' => $productData['total'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
                $purchase->details()->insert($pDetails);
            }

            DB::commit();

            return redirect()
                ->route('purchases.index')
                ->with('success', 'L\'achat a été créé avec succès!');

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error("Erreur lors de la création de l'achat : " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return back()->withErrors(['error' => 'Une erreur est survenue lors de la création de l\'achat.']);
        }
    }

    /**
     * Met à jour un achat (généralement pour l'approuver).
     * S'assure que seul le propriétaire et l'entreprise active peuvent le modifier.
     */
    public function update(Purchase $purchase, Request $request)
    {
        // Vérifie si l'utilisateur connecté est le créateur de l'achat ET s'il appartient à l'entreprise active.
        if (!Auth::check() || $purchase->created_by !== Auth::id() || $purchase->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à approuver cet achat.');
        }

        DB::beginTransaction();

        try {
            // Récupère les détails de l'achat
            $productsInPurchase = PurchaseDetails::where('purchase_id', $purchase->id)->get();

            foreach ($productsInPurchase as $productDetail) {
                // Vérifier que le produit appartient à l'utilisateur ET à l'entreprise active avant de modifier la quantité
                $product = Product::where('id', $productDetail->product_id)
                                  ->where('user_id', Auth::id())
                                  ->where('company_id', Session::get('active_company_id')) // FILTRAGE PAR ENTREPRISE
                                  ->first();

                if ($product) {
                    // Augmente la quantité du produit en stock
                    $product->update(['quantity' => DB::raw('quantity+' . $productDetail->quantity)]);
                } else {
                    \Log::warning("Produit non trouvé ou non propriétaire pour la mise à jour de stock dans l'achat ID: {$purchase->id}, produit ID: {$productDetail->product_id}");
                    DB::rollBack(); // Annule la transaction si un produit n'est pas valide
                    return redirect()->back()->withErrors('Un produit lié à cet achat n\'existe pas ou ne vous appartient pas / n\'appartient pas à l\'entreprise active, impossible de mettre à jour le stock.');
                }
            }

            // Met à jour le statut de l'achat et l'utilisateur qui l'a mis à jour
            $purchase->update([
                'status' => PurchaseStatus::APPROVED,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()
                ->route('purchases.index')
                ->with('success', 'L\'achat a été approuvé avec succès!');

        } catch (Exception $e) {
            DB::rollBack();
            \Log::error("Erreur lors de l'approbation de l'achat: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()->back()->withErrors('Une erreur est survenue lors de l\'approbation de l\'achat. Veuillez réessayer.');
        }
    }

    /**
     * Supprime un achat.
     * S'assure que seul le propriétaire et l'entreprise active peuvent le supprimer.
     */
    public function destroy(Purchase $purchase)
    {
        // Vérifie si l'utilisateur connecté est le créateur de l'achat ET s'il appartient à l'entreprise active.
        if (!Auth::check() || $purchase->created_by !== Auth::id() || $purchase->company_id !== Session::get('active_company_id')) {
            abort(403, 'Accès non autorisé à supprimer cet achat.');
        }

        $purchase->delete();

        return redirect()
            ->back()
            ->with('success', 'L\'achat a été supprimé avec succès!');
    }

    /**
     * Affiche le rapport quotidien des achats de l'utilisateur connecté et de l'entreprise active.
     */
    public function dailyPurchaseReport()
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        // Redirige si aucune entreprise n'est sélectionnée
        if (!$activeCompanyId) {
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise pour voir le rapport des achats quotidiens.');
        }

        $purchases = Purchase::with(['supplier'])
            ->where('created_by', $userId)
            ->where('company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
            ->whereDate('date', today())
            ->get();

        return view('purchases.daily-report', [
            'purchases' => $purchases,
        ]);
    }

    public function getPurchaseReport()
    {
        return view('purchases.report-purchase');
    }

    /**
     * Exporte le rapport d'achats filtré par l'utilisateur connecté et l'entreprise active.
     */
    public function exportPurchaseReport(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login')->withErrors('Veuillez vous connecter.');
        }

        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        // Redirige si aucune entreprise n'est sélectionnée
        if (!$activeCompanyId) {
            return redirect()->route('companies.index')->withErrors('Veuillez sélectionner une entreprise avant d\'exporter un rapport.');
        }

        $rules = [
            'start_date' => 'required|string|date_format:Y-m-d',
            'end_date' => 'required|string|date_format:Y-m-d',
        ];

        $validatedData = $request->validate($rules);

        $sDate = $validatedData['start_date'];
        $eDate = $validatedData['end_date'];

        $purchases = DB::table('purchase_details')
            ->join('products', 'purchase_details.product_id', '=', 'products.id')
            ->join('purchases', 'purchase_details.purchase_id', '=', 'purchases.id')
            ->join('users', 'users.id', '=', 'purchases.created_by')
            ->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id') // Joignez la table des fournisseurs
            // AJOUT DU FILTRE PAR L'UTILISATEUR CRÉATEUR ET L'ENTREPRISE ACTIVE
            ->where('purchases.created_by', $userId)
            ->where('purchases.company_id', $activeCompanyId) // FILTRAGE PAR ENTREPRISE
            ->whereBetween('purchases.date', [$sDate, $eDate])
            ->where('purchases.status', PurchaseStatus::APPROVED)
            ->select(
                'purchases.purchase_no',
                'purchases.date',
                'suppliers.name as supplier_name',
                'products.code',
                'products.name',
                'purchase_details.quantity',
                'purchase_details.unitcost',
                'purchase_details.total',
                'users.name as created_by'
            )
            ->get();

        $purchase_array[] = [
            'Date',
            'No Purchase',
            'Supplier',
            'Product Code',
            'Product',
            'Quantity',
            'Unitcost',
            'Total',
            'Created By'
        ];

        foreach ($purchases as $purchase) {
            $purchase_array[] = [
                'Date' => $purchase->date,
                'No Purchase' => $purchase->purchase_no,
                'Supplier' => $purchase->supplier_name,
                'Product Code' => $purchase->code,
                'Product' => $purchase->name,
                'Quantity' => $purchase->quantity,
                'Unitcost' => $purchase->unitcost,
                'Total' => $purchase->total,
                'Created By' => $purchase->created_by
            ];
        }

        $this->exportExcel($purchase_array);
    }

    public function exportExcel($products)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '4000M');

        try {
            $spreadSheet = new Spreadsheet();
            $spreadSheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);
            $spreadSheet->getActiveSheet()->fromArray($products);
            $Excel_writer = new Xls($spreadSheet);
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="purchase-report.xls"');
            header('Cache-Control: max-age=0');
            ob_end_clean();
            $Excel_writer->save('php://output');
            exit();
        } catch (Exception $e) {
            \Log::error("Erreur lors de l'exportation du rapport d'achat : " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return back()->withErrors(['error' => 'Une erreur est survenue lors de l\'exportation du rapport.']);
        }
    }
}
