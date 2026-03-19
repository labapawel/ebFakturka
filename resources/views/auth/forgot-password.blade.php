<x-guest-layout>
    <div class="mb-10 text-center lg:text-left">
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight mb-2">Zresetuj hasło</h1>
        <p class="text-slate-500 leading-relaxed">
            Podaj swój email, a wyślemy Ci link do resetu.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div class="space-y-1.5">
            <label for="email" class="block text-sm font-semibold text-slate-700 ml-1">{{ __('content.auth.email') }}</label>
            <input id="email" 
                   class="block w-full px-4 py-3.5 bg-slate-50 border border-transparent rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all duration-200 placeholder-slate-400 font-medium" 
                   type="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm font-medium text-rose-600" />
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl text-sm font-bold text-white bg-slate-900 hover:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                {{ __('content.auth.send_reset_link') }}
            </button>
        </div>

        <div class="text-center pt-4">
            <a href="{{ route('login') }}" class="text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors inline-flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Powrót do logowania
            </a>
        </div>
    </form>
</x-guest-layout>
