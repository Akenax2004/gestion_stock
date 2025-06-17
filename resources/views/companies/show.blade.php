<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>Détails Entreprise - {{ config('app.name', 'Mon Application') }}</title>
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
        .flex-wrap { flex-wrap: wrap; }
        .gap-4 { gap: 1rem; }

        /* Typography */
        .text-3xl { font-size: 1.875rem; line-height: 2.25rem; }
        .font-bold { font-weight: 700; }
        .text-gray-800 { color: #2d3748; }
        .text-white { color: #fff; }
        .text-left { text-align: left; }
        .text-lg { font-size: 1.125rem; line-height: 1.75rem; }
        .font-semibold { font-weight: 600; }
        .text-gray-600 { color: #4a5568; }
        .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
        .text-gray-900 { color: #1a202c; }
        .leading-tight { line-height: 1.25; }
        .text-green-900 { color: #1a4d2e; }
        .text-red-900 { color: #660000; }
        .text-indigo-600 { color: #4338ca; }
        .hover\:text-indigo-900:hover { color: #282181; }
        .text-yellow-600 { color: #d69e2e; }
        .hover\:text-yellow-900:hover { color: #8B5F22; }
        .text-blue-600 { color: #2563eb; }
        .hover\:text-blue-900:hover { color: #1e40af; }

        /* Sizing and Spacing */
        .w-24 { width: 6rem; }
        .h-24 { height: 6rem; }
        .w-full { width: 100%; }
        .p-4 { padding: 1rem; }
        .pb-4 { padding-bottom: 1rem; }
        .px-4 { padding-left: 1rem; padding-right: 1rem; }
        .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }

        /* Borders and Shadows */
        .rounded-lg { border-radius: 0.5rem; }
        .rounded-full { border-radius: 9999px; }
        .shadow-md { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        .border { border-width: 1px; }
        .border-gray-200 { border-color: #edf2f7; }

        /* Backgrounds */
        .bg-white { background-color: #fff; }
        .bg-blue-600 { background-color: #2563eb; }
        .hover\:bg-blue-700:hover { background-color: #1d4ed8; }

        /* Other */
        .object-cover { object-fit: cover; }
    </style>
</head>
<body>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Détails de l'entreprise : {{ $company->name ?? '' }}</h1>
            <div class="flex gap-4">
                <a href="{{ route('companies.edit', $company) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300">
                    Modifier
                </a>
                <a href="{{ route('companies.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300">
                    Retour à la liste
                </a>
            </div>
        </div>

        @php
            // Simulation des données pour la démonstration HTML statique.
            // Dans un vrai Blade, $company serait passé par le contrôleur.
            $company = $company ?? (object)[
                'name' => 'Nom de l\'entreprise',
                'email' => 'contact@entreprise.com',
                'phone' => '123-456-7890',
                'address' => '123 Rue de l\'Exemple, Ville',
                'vat_number' => 'FR123456789',
                'logo' => null, // ou 'path/to/logo.png'
                'is_active' => true,
                'created_at' => \Carbon\Carbon::now()->subMonths(2)->format('d/m/Y H:i'),
                'updated_at' => \Carbon\Carbon::now()->format('d/m/Y H:i'),
            ];
        @endphp

        <div class="bg-white shadow-md rounded-lg p-4">
            <div class="flex flex-wrap items-center gap-4 pb-4 border-b border-gray-200 mb-4">
                @if($company->logo)
                    <img src="{{ asset('storage/companies/logos/' . $company->logo) }}" alt="{{ $company->name ?? 'Logo' }}" class="w-24 h-24 rounded-full object-cover shadow-md">
                @else
                    <div class="w-24 h-24 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 text-lg font-bold">N/A</div>
                @endif
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $company->name ?? '' }}</h2>
                    <p class="text-gray-600">{{ $company->email ?? 'N/A' }}</p>
                    <p class="text-gray-600">{{ $company->phone ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-700 text-sm font-semibold">Adresse :</p>
                    <p class="text-gray-900">{{ $company->address ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-700 text-sm font-semibold">Numéro de TVA :</p>
                    <p class="text-gray-900">{{ $company->vat_number ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-700 text-sm font-semibold">Statut :</p>
                    @if(isset($company->is_active) && $company->is_active)
                        <span class="relative inline-block px-3 py-1 font-semibold text-green-900 leading-tight">
                            <span aria-hidden class="absolute inset-0 bg-green-200 opacity-50 rounded-full"></span>
                            <span class="relative">Actif</span>
                        </span>
                    @else
                        <span class="relative inline-block px-3 py-1 font-semibold text-red-900 leading-tight">
                            <span aria-hidden class="absolute inset-0 bg-red-200 opacity-50 rounded-full"></span>
                            <span class="relative">Inactif</span>
                        </span>
                    @endif
                </div>
                <div>
                    <p class="text-gray-700 text-sm font-semibold">Date de création :</p>
                    <p class="text-gray-900">{{ $company->created_at ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-700 text-sm font-semibold">Dernière mise à jour :</p>
                    <p class="text-gray-900">{{ $company->updated_at ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Script files -->
    <script src="{{ asset('dist/js/tabler.min.js') }}" defer></script>
</body>
</html>
