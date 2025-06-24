<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Entreprises</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-4xl">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Mes Entreprises</h1>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="mb-6 flex justify-between items-center">
            <a href="{{ route('manage.companies.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md">
                Créer une Nouvelle Entreprise
            </a>
            <a href="{{ route('choose-license') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded-md">
                Gérer ma Licence
            </a>
        </div>


        @if ($companies->isEmpty())
            <p class="text-center text-gray-600">Vous n'avez pas encore créé d'entreprises.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                            <th class="py-3 px-6 text-left">Nom</th>
                            <th class="py-3 px-6 text-left">Email</th>
                            <th class="py-3 px-6 text-left">Téléphone</th>
                            <th class="py-3 px-6 text-center">Statut</th>
                            <th class="py-3 px-6 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        @foreach ($companies as $company)
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-3 px-6 text-left whitespace-nowrap">{{ $company->name }}</td>
                                <td class="py-3 px-6 text-left">{{ $company->email ?? 'N/A' }}</td>
                                <td class="py-3 px-6 text-left">{{ $company->phone ?? 'N/A' }}</td>
                                <td class="py-3 px-6 text-center">
                                    <span class="relative inline-block px-3 py-1 font-semibold leading-tight">
                                        <span aria-hidden="true" class="absolute inset-0 opacity-50 rounded-full {{ $company->is_active ? 'bg-green-200' : 'bg-red-200' }}"></span>
                                        <span class="relative">{{ $company->is_active ? 'Actif' : 'Inactif' }}</span>
                                    </span>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <div class="flex item-center justify-center space-x-2">
                                        {{-- MODIFICATION ICI: Ajout du lien vers le dashboard de l'entreprise --}}
                                        <a href="{{ route('companies.select', $company) }}" class="w-6 h-6 transform hover:text-purple-500 hover:scale-110" title="Accéder au tableau de bord de {{ $company->name }}">
                                            <!-- Icone Vue/Dashboard de l'entreprise -->
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 6.943a1.994 1.994 0 010 .114C20.268 16.057 16.477 19 12 19c-4.478 0-8.268-2.943-9.542-6.943a1.994 1.994 0 010-.114z" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('manage.companies.edit', $company) }}" class="w-6 h-6 transform hover:text-blue-500 hover:scale-110" title="Éditer {{ $company->name }}">
                                            <!-- Icone Éditer -->
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('manage.companies.destroy', $company) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette entreprise ?');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-6 h-6 transform hover:text-red-500 hover:scale-110" title="Supprimer {{ $company->name }}">
                                                <!-- Icone Supprimer -->
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</body>
</html>
