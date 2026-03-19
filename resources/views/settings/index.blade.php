<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('content.settings.title') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ tab: 'main' }">
        <div class="w-full mx-auto">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    <div class="mb-4 border-b border-gray-200">
                        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" role="tablist">
                            <li class="mr-2" role="presentation">
                                <button @click.prevent="tab = 'main'" :class="{'inline-block p-4 border-b-2 rounded-t-lg transition-colors outline-none': true, 'text-blue-600 border-blue-600 font-bold': tab === 'main', 'hover:text-gray-600 hover:border-gray-300 border-transparent': tab !== 'main'}" type="button" role="tab" >{{ __('content.settings.tab_main') }}</button>
                            </li>
                            <li class="mr-2" role="presentation">
                                <button @click.prevent="tab = 'advanced'" :class="{'inline-block p-4 border-b-2 rounded-t-lg transition-colors outline-none': true, 'text-blue-600 border-blue-600 font-bold': tab === 'advanced', 'hover:text-gray-600 hover:border-gray-300 border-transparent': tab !== 'advanced'}" type="button" role="tab" >{{ __('content.settings.tab_advanced') }}</button>
                            </li>
                            <li class="mr-2" role="presentation">
                                <button @click.prevent="tab = 'ksef'" :class="{'inline-block p-4 border-b-2 rounded-t-lg transition-colors outline-none': true, 'text-blue-600 border-blue-600 font-bold': tab === 'ksef', 'hover:text-gray-600 hover:border-gray-300 border-transparent': tab !== 'ksef'}" type="button" role="tab" >{{ __('content.settings.tab_ksef') }}</button>
                            </li>
                        </ul>
                    </div>

                    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div x-show="tab === 'main'" class="space-y-6" x-transition>
                            <h3 class="text-lg font-medium leading-6 text-gray-900">{{ __('content.settings.basic_options') }}</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ __('content.settings.basic_options_desc') }}
                            </p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                @foreach($mainEnvs as $env)
                                    <div>
                                        <x-input-label for="env_main_{{ $env['key'] }}" :value="\Lang::has('settings.'.$env['key']) ? __('settings.'.$env['key']) : $env['key']" />
                                        @if($env['key'] === 'VAT_EXEMPT')
                                            <select id="env_main_{{ $env['key'] }}" name="env[{{ $env['key'] }}]" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                @foreach($vatOptions as $value => $label)
                                                    <option value="{{ $value }}" @selected($env['value'] === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($env['key'] === 'VAT_EXEMPT_REASON_TYPE')
                                            <select id="env_main_{{ $env['key'] }}" name="env[{{ $env['key'] }}]" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                @foreach($vatReasonTypeOptions as $value => $label)
                                                    <option value="{{ $value }}" @selected($env['value'] === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($env['isHidden'])
                                            <x-text-input id="env_main_{{ $env['key'] }}" name="env[{{ $env['key'] }}]" type="password" class="mt-1 block w-full bg-gray-50 placeholder-gray-400" autocomplete="off" :placeholder="__('content.settings.new_value_placeholder')" />
                                        @else
                                            <x-text-input id="env_main_{{ $env['key'] }}" name="env[{{ $env['key'] }}]" type="text" class="mt-1 block w-full" :value="$env['value']" />
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div x-show="tab === 'advanced'" class="space-y-6" style="display: none;" x-transition>
                            <h3 class="text-lg font-medium leading-6 text-gray-900">{{ __('content.settings.advanced_env') }}</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ __('content.settings.advanced_env_desc') }}
                            </p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                @foreach($advancedEnvs as $env)
                                    <div>
                                        <x-input-label for="env_adv_{{ $env['key'] }}" :value="\Lang::has('settings.'.$env['key']) ? __('settings.'.$env['key']) : $env['key']" />
                                        @if($env['isHidden'])
                                            <x-text-input id="env_adv_{{ $env['key'] }}" name="env[{{ $env['key'] }}]" type="password" class="mt-1 block w-full bg-gray-50 placeholder-gray-400" autocomplete="off" :placeholder="__('content.settings.new_value_placeholder')" />
                                        @else
                                            <x-text-input id="env_adv_{{ $env['key'] }}" name="env[{{ $env['key'] }}]" type="text" class="mt-1 block w-full" :value="$env['value']" />
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div x-show="tab === 'ksef'" class="space-y-6" style="display: none;" x-transition>
                            <h3 class="text-lg font-medium leading-6 text-gray-900">{{ __('content.settings.ksef_certs') }}</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ __('content.settings.ksef_certs_desc') }}
                            </p>
                            
                            @if(Storage::disk('local')->exists('ksef/cert.crt') && Storage::disk('local')->exists('ksef/private.key'))
                                <div class="mb-4 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded relative text-sm" role="alert">
                                    <span class="block sm:inline">{!! __('content.settings.ksef_certs_info') !!}</span>
                                </div>
                            @else
                                <div class="mb-4 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded relative text-sm" role="alert">
                                    <span class="block sm:inline">{!! __('content.settings.ksef_certs_missing') !!}</span>
                                </div>
                            @endif

                            <h4 class="text-md font-medium text-gray-800 mt-6 mb-2">{{ __('content.settings.upload_files') }}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                <div>
                                    <x-input-label for="ksef_cert" :value="__('content.settings.cert_crt')" />
                                    <input id="ksef_cert" name="ksef_cert" type="file" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" accept=".crt,.pem,.txt">
                                    @error('ksef_cert')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <x-input-label for="ksef_key" :value="__('content.settings.private_key')" />
                                    <input id="ksef_key" name="ksef_key" type="file" class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" accept=".key,.pem,.txt">
                                    @error('ksef_key')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            
                            <h4 class="text-md font-medium text-gray-800 mt-8 mb-2">{{ __('content.settings.ksef_env') }}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                                @foreach($ksefEnvs as $env)
                                    <div>
                                        <x-input-label for="env_ksef_{{ $env['key'] }}" :value="\Lang::has('settings.'.$env['key']) ? __('settings.'.$env['key']) : $env['key']" />
                                        @if($env['isHidden'])
                                            <x-text-input id="env_ksef_{{ $env['key'] }}" name="env[{{ $env['key'] }}]" type="password" class="mt-1 block w-full bg-gray-50 placeholder-gray-400" autocomplete="off" :placeholder="__('content.settings.new_value_placeholder')" />
                                        @else
                                            <x-text-input id="env_ksef_{{ $env['key'] }}" name="env[{{ $env['key'] }}]" type="text" class="mt-1 block w-full" :value="$env['value']" />
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 border-t border-gray-200 pt-5">
                            <x-primary-button class="ml-3">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                {{ __('content.settings.save_settings') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
