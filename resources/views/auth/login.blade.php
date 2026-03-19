<x-guest-layout>
    
    <div class="mb-10 text-center lg:text-left">
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight mb-2">Witaj ponownie</h1>
        <p class="text-slate-500">Zaloguj się do swojego konta.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div class="space-y-1.5">
            <label for="email" class="block text-sm font-semibold text-slate-700 ml-1">{{ __('content.auth.email') }}</label>
            <input id="email" 
                   class="block w-full px-4 py-3.5 bg-slate-50 border border-transparent rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all duration-200 placeholder-slate-400 font-medium" 
                   type="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   required autofocus autocomplete="username" 
                   placeholder="nazwa@firma.pl" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm font-medium text-rose-600" />
        </div>

        <!-- Password -->
        <div class="space-y-1.5">
            <div class="flex justify-between items-center ml-1">
                <label for="password" class="block text-sm font-semibold text-slate-700">{{ __('content.auth.password') }}</label>
                @if (Route::has('password.request'))
                    <a class="text-sm font-medium text-indigo-600 hover:text-indigo-500 transition-colors" href="{{ route('password.request') }}">
                        Zapomniałeś?
                    </a>
                @endif
            </div>
            <input id="password" 
                   class="block w-full px-4 py-3.5 bg-slate-50 border border-transparent rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all duration-200 placeholder-slate-400 font-medium"
                   type="password"
                   name="password"
                   required autocomplete="current-password"
                   placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm font-medium text-rose-600" />
        </div>

        <div class="flex items-center pt-2 ml-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500 cursor-pointer" name="remember">
                <span class="ms-2 text-sm text-slate-600 group-hover:text-slate-900 transition-colors">Zapamiętaj mnie</span>
            </label>
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl text-sm font-bold text-white bg-slate-900 hover:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                Zaloguj się
            </button>
        </div>
    </form>
</x-guest-layout>
