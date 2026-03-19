<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('content.users.edit') }}: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <!-- Name -->
                        <div>
                            <x-input-label for="name" :value="__('content.users.name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Email -->
                        <div class="mt-4">
                            <x-input-label for="email" :value="__('content.users.email')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Role -->
                        <div class="mt-4">
                            <x-input-label for="role" :value="__('content.users.role')" />
                            <select id="role" name="role" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>{{ __('content.users.roles.user') }}</option>
                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>{{ __('content.users.roles.admin') }}</option>
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        <!-- Password -->
                        <div class="mt-4">
                            <x-input-label for="password" :value="__('content.users.new_password')" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            <p class="text-sm text-gray-500 mt-1">{{ __('content.users.leave_empty') }}</p>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mt-4">
                            <x-input-label for="password_confirmation" :value="__('content.users.confirm_password')" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <!-- Permissions -->
                        <div class="mt-6 border-t pt-4">
                            <h3 class="font-medium text-gray-700 mb-2">{{ __('content.users.permissions') }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @php
                                    $availablePermissions = [
                                        'manage-users' => __('content.users.permissions_list.manage-users'),
                                        'issue-invoices' => __('content.users.permissions_list.issue-invoices'),
                                        'view-invoices' => __('content.users.permissions_list.view-invoices'),
                                        'send-ksef' => __('content.users.permissions_list.send-ksef'),
                                        'view-ksef' => __('content.users.permissions_list.view-ksef'),
                                    ];
                                    $userPermissions = $user->permissions ?? [];
                                @endphp

                                @foreach ($availablePermissions as $key => $label)
                                    <div class="flex items-center">
                                        <input id="perm_{{ $key }}" type="checkbox" name="permissions[]" value="{{ $key }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                        {{ in_array($key, $userPermissions) ? 'checked' : '' }}>
                                        <label for="perm_{{ $key }}" class="ml-2 text-sm text-gray-600">{{ $label }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('users.index') }}" class="text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-4">
                                {{ __('content.common.cancel') }}
                            </a>
                            <x-primary-button class="ml-4">
                                {{ __('content.common.save_changes') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
