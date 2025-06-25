<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User; // Assurez-vous d'importer votre modèle User

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Réinitialise le cache des permissions (recommandé avant de recréer les permissions)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Création des permissions (inchangé, toutes les permissions nécessaires)
        // Permissions spécifiques à l'admin principal
        $manageCompanies = Permission::firstOrCreate(['name' => 'manage-companies', 'guard_name' => 'web']);
        $manageLicences = Permission::firstOrCreate(['name' => 'manage-licences', 'guard_name' => 'web']);
        $manageUsersPerCompany = Permission::firstOrCreate(['name' => 'manage-users-per-company', 'guard_name' => 'web']); // Pour gérer les utilisateurs secondaires par entreprise

        // Permissions générales de gestion de stock, commandes, etc.
        $viewProducts = Permission::firstOrCreate(['name' => 'view-products', 'guard_name' => 'web']);
        $createProduct = Permission::firstOrCreate(['name' => 'create-product', 'guard_name' => 'web']);
        $editProduct = Permission::firstOrCreate(['name' => 'edit-product', 'guard_name' => 'web']);
        $deleteProduct = Permission::firstOrCreate(['name' => 'delete-product', 'guard_name' => 'web']);
        $manageStockIn = Permission::firstOrCreate(['name' => 'manage-stock-in', 'guard_name' => 'web']); // Import
        $manageStockOut = Permission::firstOrCreate(['name' => 'manage-stock-out', 'guard_name' => 'web']); // Export
        $createOrder = Permission::firstOrCreate(['name' => 'create-order', 'guard_name' => 'web']);
        $viewOrders = Permission::firstOrCreate(['name' => 'view-orders', 'guard_name' => 'web']);
        $editOrder = Permission::firstOrCreate(['name' => 'edit-order', 'guard_name' => 'web']);
        $viewReports = Permission::firstOrCreate(['name' => 'view-reports', 'guard_name' => 'web']);
        $manageCustomers = Permission::firstOrCreate(['name' => 'manage-customers', 'guard_name' => 'web']);
        $manageSuppliers = Permission::firstOrCreate(['name' => 'manage-suppliers', 'guard_name' => 'web']);
        $manageCategories = Permission::firstOrCreate(['name' => 'manage-categories', 'guard_name' => 'web']);
        $manageUnits = Permission::firstOrCreate(['name' => 'manage-units', 'guard_name' => 'web']);
        $managePurchases = Permission::firstOrCreate(['name' => 'manage-purchases', 'guard_name' => 'web']);
        $manageQuotations = Permission::firstOrCreate(['name' => 'manage-quotations', 'guard_name' => 'web']);


        // 2. Création des rôles (inchangé)
        $adminPrincipalRole = Role::firstOrCreate(['name' => 'admin_principal', 'guard_name' => 'web']);
        $gestionRole = Role::firstOrCreate(['name' => 'gestion', 'guard_name' => 'web']);
        $venteRole = Role::firstOrCreate(['name' => 'vente', 'guard_name' => 'web']);

        // 3. Attribution des permissions aux rôles

        // Rôle admin_principal: a toutes les permissions
        // S'assurer qu'il a toutes les permissions existantes, puis celles créées ci-dessus.
        $adminPrincipalRole->givePermissionTo(Permission::all());

        // Rôle gestion: peut tout gérer sauf les entreprises, licences et gestion des utilisateurs secondaires
        $gestionRole->syncPermissions([ // Utilise syncPermissions pour remplacer toutes les permissions précédentes
            $viewProducts, $createProduct, $editProduct, $deleteProduct,
            $manageStockIn, $manageStockOut,
            $createOrder, $viewOrders, $editOrder, // Les permissions d'ordre
            $viewReports,
            $manageCustomers, $manageSuppliers,
            $manageCategories, $manageUnits,
            $managePurchases, $manageQuotations,
        ]);

        // Rôle vente: a des permissions limitées (principalement les ventes/commandes et la consultation)
        $venteRole->syncPermissions([ // Utilise syncPermissions pour remplacer toutes les permissions précédentes
            $viewProducts,           // Peut voir les produits
            // Pas de createProduct, editProduct, deleteProduct pour 'vente'
            $manageStockOut,         // Peut gérer la sortie de stock (pour les ventes/commandes)
            $createOrder,            // Peut créer des commandes
            $viewOrders,             // Peut voir les commandes (ajouté pour cohérence)
            $editOrder,              // Peut éditer les commandes (ajouté pour cohérence si nécessaire)
            $viewReports,            // Peut voir les rapports
            $manageCustomers,        // Peut gérer les clients (créer/voir)
            // Pas de manageSuppliers pour 'vente'
            // Pas de manageCategories, manageUnits pour 'vente'
            $manageQuotations,       // Peut gérer les devis (inclut la création)
            // Pas de managePurchases pour 'vente'
        ]);

        // 4. Attribuer le rôle 'admin_principal' au premier utilisateur existant (si applicable)
        // C'est utile si vous avez déjà un utilisateur créé et que vous voulez qu'il soit l'admin initial.
        // Sinon, le RegisteredUserController s'en chargera pour les nouvelles inscriptions.
        $user = User::first(); // Récupère le premier utilisateur. ATTENTION: ceci peut attribuer le rôle à un utilisateur de test.
        if ($user && !$user->hasRole('admin_principal')) {
            $user->assignRole('admin_principal');
            $this->command->info("Rôle 'admin_principal' attribué à l'utilisateur ID: " . $user->id);
        } else {
            $this->command->info("Le rôle 'admin_principal' n'a pas été attribué au premier utilisateur (soit il n'existe pas, soit il l'a déjà).");
        }
    }
}
