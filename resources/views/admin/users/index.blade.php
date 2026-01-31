<x-admin-layout>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">User Management</h1>
        </div>

        <!-- Search and Filter -->
        <form method="GET" class="flex gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search users..."
                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
            <select name="is_admin"
                class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                <option value="">All Users</option>
                <option value="1" {{ request('is_admin') == '1' ? 'selected' : '' }}>Admins Only</option>
                <option value="0" {{ request('is_admin') == '0' ? 'selected' : '' }}>Regular Users</option>
            </select>
            <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                Filter
            </button>
        </form>

        <!-- Users Table -->
        <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            User</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Email</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Characters</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Role</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Joined</th>
                        <th
                            class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    @forelse ($users as $user)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->characters_count }}
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                @if ($user->is_admin)
                                    <span
                                        class="inline-flex rounded-full bg-red-100 px-2 text-xs font-semibold leading-5 text-red-800 dark:bg-red-900/20 dark:text-red-200">Admin</span>
                                @else
                                    <span
                                        class="inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold leading-5 text-gray-800 dark:bg-gray-700 dark:text-gray-200">User</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400">Edit</a>

                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.toggle-admin', $user) }}"
                                            class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400">
                                                {{ $user->is_admin ? 'Revoke Admin' : 'Make Admin' }}
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                            class="inline" onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>
</x-admin-layout>
