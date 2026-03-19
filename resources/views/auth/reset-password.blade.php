<x-guest-layout>
    <div class="mb-10 text-center lg:text-left">
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight mb-2">Nowe hasło</h1>
        <p class="text-slate-500">
            Utwórz nowe, bezpieczne hasło.
        </p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div class="space-y-1.5">
            <label for="email" class="block text-sm font-semibold text-slate-700 ml-1">{{ __('content.auth.email') }}</label>
            <input id="email" 
                   class="block w-full px-4 py-3.5 bg-slate-50 border border-transparent rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all duration-200 placeholder-slate-400 font-medium" 
                   type="email" 
                   name="email" 
                   value="{{ old('email', $request->email) }}" 
                   required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm font-medium text-rose-600" />
        </div>

        <!-- Password -->
        <div class="space-y-1.5">
            <label for="password" class="block text-sm font-semibold text-slate-700 ml-1">Nowe hasło</label>
            <input id="password" 
                   class="block w-full px-4 py-3.5 bg-slate-50 border border-transparent rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all duration-200 placeholder-slate-400 font-medium"
                   type="password"
                   name="password"
                   required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm font-medium text-rose-600" />
        </div>

        <!-- Confirm Password -->
        <div class="space-y-1.5">
            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 ml-1">Potwierdź hasło</label>
            <input id="password_confirmation" 
                   class="block w-full px-4 py-3.5 bg-slate-50 border border-transparent rounded-xl text-slate-900 text-sm focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all duration-200 placeholder-slate-400 font-medium"
                   type="password"
                   name="password_confirmation" 
                   required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-sm font-medium text-rose-600" />
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl text-sm font-bold text-white bg-slate-900 hover:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                {{ __('content.auth.reset_password') }}
            </button>
        </div>
    </form>
</x-guest-layout>
