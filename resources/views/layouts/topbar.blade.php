<header class="bg-white shadow-sm z-10 px-6 py-3">
    <div class="flex items-center justify-between gap-4">
        <div class="min-w-0 flex-1">
            @isset($topbar)
                {{ $topbar }}
            @endisset
        </div>

        <div class="flex items-center justify-end space-x-4">
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
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
                    {{ __('Język / Language') }}
                </div>
                @php
                    $langs = array_map('basename', array_filter(glob(resource_path('lang/*')), 'is_dir'));
                    $currentLang = Session::get('applocale', config('app.locale'));
                @endphp
                <div class="flex items-center space-x-2 px-4 py-2">
                @foreach($langs as $lang)
                    <a href="{{ route('lang.switch', $lang) }}" class="uppercase text-sm font-medium transition-colors hover:text-indigo-500 {{ $currentLang === $lang ? 'font-bold text-indigo-600' : 'text-gray-600' }}">
                        {{ $lang }}
                    </a>
                    @if(!$loop->last)
                        <span class="text-gray-300 text-sm">|</span>
                    @endif
                @endforeach
                </div>
                
                <div class="border-t border-gray-100 mt-1 mb-1"></div>

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
    </div>
</header>
