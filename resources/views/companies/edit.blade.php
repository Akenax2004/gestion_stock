<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>Modifier une entreprise - {{ config('app.name', 'Mon Application') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />

    <!-- CSS files -->
    <link href="{{ asset('dist/css/tabler.min.css') }}" rel="stylesheet"/>
    <style>
        @import url('https://rsms.me/inter/inter.css');
        :root {
            --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
        }
        body {
            font-feature-settings: "cv03", "cv04", "cv11";
        }

        /* Styles Tailwind CSS de base pour le conteneur et les alertes */
        .container {
            width: 100%;
            margin-right: auto;
            margin-left: auto;
            padding-right: 1rem;
            padding-left: 1rem;
        }
        @media (min-width: 640px) {
            .container { max-width: 640px; }
        }
        @media (min-width: 768px) {
            .container { max-width: 768px; }
        }
        @media (min-width: 1024px) {
            .container { max-width: 1024px; }
        }
        @media (min-width: 1280px) {
            .container { max-width: 1280px; }
        }
        .mx-auto { margin-left: auto; margin-right: auto; }
        .px-4 { padding-left: 1rem; padding-right: 1rem; }
        .py-8 { padding-top: 2rem; padding-bottom: 2rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mt-3 { margin-top: 0.75rem; }
        .my-4 { margin-top: 1rem; margin-bottom: 1rem; }
        .mb-3 { margin-bottom: 0.75rem; }

        /* Flexbox */
        .flex { display: flex; }
        .justify-between { justify-content: space-between; }
        .items-center { align-items: center; }
        .gap-4 { gap: 1rem; }

        /* Typography */
        .text-3xl { font-size: 1.875rem; line-height: 2.25rem; }
        .font-bold { font-weight: 700; }
        .text-gray-800 { color: #2d3748; }
        .text-white { color: #fff; }
        .text-left { text-align: left; }
        .text-xs { font-size: 0.75rem; line-height: 1rem; }
        .font-semibold { font-weight: 600; }
        .text-gray-600 { color: #4a5568; }
        .uppercase { text-transform: uppercase; }
        .tracking-wider { letter-spacing: 0.05em; }
        .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
        .text-gray-900 { color: #1a202c; }
        .whitespace-no-wrap { white-space: nowrap; }
        .leading-tight { line-height: 1.25; }
        .text-green-900 { color: #1a4d2e; }
        .text-red-900 { color: #660000; }
        .text-yellow-700 { color: #975A16; }
        .text-indigo-600 { color: #4338ca; }
        .hover\:text-indigo-900:hover { color: #282181; }
        .text-yellow-600 { color: #d69e2e; }
        .hover\:text-yellow-900:hover { color: #8B5F22; }
        .text-red-600 { color: #dc2626; }
        .hover\:text-red-900:hover { color: #991b1b; }
        .text-base { font-size: 1rem; line-height: 1.5rem; }
        .text-gray-700 { color: #4a5568; }

        /* Sizing and Spacing */
        .w-10 { width: 2.5rem; }
        .h-10 { height: 2.5rem; }
        .w-full { width: 100%; }
        .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
        .px-4 { padding-left: 1rem; padding-right: 1rem; }
        .px-5 { padding-left: 1.25rem; padding-right: 1.25rem; }
        .py-3 { padding-top: 0.75rem; padding-bottom: 0.75rem; }
        .py-5 { padding-top: 1.25rem; padding-bottom: 1.25rem; }
        .mr-3 { margin-right: 0.75rem; }
        .p-4 { padding: 1rem; }

        /* Borders and Shadows */
        .rounded-lg { border-radius: 0.5rem; }
        .rounded-full { border-radius: 9999px; }
        .shadow-md { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        .border { border-width: 1px; }
        .border-green-400 { border-color: #48bb78; }
        .border-red-400 { border-color: #ef4444; }
        .border-yellow-400 { border-color: #F6E05E; }
        .border-b-2 { border-bottom-width: 2px; }
        .border-gray-200 { border-color: #edf2f7; }
        .border-b { border-bottom-width: 1px; }
        .border-gray-300 { border-color: #d1d5db; }

        /* Backgrounds */
        .bg-blue-600 { background-color: #2563eb; }
        .hover\:bg-blue-700:hover { background-color: #1d4ed8; }
        .bg-green-100 { background-color: #edfdf1; }
        .bg-red-100 { background-color: #fef2f2; }
        .bg-yellow-100 { background-color: #FEF3C7; }
        .bg-white { background-color: #fff; }
        .bg-gray-100 { background-color: #f7fafc; }
        .bg-gray-300 { background-color: #d1d5db; }
        .bg-green-200 { background-color: #a7f3d0; }
        .bg-red-200 { background-color: #fecaca; }

        /* Transitions */
        .transition { transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform; }
        .duration-300 { transition-duration: 0.3s; }

        /* Other */
        .rounded { border-radius: 0.25rem; }
        .relative { position: relative; }
        .block { display: block; }
        .sm\:inline { display: inline; }
        .overflow-hidden { overflow: hidden; }
        .min-w-full { min-width: 100%; }
        .object-cover { object-fit: cover; }
        .inline-block { display: inline-block; }
        .absolute { position: absolute; }
        .inset-0 { top: 0; right: 0; bottom: 0; left: 0; }
        .opacity-50 { opacity: 0.5; }
        .list-disc { list-style-type: disc; }
        .list-inside { list-style-position: inside; }
        .form-control {
            display: block;
            width: 100%;
            padding: 0.5rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .form-check-input {
            width: 1rem;
            height: 1rem;
            margin-top: 0.25rem;
            margin-right: 0.25rem;
            vertical-align: top;
            background-color: #fff;
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
            border: 1px solid rgba(0, 0, 0, 0.25);
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            border-radius: 0.25rem;
            transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .form-check-input:checked {
            background-color: #2563eb;
            border-color: #2563eb;
        }
        .btn {
            display: inline-block;
            font-weight: 400;
            color: #212529;
            text-align: center;
            vertical-align: middle;
            cursor: pointer;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            background-color: transparent;
            border: 1px solid transparent;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .btn-primary {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            color: #fff;
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .btn-secondary {
            color: #fff;
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .btn-secondary:hover {
            color: #fff;
            background-color: #5c636a;
            border-color: #5c636a;
        }
    </style>
</head>
<body>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Modifier l'entreprise : {{ $company->name ?? '' }}</h1>
            <a href="{{ route('companies.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300">
                Retour à la liste
            </a>
        </div>

        @php
            // Simulation des données pour la démonstration HTML statique.
            // Dans un vrai Blade, $company serait passé par le contrôleur.
            // Utilisez old('field', $company->field) pour pré-remplir et gérer les erreurs de validation
            $company = $company ?? (object)[
                'name' => 'Nom de l\'entreprise',
                'email' => 'contact@entreprise.com',
                'phone' => '123-456-7890',
                'address' => '123 Rue de l\'Exemple, Ville',
                'vat_number' => 'FR123456789',
                'logo' => null, // ou 'path/to/logo.png'
                'is_active' => true,
            ];
            $errorsBag = $errors ?? collect();
        @endphp

        @if ($errorsBag->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul class="list-disc list-inside">
                    @foreach ($errorsBag->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white shadow-md rounded-lg p-4">
            <form action="{{ route('companies.update', $company) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT') {{-- Indique que c'est une requête PUT pour la mise à jour --}}

                <div class="mb-3">
                    <label for="name" class="form-label text-gray-700">Nom de l'entreprise <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $company->name) }}" required>
                    @error('name')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label text-gray-700">Email</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $company->email) }}">
                    @error('email')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label text-gray-700">Téléphone</label>
                    <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $company->phone) }}">
                    @error('phone')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label text-gray-700">Adresse</label>
                    <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror">{{ old('address', $company->address) }}</textarea>
                    @error('address')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="vat_number" class="form-label text-gray-700">Numéro de TVA</label>
                    <input type="text" name="vat_number" id="vat_number" class="form-control @error('vat_number') is-invalid @enderror" value="{{ old('vat_number', $company->vat_number) }}">
                    @error('vat_number')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="logo" class="form-label text-gray-700">Logo actuel</label>
                    @if($company->logo)
                        <div class="my-2">
                            <img src="{{ asset('storage/companies/logos/' . $company->logo) }}" alt="Logo actuel" class="w-20 h-20 rounded-full object-cover">
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="logo_removed" id="logo_removed" class="form-check-input" value="1">
                            <label class="form-check-label text-gray-700" for="logo_removed">Supprimer le logo actuel</label>
                        </div>
                    @else
                        <p class="text-gray-600">Aucun logo actuel.</p>
                    @endif
                    <label for="logo_new" class="form-label text-gray-700 mt-2">Charger un nouveau logo</label>
                    <input type="file" name="logo" id="logo_new" class="form-control @error('logo') is-invalid @enderror">
                    @error('logo')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', $company->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label text-gray-700" for="is_active">Active</label>
                    @error('is_active')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex gap-4 mt-4">
                    <button type="submit" class="btn btn-primary bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300">
                        Mettre à jour l'entreprise
                    </button>
                    <a href="{{ route('companies.index') }}" class="btn btn-secondary bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Script files -->
    <script src="{{ asset('dist/js/tabler.min.js') }}" defer></script>
</body>
</html>
