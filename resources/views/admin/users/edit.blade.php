<x-admin-layout>
    <div class="space-y-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit User: {{ $user->name }}</h1>

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                <div class="space-y-4">
                    <div>
                        <label for="name"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="bio"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bio</label>
                        <textarea name="bio" id="bio" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_admin" id="is_admin" value="1"
                            {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900">
                        <label for="is_admin" class="ms-2 block text-sm text-gray-900 dark:text-gray-300">
                            Administrator
                        </label>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">New
                            Password (leave blank to keep current)</label>
                        <input type="password" name="password" id="password"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                </div>

                <div class="mt-6 flex gap-2">
                    <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                        Update User
                    </button>
                    <a href="{{ route('admin.users.index') }}"
                        class="rounded-md bg-gray-600 px-4 py-2 text-white hover:bg-gray-700">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</x-admin-layout>
