<div class="flex flex-col w-64 bg-slate-900 h-full text-white transition-all duration-300 ease-in-out sidebar">
    <!-- Logo -->
    <div class="flex items-center justify-center h-16 bg-slate-950 shadow-md">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
            <x-application-logo class="block h-8 w-auto fill-current text-indigo-500" />
            <span class="text-xl font-bold tracking-wide">ebFakturka</span>
        </a>
    </div>

    <!-- Navigation Links -->
    <div class="flex-1 overflow-y-auto py-4">
        <nav class="px-2 space-y-1">
            
            <p class="px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 mt-2">{{ __('content.nav.general') }}</p>
            
            <a href="{{ route('dashboard') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="mr-3 h-5 w-5 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                {{ __('content.nav.dashboard') }}
            </a>

            <div x-cloak x-data="{ open: localStorage.getItem('sidebar_sales') ? localStorage.getItem('sidebar_sales') === 'true' : {{ request()->routeIs('invoices.*', 'recurring_invoices.*', 'contractors.*', 'products.*') ? 'true' : 'false' }} }" x-init="$watch('open', value => localStorage.setItem('sidebar_sales', value))">
                <button @click="open = !open" type="button" class="w-full flex justify-between items-center px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 mt-6 focus:outline-none hover:text-slate-300 transition-colors">
                    <span>{{ __('content.nav.sales') }}</span>
                    <svg :class="{'rotate-180': open, 'rotate-0': !open}" class="w-4 h-4 transition-transform text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-1 mt-2">
                    <a href="{{ route('invoices.index') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('invoices.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('invoices.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        {{ __('content.nav.invoices') }}
                    </a>

                    <a href="{{ route('recurring_invoices.index') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('recurring_invoices.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('recurring_invoices.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        {{ __('content.nav.recurring_invoices') }}
                    </a>

                    <a href="{{ route('contractors.index') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('contractors.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('contractors.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        {{ __('content.nav.contractors') }}
                    </a>

                    <a href="{{ route('products.index') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('products.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('products.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        {{ __('content.nav.products') }}
                    </a>
                </div>
            </div>

            <div x-cloak x-data="{ open: localStorage.getItem('sidebar_purchases') ? localStorage.getItem('sidebar_purchases') === 'true' : {{ request()->routeIs('purchase_invoices.*') ? 'true' : 'false' }} }" x-init="$watch('open', value => localStorage.setItem('sidebar_purchases', value))">
                <button @click="open = !open" type="button" class="w-full flex justify-between items-center px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 mt-6 focus:outline-none hover:text-slate-300 transition-colors">
                    <span>{{ __('content.nav.purchases') }}</span>
                    <svg :class="{'rotate-180': open, 'rotate-0': !open}" class="w-4 h-4 transition-transform text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-1 mt-2">
                    <a href="{{ route('purchase_invoices.index') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('purchase_invoices.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                         <svg class="mr-3 h-5 w-5 {{ request()->routeIs('purchase_invoices.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        {{ __('content.nav.purchase_invoices') }}
                    </a>
                </div>
            </div>

            <div x-cloak x-data="{ open: localStorage.getItem('sidebar_config') ? localStorage.getItem('sidebar_config') === 'true' : {{ request()->routeIs('vat_rates.*', 'currencies.*', 'users.*', 'settings.*') ? 'true' : 'false' }} }" x-init="$watch('open', value => localStorage.setItem('sidebar_config', value))">
                <button @click="open = !open" type="button" class="w-full flex justify-between items-center px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 mt-6 focus:outline-none hover:text-slate-300 transition-colors">
                    <span>{{ __('content.nav.configuration') }}</span>
                    <svg :class="{'rotate-180': open, 'rotate-0': !open}" class="w-4 h-4 transition-transform text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-1 mt-2">
                    <a href="{{ route('vat_rates.index') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('vat_rates.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('vat_rates.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        {{ __('content.nav.vat_rates') }}
                    </a>

                     <a href="{{ route('currencies.index') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('currencies.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('currencies.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ __('content.nav.currencies') }}
                    </a>

                    @can('manage-users')
                    <a href="{{ route('users.index') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('users.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('users.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        {{ __('content.nav.users') }}
                    </a>
                    @endcan

                    @if(Auth::check() && Auth::user()->isAdmin())
                    <a href="{{ route('settings.index') }}" class="group flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('settings.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('settings.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ __('content.nav.settings') }}
                    </a>
                    @endif
                </div>
            </div>
        </nav>
    </div>

    <!-- User Profile Summary (Bottom) -->
    <div class="px-4 py-4 bg-slate-950 border-t border-slate-800">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-white">{{ Auth::user()->name }}</p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs text-slate-400 hover:text-white transition-colors">{{ __('content.nav.logout') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
