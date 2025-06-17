<?php

namespace App\Http\Requests\Quotation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;    // NOUVEAU : Importez la façade Auth
use Illuminate\Validation\Rule;         // NOUVEAU : Importez Rule pour des règles de validation plus complexes
use Illuminate\Support\Facades\Session; // NOUVEAU : Importez la façade Session

class StoreQuotationRequest extends FormRequest
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
            'customer_id' => [
                'required',
                'numeric',
                // NOUVEAU : Vérifie que le customer_id existe DANS LA TABLE customers
                // ET qu'il appartient à l'utilisateur connecté ET à l'entreprise active.
                Rule::exists('customers', 'id')->where(function ($query) use ($userId, $activeCompanyId) {
                    return $query->where('user_id', $userId)
                                 ->where('company_id', $activeCompanyId);
                }),
            ],
            'reference' => [
                'required',
                'string',
                'max:255',
                // OPTIONNEL : Si 'reference' doit être unique par entreprise, ajoutez cette règle.
                // Rule::unique('quotations', 'reference')->where(function ($query) use ($activeCompanyId) {
                //     return $query->where('company_id', $activeCompanyId);
                // }),
            ],
            'tax_percentage' => 'required|integer|min:0|max:100',
            'discount_percentage' => 'required|integer|min:0|max:100',
            'shipping_amount' => 'required|numeric|min:0', // Ajout de min:0
            'total_amount' => 'required|numeric|min:0', // Ajout de min:0
            'status' => 'required|string|max:255', // Si 'status' est une valeur d'Enum (string), c'est correct. Si c'est un entier, changez à 'integer'.
            'note' => 'nullable|string|max:1000',
            'date' => 'required|date_format:Y-m-d', // Assurez-vous que la date est soumise et valide
        ];
    }

    /**
     * Personnalise les messages d'erreur de validation (facultatif).
     */
    public function messages(): array
    {
        return [
            'customer_id.required' => 'Le client est obligatoire.',
            'customer_id.numeric' => 'L\'ID du client doit être un nombre.',
            'customer_id.exists' => 'Le client sélectionné est invalide ou n\'appartient pas à votre entreprise.',
            'reference.required' => 'La référence est obligatoire.',
            'reference.unique' => 'Cette référence de devis existe déjà pour votre entreprise.',
            'tax_percentage.required' => 'Le pourcentage de taxe est obligatoire.',
            'tax_percentage.integer' => 'Le pourcentage de taxe doit être un nombre entier.',
            'tax_percentage.min' => 'Le pourcentage de taxe ne peut pas être inférieur à 0.',
            'tax_percentage.max' => 'Le pourcentage de taxe ne peut pas être supérieur à 100.',
            'discount_percentage.required' => 'Le pourcentage de remise est obligatoire.',
            'discount_percentage.integer' => 'Le pourcentage de remise doit être un nombre entier.',
            'discount_percentage.min' => 'Le pourcentage de remise ne peut pas être inférieur à 0.',
            'discount_percentage.max' => 'Le pourcentage de remise ne peut pas être supérieur à 100.',
            'shipping_amount.required' => 'Les frais de port sont obligatoires.',
            'shipping_amount.numeric' => 'Les frais de port doivent être un nombre.',
            'shipping_amount.min' => 'Les frais de port ne peuvent pas être négatifs.',
            'total_amount.required' => 'Le montant total est obligatoire.',
            'total_amount.numeric' => 'Le montant total doit être un nombre.',
            'total_amount.min' => 'Le montant total ne peut pas être négatif.',
            'status.required' => 'Le statut du devis est obligatoire.',
            'note.max' => 'La note ne doit pas dépasser 1000 caractères.',
            'date.required' => 'La date du devis est obligatoire.',
            'date.date_format' => 'Le format de la date du devis est invalide (attendu : YYYY-MM-DD).',
        ];
    }
}
