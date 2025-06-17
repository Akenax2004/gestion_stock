<?php

namespace App\Http\Requests\Unit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;    // NOUVEAU : Importez la façade Auth
use Illuminate\Validation\Rule;         // NOUVEAU : Importez Rule pour des règles uniques plus complexes
use Illuminate\Support\Facades\Session; // NOUVEAU : Importez la façade Session
use Illuminate\Support\Str;             // NOUVEAU : Importez Str pour la génération de slug

class UpdateUnitRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à effectuer cette requête.
     * L'autorisation doit être basée sur l'authentification, la sélection d'une entreprise active,
     * et la propriété de l'unité par l'utilisateur dans l'entreprise active.
     */
    public function authorize(): bool
    {
        // L'utilisateur doit être connecté ET avoir une entreprise active sélectionnée.
        // De plus, l'unité en cours de modification doit appartenir à l'utilisateur connecté
        // ET à l'entreprise active.
        return Auth::check() && Session::has('active_company_id') &&
               $this->route('unit')->user_id === Auth::id() &&
               $this->route('unit')->company_id === Session::get('active_company_id');
    }

    /**
     * Obtient les règles de validation qui s'appliquent à la requête.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $unitId = $this->route('unit')->id; // Récupère l'ID de l'unité depuis la route
        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // Le nom doit être unique pour cet 'user_id' ET ce 'company_id',
                // tout en ignorant l'enregistrement de l'unité actuelle ($unitId)
                Rule::unique('units')->where(function ($query) use ($userId, $activeCompanyId) {
                    return $query->where('user_id', $userId)
                                 ->where('company_id', $activeCompanyId);
                })->ignore($unitId),
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                // Le slug doit être unique pour cet 'user_id' ET ce 'company_id',
                // tout en ignorant l'enregistrement de l'unité actuelle ($unitId)
                Rule::unique('units')->where(function ($query) use ($userId, $activeCompanyId) {
                    return $query->where('user_id', $userId)
                                 ->where('company_id', $activeCompanyId);
                })->ignore($unitId),
            ],
            'short_code' => [
                'nullable', // Gardé nullable comme dans votre migration
                'string',
                'max:255',
                // Le short_code doit être unique pour cet 'user_id' ET ce 'company_id',
                // tout en ignorant l'enregistrement de l'unité actuelle ($unitId)
                Rule::unique('units')->where(function ($query) use ($userId, $activeCompanyId) {
                    return $query->where('user_id', $userId)
                                 ->where('company_id', $activeCompanyId);
                })->ignore($unitId),
            ],
        ];
    }

    /**
     * Prépare les données pour la validation.
     * Permet de générer le slug automatiquement avant la validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->name), // Utilise Str au lieu de \Illuminate\Support\Str
        ]);
    }

    /**
     * Personnalise les messages d'erreur de validation (facultatif).
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'Ce nom d\'unité existe déjà pour votre entreprise.',
            'slug.unique' => 'Ce slug d\'unité existe déjà pour votre entreprise.',
            'slug.alpha_dash' => 'Le slug ne doit contenir que des lettres, des chiffres, des tirets et des underscores.',
            'short_code.unique' => 'Ce code court d\'unité existe déjà pour votre entreprise.',
        ];
    }
}
