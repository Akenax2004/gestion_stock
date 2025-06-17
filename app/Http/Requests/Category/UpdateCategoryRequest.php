<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;    // NOUVEAU : Importez la façade Auth
use Illuminate\Validation\Rule;         // NOUVEAU : Importez Rule pour des règles uniques plus complexes
use Illuminate\Support\Facades\Session; // NOUVEAU : Importez la façade Session
use Illuminate\Support\Str;             // NOUVEAU : Importez Str pour la génération de slug

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à effectuer cette requête.
     * L'autorisation doit être basée sur l'authentification, la sélection d'une entreprise active,
     * et la propriété de la catégorie par l'utilisateur dans l'entreprise active.
     */
    public function authorize(): bool
    {
        // L'utilisateur doit être connecté
        // Une entreprise active doit être sélectionnée en session
        // La catégorie en cours de modification doit appartenir à l'utilisateur connecté ET à l'entreprise active
        return Auth::check() && Session::has('active_company_id') &&
               $this->route('category')->user_id === Auth::id() &&
               $this->route('category')->company_id === Session::get('active_company_id');
    }

    /**
     * Obtient les règles de validation qui s'appliquent à la requête.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $category = $this->route('category'); // Récupère l'instance de la catégorie depuis la route
        $userId = Auth::id();
        $activeCompanyId = Session::get('active_company_id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // La règle unique est maintenant composée : 'name' doit être unique pour cet 'user_id' ET ce 'company_id',
                // tout en ignorant l'enregistrement de la catégorie actuelle ($category->id)
                Rule::unique('categories')->where(function ($query) use ($userId, $activeCompanyId) {
                    return $query->where('user_id', $userId)
                                 ->where('company_id', $activeCompanyId);
                })->ignore($category->id),
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash', // Permet lettres, chiffres, tirets et underscores
                // La règle unique est également composée pour 'slug', ignorant la catégorie actuelle
                Rule::unique('categories')->where(function ($query) use ($userId, $activeCompanyId) {
                    return $query->where('user_id', $userId)
                                 ->where('company_id', $activeCompanyId);
                })->ignore($category->id),
            ],
            'short_code' => [ // Si ce champ est utilisé et doit être unique par entreprise
                'nullable',
                'string',
                'max:50',
                Rule::unique('categories')->where(function ($query) use ($userId, $activeCompanyId) {
                    return $query->where('user_id', $userId)
                                 ->where('company_id', $activeCompanyId);
                })->ignore($category->id),
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
            'name.unique' => 'Ce nom de catégorie existe déjà pour votre entreprise.',
            'slug.unique' => 'Ce slug de catégorie existe déjà pour votre entreprise.',
            'slug.alpha_dash' => 'Le slug ne doit contenir que des lettres, des chiffres, des tirets et des underscores.',
            'short_code.unique' => 'Ce code court de catégorie existe déjà pour votre entreprise.',
        ];
    }
}
