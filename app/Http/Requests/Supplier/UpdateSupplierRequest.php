<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;    // NOUVEAU : Importez la façade Auth
use Illuminate\Validation\Rule;         // NOUVEAU : Importez Rule pour des règles uniques plus complexes
use Illuminate\Support\Facades\Session; // NOUVEAU : Importez la façade Session

class UpdateSupplierRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à effectuer cette requête.
     * L'autorisation doit être basée sur l'authentification, la sélection d'une entreprise active,
     * et la propriété du fournisseur par l'utilisateur dans l'entreprise active.
     */
    public function authorize(): bool
    {
        // L'utilisateur doit être connecté ET avoir une entreprise active sélectionnée.
        // De plus, le fournisseur en cours de modification doit appartenir à l'utilisateur connecté
        // ET à l'entreprise active.
        return Auth::check() && Session::has('active_company_id') &&
               $this->route('supplier')->user_id === Auth::id() &&
               $this->route('supplier')->company_id === Session::get('active_company_id');
    }

    /**
     * Obtient les règles de validation qui s'appliquent à la requête.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $supplierId = $this->route('supplier')->id; // Récupère l'ID du fournisseur depuis la route
        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        return [
            'photo' => 'nullable|image|file|max:1024',
            'name' => 'required|string|max:50',
            'email' => [
                'required',
                'email',
                'max:50',
                // L'email doit être unique pour cet 'user_id' ET ce 'company_id',
                // tout en ignorant l'enregistrement du fournisseur actuel ($supplierId)
                Rule::unique('suppliers')->where(function ($query) use ($userId, $activeCompanyId) {
                    return $query->where('user_id', $userId)
                                 ->where('company_id', $activeCompanyId);
                })->ignore($supplierId),
            ],
            'phone' => [
                'required',
                'string',
                'max:25',
                // Le téléphone doit être unique pour cet 'user_id' ET ce 'company_id',
                // tout en ignorant l'enregistrement du fournisseur actuel ($supplierId)
                Rule::unique('suppliers')->where(function ($query) use ($userId, $activeCompanyId) {
                    return $query->where('user_id', $userId)
                                 ->where('company_id', $activeCompanyId);
                })->ignore($supplierId),
            ],
            'shopname' => 'required|string|max:50',
            'type' => 'required|string|max:25', // Si 'type' est un enum, il pourrait y avoir une règle 'in' ici
            'account_holder' => 'nullable|string|max:50', // Rendu nullable
            'account_number' => 'nullable|string|max:25', // Rendu nullable
            'bank_name' => 'nullable|string|max:25',     // Rendu nullable
            'address' => 'required|string|max:100',
        ];
    }

    /**
     * Personnalise les messages d'erreur de validation (facultatif).
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Cet e-mail de fournisseur existe déjà pour votre entreprise.',
            'phone.unique' => 'Ce numéro de téléphone de fournisseur existe déjà pour votre entreprise.',
        ];
    }
}
