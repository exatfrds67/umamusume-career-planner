<x-admin-layout title="Users">
    <div class="space-y-6">
        <div class="admin-page-hero">
            <div class="admin-page-hero__content">
                <div>
                    <div class="admin-page-hero__eyebrow">
                        <span>Access Control</span>
                    </div>
                    <h1 class="admin-page-hero__title">User Management</h1>
                    <p class="admin-page-hero__body text-sm sm:text-base">Review roles, search accounts, and manage administrative privileges.</p>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <form method="GET" role="search" aria-label="Filter users" class="filter-surface p-4 flex gap-4 flex-wrap md:flex-nowrap">
            <label for="user-search" class="sr-only">Search users</label>
            <input type="text" name="search" id="user-search" value="{{ request('search') }}" placeholder="Search users..."
                class="flex-1 rounded-md border-neutral-300 shadow-xs focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white">
            <label for="user-role-filter" class="sr-only">Role filter</label>
            <select name="is_admin" id="user-role-filter"
                class="rounded-md border-neutral-300 shadow-xs focus:border-blue-500 focus:ring-blue-500 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white">
                <option value="">All Users</option>
                <option value="1" {{ request('is_admin') == '1' ? 'selected' : '' }}>Admins Only</option>
                <option value="0" {{ request('is_admin') == '0' ? 'selected' : '' }}>Regular Users</option>
            </select>
            <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2">
                Filter
            </button>
        </form>

        <!-- Users Table -->
        <div class="admin-surface overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <caption class="sr-only">User list</caption>
                <thead class="bg-neutral-50 dark:bg-neutral-900">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                            User</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                            Email</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                            Characters</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                            Role</th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                            Joined</th>
                        <th scope="col"
                            class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 bg-white dark:divide-neutral-700 dark:bg-neutral-800">
                    @forelse ($users as $user)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm font-medium text-neutral-900 dark:text-white">{{ $user->name }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm text-neutral-500 dark:text-neutral-400">{{ $user->email }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm text-neutral-500 dark:text-neutral-400">{{ $user->characters_count }}
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                @if ($user->is_admin)
                                    <span
                                        class="inline-flex rounded-full bg-red-100 px-2 text-xs font-semibold leading-5 text-red-800 dark:bg-red-900/20 dark:text-red-200">Admin</span>
                                @else
                                    <span
                                        class="inline-flex rounded-full bg-neutral-100 px-2 text-xs font-semibold leading-5 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-200">User</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-neutral-500 dark:text-neutral-400">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                        aria-label="Edit {{ $user->name }}"
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400">Edit</a>

                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.toggle-admin', $user) }}"
                                            class="inline"
                                            onsubmit="return confirm('{{ $user->is_admin ? 'Revoke admin privileges from' : 'Grant admin privileges to' }} {{ $user->name }}?')">
                                            @csrf
                                            <button type="submit"
                                                aria-label="{{ $user->is_admin ? 'Revoke admin from ' : 'Make admin: ' }}{{ $user->name }}"
                                                class="text-amber-600 hover:text-amber-900 dark:text-amber-400">
                                                {{ $user->is_admin ? 'Revoke Admin' : 'Make Admin' }}
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                            class="inline"
                                            onsubmit="return confirm('Delete user {{ $user->name }}? This action cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                aria-label="Delete {{ $user->name }}"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-neutral-500 dark:text-neutral-400">
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
