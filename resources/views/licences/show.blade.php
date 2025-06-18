<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choisissez votre Plan de Licence</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom styles for the Inter font and general body/container */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8faff; /* Light background for the whole page */
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start; /* Align to top for longer content */
            min-height: 100vh; /* Ensure it takes full viewport height */
        }
        .container {
            max-width: 1200px; /* Max width for the content area */
            width: 100%; /* Make it fluid up to max-width */
            padding: 2rem;
            box-sizing: border-box; /* Include padding in element's total width and height */
        }
        /* Specific styling for the plan cards to ensure consistent height and layout */
        .plan-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%; /* Ensure all cards have the same height */
        }
        /* Style for the features list checkmark icon */
        .feature-icon {
            min-width: 1.25rem; /* Ensure icon doesn't shrink */
        }
    </style>
</head>
<body class="bg-gray-100 antialiased">
    <div class="container mx-auto p-4 md:p-8">
        <h1 class="text-4xl font-extrabold text-gray-900 mb-8 text-center leading-tight">
            Choisissez le Plan Qui Vous Convient
        </h1>

        {{-- Session Messages --}}
        @if (session('message'))
            <div class="bg-yellow-50 border border-yellow-300 text-yellow-800 p-4 mb-6 rounded-lg shadow-sm" role="alert">
                <p class="font-medium">{{ session('message') }}</p>
            </div>
        @endif
        @if (session('success'))
            <div class="bg-green-50 border border-green-300 text-green-800 p-4 mb-6 rounded-lg shadow-sm" role="alert">
                <p class="font-medium">{{ session('success') }}</p>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-50 border border-red-300 text-red-800 p-4 mb-6 rounded-lg shadow-sm" role="alert">
                <p class="font-medium">{{ session('error') }}</p>
            </div>
        @endif
        @if (session('info'))
            <div class="bg-blue-50 border border-blue-300 text-blue-800 p-4 mb-6 rounded-lg shadow-sm" role="alert">
                <p class="font-medium">{{ session('info') }}</p>
            </div>
        @endif

        {{-- Current Licence Information (now user-based) --}}
        @if (isset($currentUserLicence) && $currentUserLicence && $currentUserLicence->licencePlan)
            <div class="bg-blue-50 border border-blue-300 text-blue-800 p-6 mb-8 rounded-lg shadow-md">
                <h2 class="font-bold text-2xl mb-3">Votre Licence Actuelle</h2>
                <p class="text-lg mb-1">
                    <span class="font-semibold">Plan:</span> {{ $currentUserLicence->licencePlan->name }}
                </p>
                <p class="text-lg mb-1">
                    <span class="font-semibold">Statut:</span>
                    <span class="font-bold
                        @if ($currentUserLicence->status === 'ACTIVE' || $currentUserLicence->status === 'TRIAL')
                            text-green-600
                        @elseif ($currentUserLicence->status === 'EXPIRED' || $currentUserLicence->status === 'CANCELLED')
                            text-red-600
                        @else
                            text-yellow-600
                        @endif
                    ">{{ $currentUserLicence->status }}</span>
                </p>
                <p class="text-lg mb-1">
                    <span class="font-semibold">Valide jusqu'au:</span>
                    @if ($currentUserLicence->end_date)
                        {{ \Carbon\Carbon::parse($currentUserLicence->end_date)->format('d/m/Y') }}
                    @else
                        N/A
                    @endif
                </p>
                <p class="text-lg mb-1">
                    <span class="font-semibold">Entreprises autorisées:</span>
                    {{ $currentUserLicence->licencePlan->max_companies === -1 ? 'Illimité' : $currentUserLicence->licencePlan->max_companies }}
                </p>
                <p class="text-lg mb-1">
                    <span class="font-semibold">Entreprises actuelles:</span> {{ Auth::user()->companies->count() }}
                </p>
                @if (!$currentUserLicence->isActive)
                    <p class="text-red-700 mt-4 font-medium text-lg">
                        Votre licence est expirée ou inactive. Veuillez renouveler pour continuer à utiliser l'application.
                    </p>
                @elseif (Auth::user()->companies->count() >= ($currentUserLicence->licencePlan->max_companies ?? PHP_INT_MAX))
                    <p class="text-yellow-700 mt-4 font-medium text-lg">
                        Vous avez atteint le nombre maximal d'entreprises autorisé par votre plan. Veuillez mettre à niveau votre licence pour ajouter plus d'entreprises.
                    </p>
                @endif
            </div>
        @endif

        {{-- Licence Plans Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse ($licencePlans as $plan)
                <div class="plan-card bg-white rounded-xl shadow-xl p-8 flex flex-col transform transition-transform duration-300 hover:scale-[1.02] hover:shadow-2xl">
                    <div>
                        <h2 class="text-3xl font-extrabold text-gray-900 mb-3">{{ $plan->name }}</h2>
                        <p class="text-5xl font-black text-indigo-700 mb-4">
                            @if ($plan->price_xof === 0)
                                Gratuit
                            @else
                                {{ number_format($plan->price_xof, 0, ',', ' ') }} XOF
                            @endif
                        </p>
                        <p class="text-gray-600 mb-6 text-xl">
                            @if ($plan->duration_type === 'MONTHLY')
                                Par mois
                            @elseif ($plan->duration_type === 'ANNUAL')
                                Par an
                            @elseif ($plan->duration_type === 'TRIAL')
                                Pour {{ $plan->duration_days }} jours
                            @else
                                Période indéfinie
                            @endif
                        </p>

                        <ul class="text-gray-800 mb-8 space-y-3 text-lg">
                            {{-- Display max companies for each plan --}}
                            <li class="flex items-start">
                                <svg class="w-6 h-6 text-blue-500 mr-3 flex-shrink-0 feature-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v2m7-7a4 4 0 100-8 4 4 0 000 8z"></path>
                                </svg>
                                <span>{{ $plan->max_companies === -1 ? 'Nombre illimité d\'entreprises' : 'Jusqu\'à ' . $plan->max_companies . ' entreprise(s)' }}</span>
                            </li>
                            @foreach ($plan->features as $feature)
                                <li class="flex items-start">
                                    <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0 feature-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    {{-- Assurez-vous que $feature est une chaîne, ou implode si c'est un tableau imbriqué --}}
                                    <span>{{ is_array($feature) ? implode(', ', $feature) : (string) $feature }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <form action="{{ route('licences.purchase') }}" method="POST" class="mt-auto pt-6 border-t border-gray-100">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                        <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-700 text-white py-4 px-6 rounded-lg text-xl font-semibold shadow-lg hover:from-indigo-700 hover:to-purple-800 focus:outline-none focus:ring-4 focus:ring-indigo-300 focus:ring-offset-2 transition duration-200 ease-in-out transform hover:-translate-y-0.5">
                            Choisir ce plan
                        </button>
                    </form>
                </div>
            @empty
                <p class="col-span-full text-center text-gray-600 text-xl">Aucun plan de licence disponible pour le moment. Veuillez revenir plus tard.</p>
            @endforelse
        </div>
    </div>
</body>
</html>
