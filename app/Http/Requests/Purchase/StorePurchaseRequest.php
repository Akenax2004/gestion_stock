<?php

namespace App\Http\Requests\Purchase;

use App\Enums\PurchaseStatus;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;    // NOUVEAU : Importez la façade Auth
use Illuminate\Validation\Rule;         // NOUVEAU : Importez Rule pour des règles de validation plus complexes
use Illuminate\Support\Facades\Session; // NOUVEAU : Importez la façade Session
use Illuminate\Support\Carbon;          // NOUVEAU : Importez Carbon pour la date

class StorePurchaseRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        return [
            'supplier_id' => [
                'required',
                'integer',
                // NOUVEAU : Vérifie que le supplier_id existe DANS LA TABLE suppliers
                // ET qu'il appartient à l'utilisateur connecté ET à l'entreprise active.
                Rule::exists('suppliers', 'id')->where(function ($query) use ($userId, $activeCompanyId) {
                    return $query->where('user_id', $userId)
                                 ->where('company_id', $activeCompanyId);
                }),
            ],
            'date' => 'required|date_format:Y-m-d', // Assurez-vous que le format de date est correct
            'total_amount' => 'required|numeric|min:0', // Le montant total doit être un nombre non négatif
            'status' => 'required|integer', // Utilisation de 'integer' pour l'Enum value
            'purchase_no' => [
                'required',
                'string',
                'max:255',
                // OPTIONNEL : Si purchase_no doit être unique par entreprise, ajoutez cette règle.
                // Notez que IdGenerator peut déjà gérer l'unicité globale. Pour l'unicité par entreprise,
                // vous devrez peut-être ajuster IdGenerator ou faire une vérification manuelle.
                // Rule::unique('purchases', 'purchase_no')->where(function ($query) use ($activeCompanyId) {
                //     return $query->where('company_id', $activeCompanyId);
                // }),
            ],
            // Vous pourriez avoir d'autres validations pour invoiceProducts si elles sont envoyées via le FormRequest.
        ];
    }

    /**
     * Prépare les données pour la validation.
     * Définit les champs calculés ou générés avant l'application des règles.
     */
    public function prepareForValidation(): void
    {
        // Générer le numéro d'achat ici.
        $generatedPurchaseNo = IdGenerator::generate([
            'table' => 'purchases',
            'field' => 'purchase_no',
            'length' => 10,
            'prefix' => 'PRS-'
            // Comme mentionné précédemment, IdGenerator génère globalement unique.
            // La règle 'unique' dans 'rules()' est le moyen d'appliquer l'unicité par entreprise.
        ]);

        $this->merge([
            'purchase_no' => $generatedPurchaseNo,
            'status' => PurchaseStatus::PENDING->value, // Définit le statut par défaut comme PENDING
            'created_by' => Auth::id(), // Associe l'achat à l'utilisateur connecté
            'company_id' => Session::get('active_company_id'), // NOUVEAU : Associe l'achat à l'entreprise active
            'date' => Carbon::parse($this->date)->format('Y-m-d'), // S'assurer que la date est au bon format
        ]);
    }

    /**
     * Personnalise les messages d'erreur de validation.
     */
    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Le fournisseur est obligatoire.',
            'supplier_id.integer' => 'L\'ID du fournisseur doit être un nombre entier.',
            'supplier_id.exists' => 'Le fournisseur sélectionné est invalide ou n\'appartient pas à votre entreprise.',
            'date.required' => 'La date d\'achat est obligatoire.',
            'date.date_format' => 'Le format de la date d\'achat est invalide (attendu : YYYY-MM-DD).',
            'total_amount.required' => 'Le montant total est obligatoire.',
            'total_amount.numeric' => 'Le montant total doit être un nombre.',
            'total_amount.min' => 'Le montant total ne peut pas être négatif.',
            'status.required' => 'Le statut de l\'achat est obligatoire.',
            'status.integer' => 'Le statut de l\'achat est invalide.',
            'purchase_no.required' => 'Le numéro d\'achat est obligatoire.',
            // 'purchase_no.unique' => 'Ce numéro d\'achat existe déjà pour votre entreprise.', // Décommenter si la règle unique est appliquée
        ];
    }
}
