<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choisir votre Licence</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-2xl">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Choisissez votre Plan de Licence</h1>

        @if (session('info'))
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('info') }}</span>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($currentUserLicence)
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-6">
                <p class="font-semibold">Votre licence actuelle :</p>
                <p>Plan: {{ $currentUserLicence->licencePlan->name }} ({{ $currentUserLicence->status }})</p>
                <p>Valide jusqu'au: {{ \Carbon\Carbon::parse($currentUserLicence->end_date)->format('d/m/Y') }}</p>
                <p>Entreprises Max: {{ $currentUserLicence->licencePlan->max_companies }}</p>
                <p>Utilisateurs/Entreprise Max: {{ $currentUserLicence->licencePlan->max_users_per_company }}</p>
                @if ($currentUserLicence->isActive())
                    <p class="text-green-800 font-bold">Votre licence est active !</p>
                @else
                    <p class="text-red-800 font-bold">Votre licence n'est pas active ou a expiré. Veuillez en choisir une nouvelle.</p>
                @endif
            </div>
        @else
            <p class="text-gray-600 text-center mb-6">Vous n'avez pas encore de licence. Veuillez en choisir une pour commencer.</p>
        @endif


        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($licencePlans as $plan)
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 flex flex-col items-center text-center">
                    <h2 class="text-2xl font-semibold text-gray-700 mb-2">{{ $plan->name }}</h2>
                    <p class="text-4xl font-bold text-indigo-600 mb-2">
                        @if ($plan->price_xof == 0)
                            Gratuit
                        @else
                            {{ number_format($plan->price_xof, 0, ',', ' ') . ' XOF' }}
                            @if ($plan->duration_type === \App\Models\LicencePlan::DURATION_MONTHLY)
                                /mois
                            @elseif ($plan->duration_type === \App\Models\LicencePlan::DURATION_ANNUAL)
                                /an
                            @endif
                        @endif
                    </p>
                    <p class="text-gray-500 mb-4">
                        @if ($plan->duration_type === \App\Models\LicencePlan::DURATION_MONTHLY)
                            <!-- Calcul du prix annuel pour les plans mensuels -->
                            Soit {{ number_format($plan->price_xof * 12, 0, ',', ' ') . ' XOF/an' }}
                        @elseif ($plan->duration_type === \App\Models\LicencePlan::DURATION_TRIAL)
                            (Essai de {{ $plan->duration_days }} jours)
                        @else
                            {{ ucfirst(strtolower($plan->duration_type)) }} ({{ $plan->duration_days }} jours)
                        @endif
                    </p>

                    <ul class="text-gray-600 text-left w-full mb-6 space-y-2">
                        <li class="flex items-center"><svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Jusqu'à {{ $plan->max_companies }} entreprises</li>
                        <li class="flex items-center"><svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Jusqu'à {{ $plan->max_users_per_company }} utilisateurs par entreprise</li>
                        {{-- SUPPRIMÉ: json_decode() car $plan->features est déjà un tableau grâce au casting Eloquent --}}
                        @foreach($plan->features as $feature) {{-- <-- MODIFICATION ICI --}}
                            <li class="flex items-center"><svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>{{ $feature }}</li>
                        @endforeach
                    </ul>

                    <form action="{{ route('process-purchase') }}" method="POST" class="w-full">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition duration-300">
                            {{ $plan->price_xof == 0 ? 'Activer l\'Essai Gratuit' : 'Choisir ce Plan' }}
                        </button>
                    </form>
                </div>
            @endforeach
        </div>

        @if ($currentUserLicence && $currentUserLicence->isActive())
            <div class="mt-8 text-center">
                <a href="{{ route('manage.companies.index') }}" class="inline-block bg-green-500 text-white py-2 px-6 rounded-md hover:bg-green-600 transition duration-300">
                    Aller à la Gestion des Entreprises
                </a>
            </div>
        @endif
    </div>
</body>
</html>
