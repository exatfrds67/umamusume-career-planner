<x-admin-layout title="Edit User: {{ $user->name }}">
    <div class="space-y-6">
        <h1 class="text-3xl font-bold text-neutral-900 dark:text-white">Edit User: {{ $user->name }}</h1>

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="rounded-lg bg-white p-6 shadow dark:bg-neutral-800">
                <div class="space-y-4">
                    <div>
                        <label for="name"
                            class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                            required autocomplete="name"
                            @error('name') aria-describedby="name-error" aria-invalid="true" @enderror
                            class="mt-1 block w-full rounded-md border-neutral-300 shadow-xs focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
                        @error('name')
                            <p id="name-error" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email"
                            class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                            required autocomplete="email"
                            @error('email') aria-describedby="email-error" aria-invalid="true" @enderror
                            class="mt-1 block w-full rounded-md border-neutral-300 shadow-xs focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
                        @error('email')
                            <p id="email-error" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="bio"
                            class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Bio</label>
                        <textarea name="bio" id="bio" rows="3"
                            @error('bio') aria-describedby="bio-error" aria-invalid="true" @enderror
                            class="mt-1 block w-full rounded-md border-neutral-300 shadow-xs focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')
                            <p id="bio-error" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_admin" id="is_admin" value="1"
                            {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-neutral-300 text-blue-600 focus:ring-blue-500 dark:border-neutral-700 dark:bg-neutral-900">
                        <label for="is_admin" class="ms-2 block text-sm text-neutral-900 dark:text-neutral-300">
                            Administrator
                        </label>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">New
                            Password</label>
                        <p id="password-hint" class="mt-0.5 text-xs text-neutral-500 dark:text-neutral-400">Leave blank to keep current password.</p>
                        <input type="password" name="password" id="password"
                            autocomplete="new-password"
                            aria-describedby="password-hint"
                            @error('password') aria-describedby="password-error" aria-invalid="true" @enderror
                            class="mt-1 block w-full rounded-md border-neutral-300 shadow-xs focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
                        @error('password')
                            <p id="password-error" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation"
                            class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            autocomplete="new-password"
                            class="mt-1 block w-full rounded-md border-neutral-300 shadow-xs focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
                    </div>
                </div>

                <div class="mt-6 flex gap-2">
                    <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                        Update User
                    </button>
                    <a href="{{ route('admin.users.index') }}"
                        class="rounded-md border border-neutral-300 bg-white px-4 py-2 text-neutral-700 hover:bg-neutral-50 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-600">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</x-admin-layout>
