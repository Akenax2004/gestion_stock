<header class="navbar-expand-md">
    <div class="collapse navbar-collapse" id="navbar-menu">
        <div class="navbar">
            <div class="container-xl">
                <ul class="navbar-nav">
                    <li class="nav-item {{ request()->is('dashboard*') ? 'active' : null }}">
                        <a class="nav-link" href="{{ route('dashboard') }}" >
                            <span class="nav-link-icon d-md-none d-lg-inline-block"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" /></svg>
                            </span>
                            <span class="nav-link-title">
                                {{ __('Tableau de bord') }}
                            </span>
                        </a>
                    </li>

                    <li class="nav-item {{ request()->is('products*') ? 'active' : null }}">
                        <a class="nav-link" href="{{ route('products.index') }}" >
                            <span class="nav-link-icon d-md-none d-lg-inline-block"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-packages" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 16.5l-5 -3l5 -3l5 3v5.5l-5 3z" /><path d="M2 13.5v5.5l5 3" /><path d="M7 16.545l5 -3.03" /><path d="M17 16.5l-5 -3l5 -3l5 3v5.5l-5 3z" /><path d="M12 19l5 3" /><path d="M17 16.5l5 -3" /><path d="M12 13.5v-5.5l-5 -3l5 -3l5 3v5.5" /><path d="M7 5.03v5.455" /><path d="M12 8l5 -3" /></svg>
                            </span>
                            <span class="nav-link-title">
                                {{ __('Produits') }}
                            </span>
                        </a>
                    </li>

                    <li class="nav-item dropdown {{ request()->is('orders*') ? 'active' : null }}">
                        <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-package-export" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 21l-8 -4.5v-9l8 -4.5l8 4.5v4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12v9" /><path d="M12 12l-8 -4.5" /><path d="M15 18h7" /><path d="M19 15l3 3l-3 3" /></svg>
                            </span>
                            <span class="nav-link-title">
                                {{ __('Commandes') }}
                            </span>
                        </a>
                        <div class="dropdown-menu">
                            <div class="dropdown-menu-columns">
                                <div class="dropdown-menu-column">
                                    <a class="dropdown-item" href="{{ route('orders.index') }}">
                                        {{ __('Toutes') }}
                                    </a>
                                    <a class="dropdown-item" href="{{ route('orders.complete') }}">
                                        {{ __('Terminées') }}
                                    </a>
                                    <a class="dropdown-item" href="{{ route('orders.pending') }}">
                                        {{ __('En attente') }}
                                    </a>
                                    <a class="dropdown-item" href="{{ route('due.index') }}">
                                        {{ __('Dues') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="nav-item dropdown {{ request()->is('purchases*') ? 'active' : null }}">
                        <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-package-import" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 21l-8 -4.5v-9l8 -4.5l8 4.5v4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12v9" /><path d="M12 12l-8 -4.5" /><path d="M22 18h-7" /><path d="M18 15l-3 3l3 3" /></svg>
                            </span>
                            <span class="nav-link-title">
                                {{ __('Achats') }}
                            </span>
                        </a>
                        <div class="dropdown-menu">
                            <div class="dropdown-menu-columns">
                                <div class="dropdown-menu-column">
                                    <a class="dropdown-item" href="{{ route('purchases.index') }}">
                                        {{ __('Tous') }}
                                    </a>
                                    <a class="dropdown-item" href="{{ route('purchases.approvedPurchases') }}">
                                        {{ __('Approbation') }}
                                    </a>
                                    <a class="dropdown-item" href="{{ route('purchases.dailyPurchaseReport') }}">
                                        {{ __('Rapport d\'achat quotidien') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="nav-item {{ request()->is('quotations') ? 'active' : null }}">
                        <a class="nav-link" href="{{ route('quotations.index') }}" >
                            <span class="nav-link-icon d-md-none d-lg-inline-block"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /></svg>
                            </span>
                            <span class="nav-link-title">
                                {{ __('Devis') }}
                            </span>
                        </a>
                    </li>

                    <li class="nav-item dropdown {{ request()->is('suppliers*', 'customers*') ? 'active' : null }}">
                        <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                 <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-layers-subtract" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 4m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z" /><path d="M16 16v2a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2v-8a2 2 0 0 1 2 -2h2" /></svg>
                            </span>
                            <span class="nav-link-title">
                                {{ __('Pages') }}
                            </span>
                        </a>
                        <div class="dropdown-menu">
                            <div class="dropdown-menu-columns">
                                <div class="dropdown-menu-column">
                                    <a class="dropdown-item" href="{{ route('suppliers.index') }}">
                                        {{ __('Fournisseurs') }}
                                    </a>
                                    <a class="dropdown-item" href="{{ route('customers.index') }}">
                                        {{ __('Clients') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="nav-item dropdown {{ request()->is('users*', 'categories*', 'units*') ? 'active' : null }}">
                        <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="icon icon-tabler icon-tabler-settings"
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    stroke-width="2"
                                    stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" /><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /></svg>
                            </span>
                                <span class="nav-link-title">
                                {{ __('Paramètres') }}
                            </span>
                        </a>
                        <div class="dropdown-menu">
                            <div class="dropdown-menu-columns">
                                <div class="dropdown-menu-column">
                                    {{-- NOUVEAU : Condition pour afficher le lien "Users" uniquement à l'admin --}}
                                    @if(Auth::check() && Auth::user()->email === 'admin@admin.com')
                                        <a class="dropdown-item" href="{{ route('users.index') }}">
                                            {{ __('Utilisateurs') }}
                                        </a>
                                    @endif
                                    <a class="dropdown-item" href="{{ route('categories.index') }}">
                                        {{ __('Catégories') }}
                                    </a>
                                    <a class="dropdown-item" href="{{ route('units.index') }}">
                                        {{ __('Unités') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </li>

                    {{-- NOUVEAU : Menu déroulant pour la sélection de l'entreprise --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-building-factory" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21h18" /><path d="M5 21v-12l5 4v-4l5 4v-4l5 4v-4" /><path d="M19 21v-14l-3 -2l-6 2l-3 -2l-4 2" /></svg>
                            </span>
                            <span class="nav-link-title">
                                @if(Session::has('active_company_name'))
                                    {{ Session::get('active_company_name') }}
                                @else
                                    {{ __('Sélectionner une entreprise') }}
                                @endif
                            </span>
                        </a>
                        <div class="dropdown-menu">
                            <div class="dropdown-menu-columns">
                                <div class="dropdown-menu-column">
                                    {{-- Afficher les entreprises de l'utilisateur --}}
                                    @auth
                                        @php
                                            // Récupérer les entreprises de l'utilisateur authentifié
                                            // Assurez-vous que votre modèle User a une relation hasMany('App\Models\Company')
                                            $userCompanies = Auth::user()->companies ?? collect();
                                        @endphp
                                        @forelse($userCompanies as $company)
                                            <a class="dropdown-item @if(Session::get('active_company_id') == $company->id) active @endif"
                                               href="{{ route('companies.select', $company->id) }}">
                                                {{ $company->name }}
                                            </a>
                                        @empty
                                            <span class="dropdown-item text-muted">{{ __('Aucune entreprise trouvée.') }}</span>
                                        @endforelse
                                        <div class="dropdown-divider"></div>
                                    @endauth
                                    <a class="dropdown-item" href="{{ route('companies.create') }}">
                                        {{ __('Créer une nouvelle entreprise') }}
                                    </a>
                                    <a class="dropdown-item" href="{{ route('companies.index') }}">
                                        {{ __('Gérer les entreprises') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>