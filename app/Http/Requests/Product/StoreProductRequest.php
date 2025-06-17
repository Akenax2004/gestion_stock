<?php

namespace App\Http\Requests\Product;

use Illuminate\Support\Str;
use Illuminate\Foundation\Http\FormRequest;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\Auth;    // NOUVEAU : Importez la façade Auth
use Illuminate\Validation\Rule;         // NOUVEAU : Importez Rule pour des règles uniques plus complexes
use Illuminate\Support\Facades\Session; // NOUVEAU : Importez la façade Session

class StoreProductRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à effectuer cette requête.
     * L'autorisation doit être basée sur l'authentification et la sélection d'une entreprise active.
     */
    public function authorize(): bool
    {
        // L'utilisateur doit être connecté ET avoir une entreprise active sélectionnée.
        return Auth::check() && Session::has('active_company_id');
    }

    /**
     * Obtient les règles de validation qui s'appliquent à la requête.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        return [
            'product_image' => 'nullable|image|file|max:2048',
            'name' => [
                'required',
                'string',
                'max:255', // Ajout d'une max: length par sécurité
                // S'assurer que le nom du produit est unique pour cette entreprise et cet utilisateur
                Rule::unique('products')->where(function ($query) use ($userId, $activeCompanyId) {
                    return $query->where('user_id', $userId)
                                 ->where('company_id', $activeCompanyId);
                }),
            ],
            'slug' => [
                'required',
                'string',
                'max:255', // Ajout d'une max: length par sécurité
                // Le slug doit être unique pour cette entreprise et cet utilisateur
                Rule::unique('products')->where(function ($query) use ($userId, $activeCompanyId) {
                    return $query->where('user_id', $userId)
                                 ->where('company_id', $activeCompanyId);
                }),
            ],
            // Le 'code' est généré dans prepareForValidation, mais si vous voulez une validation ici:
            'code' => [
                'nullable', // ou 'required' si vous voulez le valider même s'il est généré
                'string',
                'max:255',
                // S'assurer que le code est unique pour cette entreprise et cet utilisateur
                Rule::unique('products')->where(function ($query) use ($userId, $activeCompanyId) {
                    return $query->where('user_id', $userId)
                                 ->where('company_id', $activeCompanyId);
                }),
            ],
            'category_id' => [
                'required',
                'integer',
                // Vérifie que la category_id existe ET qu'elle appartient à l'utilisateur connecté ET à l'entreprise active
                Rule::exists('categories', 'id')->where(function ($query) use ($userId, $activeCompanyId) {
                    return $query->where('user_id', $userId)
                                 ->where('company_id', $activeCompanyId);
                }),
            ],
            'unit_id' => [
                'required',
                'integer',
                // Vérifie que l'unit_id existe ET qu'elle appartient à l'utilisateur connecté ET à l'entreprise active
                Rule::exists('units', 'id')->where(function ($query) use ($userId, $activeCompanyId) {
                    return $query->where('user_id', $userId)
                                 ->where('company_id', $activeCompanyId);
                }),
            ],
            'quantity' => 'required|integer|min:0', // Ajout de min:0
            'buying_price' => 'required|integer|min:0',
            'selling_price' => 'required|integer|min:0',
            'quantity_alert' => 'required|integer|min:0',
            'tax' => 'nullable|numeric|min:0',
            'tax_type' => 'nullable|integer', // ou 'in:fixed,percent' si c'est un enum ou string type
            'notes' => 'nullable|string|max:1000'
        ];
    }

    /**
     * Prépare les données pour la validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->name, '-'),
            // Le code est généré ici. La règle 'unique' dans 'rules()' gérera les doublons.
            'code' => IdGenerator::generate([
                'table' => 'products',
                'field' => 'code',
                'length' => 4,
                'prefix' => 'PC'
                // Pour IdGenerator, l'unicité par 'company_id' doit être gérée soit
                // par une logique personnalisée dans le générateur lui-même,
                // soit via la règle Rule::unique dans la méthode rules(), comme ci-dessus.
                // Ici, il génère un code unique globalement, la règle ci-dessus restreint cela par entreprise.
            ])
        ]);
    }

    /**
     * Personnalise les messages d'erreur de validation.
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'Ce nom de produit existe déjà pour votre entreprise.',
            'slug.unique' => 'Ce slug de produit existe déjà pour votre entreprise.',
            'code.unique' => 'Ce code de produit existe déjà pour votre entreprise.',
            'category_id.exists' => 'La catégorie sélectionnée est invalide ou n\'appartient pas à votre entreprise.',
            'unit_id.exists' => 'L\'unité sélectionnée est invalide ou n\'appartient pas à votre entreprise.',
            // Ajoutez d'autres messages personnalisés si nécessaire
        ];
    }
}
