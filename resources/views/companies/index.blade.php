<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>Mes Entreprises</title>
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

        /* Flexbox */
        .flex { display: flex; }
        .justify-between { justify-content: space-between; }
        .items-center { align-items: center; }

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
        .text-xs { font-size: 0.75rem; line-height: 1rem; } /* Répété, mais assure sa présence */

        /* Sizing and Spacing */
        .w-10 { width: 2.5rem; }
        .h-10 { height: 2.5rem; }
        .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
        .px-4 { padding-left: 1rem; padding-right: 1rem; }
        .px-5 { padding-left: 1.25rem; padding-right: 1.25rem; }
        .py-3 { padding-top: 0.75rem; padding-bottom: 0.75rem; }
        .py-5 { padding-top: 1.25rem; padding-bottom: 1.25rem; }
        .mr-3 { margin-right: 0.75rem; }

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
        .sm\:inline { display: inline; } /* Pas de @media sm pour l'exemple, mais garde la classe */
        .overflow-hidden { overflow: hidden; }
        .min-w-full { min-width: 100%; }
        .object-cover { object-fit: cover; }
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-center { justify-content: center; }
        .inline-block { display: inline-block; }
        .absolute { position: absolute; }
        .inset-0 { top: 0; right: 0; bottom: 0; left: 0; }
        .opacity-50 { opacity: 0.5; }
        .list-disc { list-style-type: disc; }
        .list-inside { list-style-position: inside; }

        /* Styles spécifiques pour le bouton de confirmation de suppression */
        form.inline-block button[type="submit"] {
            background: none;
            border: none;
            padding: 0;
            margin: 0;
            font: inherit;
            cursor: pointer;
        }
    </style>

    <!-- Custom CSS for specific page. -->
</head>
<body>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Mes Entreprises</h1>
            <a href="{{ route('companies.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300">
                Créer une nouvelle entreprise
            </a>
        </div>

        @php
            // Simulation des données de session/erreurs pour la démonstration HTML statique.
            // Dans un vrai Blade, ces variables seraient définies par Laravel.
            $sessionSuccess = session('success') ?? null;
            $errorsBag = $errors ?? collect();
        @endphp

        @if ($sessionSuccess)
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ $sessionSuccess }}</span>
            </div>
        @endif
        @if ($errorsBag->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul class="list-disc list-inside">
                    @foreach ($errorsBag->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(isset($companies) && $companies->isEmpty())
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">Vous n'avez pas encore d'entreprise enregistrée.</span>
            </div>
        @elseif(isset($companies) && $companies->isNotEmpty())
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Logo
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Nom
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Email
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Téléphone
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Statut
                            </th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($companies as $company)
                            <tr>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    {{-- CORRIGÉ : Le lien doit appeler companies.select --}}
                                    <a href="{{ route('companies.select', $company->id) }}">
                                        @if($company->logo)
                                            <img src="{{ asset('storage/companies/logos/' . $company->logo) }}" alt="{{ $company->name ?? 'Logo' }}" class="w-10 h-10 rounded-full object-cover">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 text-xs">N/A</div>
                                        @endif
                                    </a>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    {{-- CORRIGÉ : Le lien doit appeler companies.select --}}
                                    <a href="{{ route('companies.select', $company->id) }}" class="text-blue-600 hover:text-blue-900 font-semibold">
                                        <p class="text-gray-900 whitespace-no-wrap">{{ $company->name ?? '' }}</p>
                                    </a>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">{{ $company->email ?? 'N/A' }}</p>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <p class="text-gray-900 whitespace-no-wrap">{{ $company->phone ?? 'N/A' }}</p>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
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
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    <a href="{{ route('companies.show', $company) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Voir</a>
                                    <a href="{{ route('companies.edit', $company) }}" class="text-yellow-600 hover:text-yellow-900 mr-3">Modifier</a>
                                    <form action="{{ route('companies.destroy', $company) }}" method="POST" class="inline-block" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette entreprise ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            {{-- Ce bloc s'affiche si $companies n'est pas défini du tout (par exemple, si le contrôleur ne le passe pas) --}}
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">Aucune donnée d'entreprise disponible.</span>
            </div>
        @endif
    </div>

    <!-- Script files (si nécessaire pour Tabler ou d'autres fonctionnalités JS) -->
    <script src="{{ asset('dist/js/tabler.min.js') }}" defer></script>
</body>
</html>
