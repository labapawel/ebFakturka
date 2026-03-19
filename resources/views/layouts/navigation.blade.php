<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('content.nav.dashboard') }}
                    </x-nav-link>
                    <!-- Sales Dropdown -->
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none transition duration-150 ease-in-out {{ request()->routeIs('invoices.*', 'recurring_invoices.*', 'products.*', 'contractors.*') ? 'border-indigo-400 text-gray-900' : '' }}">
                                    <div>{{ __('content.nav.sales') }}</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('invoices.index')">
                                    {{ __('content.nav.invoices') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('recurring_invoices.index')">
                                    {{ __('content.nav.recurring_invoices') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('contractors.index')">
                                    {{ __('content.nav.contractors') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('products.index')">
                                    {{ __('content.nav.products') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <!-- Purchases Dropdown -->
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none transition duration-150 ease-in-out {{ request()->routeIs('purchase_invoices.*') ? 'border-indigo-400 text-gray-900' : '' }}">
                                    <div>{{ __('content.nav.purchases') }}</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('purchase_invoices.index')">
                                    {{ __('content.nav.purchase_invoices') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <!-- Configuration Dropdown -->
                    @if(Auth::user() && (Auth::user()->isAdmin() || Auth::user()->can('manage-users')))
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none transition duration-150 ease-in-out {{ request()->routeIs('settings.*', 'vat_rates.*', 'currencies.*', 'users.*') ? 'border-indigo-400 text-gray-900' : '' }}">
                                    <div>{{ __('content.nav.configuration') }}</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                @if(Auth::user()->isAdmin())
                                <x-dropdown-link :href="route('settings.index')">
                                    {{ __('content.nav.settings') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('vat_rates.index')">
                                    {{ __('content.nav.vat_rates') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('currencies.index')">
                                    {{ __('content.nav.currencies') }}
                                </x-dropdown-link>
                                @endif
                                @can('manage-users')
                                <x-dropdown-link :href="route('users.index')">
                                    {{ __('content.nav.users') }}
                                </x-dropdown-link>
                                @endcan
                            </x-slot>
                        </x-dropdown>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Language Switcher -->
                        <div class="block px-4 py-2 text-xs text-gray-400 uppercase border-b border-gray-100">
                            Język / Language
                        </div>
                        @php
                            $langs = array_map('basename', array_filter(glob(resource_path('lang/*')), 'is_dir'));
                            $currentLang = Session::get('applocale', config('app.locale'));
                        @endphp
                        @foreach($langs as $lang)
                            <x-dropdown-link :href="route('lang.switch', $lang)" class="uppercase {{ $currentLang === $lang ? 'font-bold text-indigo-600' : '' }}">
                                {{ $lang }}
                            </x-dropdown-link>
                        @endforeach
                        
                        <div class="border-t border-gray-100"></div>

                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('content.nav.profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('content.nav.logout') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('content.nav.dashboard') }}
            </x-responsive-nav-link>
            <!-- Sprzedaż Mobile -->
            <div class="border-t border-gray-200 mt-2 pt-2">
                <div class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">{{ __('content.nav.sales') }}</div>
                <x-responsive-nav-link :href="route('invoices.index')" :active="request()->routeIs('invoices.*')">
                    {{ __('content.nav.invoices') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('recurring_invoices.index')" :active="request()->routeIs('recurring_invoices.*')">
                    {{ __('content.nav.recurring_invoices') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('contractors.index')" :active="request()->routeIs('contractors.*')">
                    {{ __('content.nav.contractors') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                    {{ __('content.nav.products') }}
                </x-responsive-nav-link>
            </div>

            <!-- Zakupy Mobile -->
            <div class="border-t border-gray-200 mt-2 pt-2">
                <div class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">{{ __('content.nav.purchases') }}</div>
                <x-responsive-nav-link :href="route('purchase_invoices.index')" :active="request()->routeIs('purchase_invoices.*')">
                    {{ __('content.nav.purchase_invoices') }}
                </x-responsive-nav-link>
            </div>

            <!-- Konfiguracja Mobile -->
            @if(Auth::user() && (Auth::user()->isAdmin() || Auth::user()->can('manage-users')))
            <div class="border-t border-gray-200 mt-2 pt-2">
                <div class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">{{ __('content.nav.configuration') }}</div>
                @if(Auth::user()->isAdmin())
                <x-responsive-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.*')">
                    {{ __('content.nav.settings') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('vat_rates.index')" :active="request()->routeIs('vat_rates.*')">
                    {{ __('content.nav.vat_rates') }}
                </x-responsive-nav-link>
                 <x-responsive-nav-link :href="route('currencies.index')" :active="request()->routeIs('currencies.*')">
                    {{ __('content.nav.currencies') }}
                </x-responsive-nav-link>
                @endif
                @can('manage-users')
                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                    {{ __('content.nav.users') }}
                </x-responsive-nav-link>
                @endcan
            </div>
            @endif

            <!-- Language Switcher (Mobile) -->
            <div class="border-t border-gray-200 mt-2 pt-2">
                <div class="px-4 text-xs text-gray-400 uppercase">Język / Language</div>
                <div class="mt-2 space-y-1">
                    @php
                        $langs = array_map('basename', array_filter(glob(resource_path('lang/*')), 'is_dir'));
                        $currentLang = Session::get('applocale', config('app.locale'));
                    @endphp
                    @foreach($langs as $lang)
                        <x-responsive-nav-link :href="route('lang.switch', $lang)" :class="$currentLang === $lang ? 'font-bold text-indigo-600 uppercase' : 'uppercase'">
                            {{ $lang }}
                        </x-responsive-nav-link>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('content.nav.profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('content.nav.logout') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
