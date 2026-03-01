<div class="bg-white dark:bg-gray-800 shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Profile Information</h2>

        <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Avatar -->
            <div x-data="avatarUploader()" class="space-y-4">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <img x-ref="avatarPreview"
                            class="h-20 w-20 rounded-full bg-gray-50 ring-2 ring-gray-200 dark:ring-gray-700 object-cover"
                            :src="previewUrl ||
                                '{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=3b82f6&color=fff&size=128' }}'"
                            loading="lazy" decoding="async"
                            alt="{{ $user->name }}">
                        <div x-show="uploading"
                            class="absolute inset-0 flex items-center justify-center bg-black/50 rounded-full">
                            <svg class="animate-spin h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <input type="file" x-ref="fileInput" @change="handleFileSelect"
                            accept="image/jpeg,image/png,image/jpg,image/gif" class="hidden">
                        <div class="flex gap-2">
                            <button type="button" @click="$refs.fileInput.click()" :disabled="uploading"
                                class="btn btn-sm btn-secondary"
                                :class="{ 'opacity-50 cursor-not-allowed': uploading }">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span x-text="uploading ? 'Uploading...' : 'Change Avatar'"></span>
                            </button>
                            <button type="button" @click="removeAvatar"
                                x-show="previewUrl || {{ !empty($user->attributes['avatar_path'] ?? null) ? 'true' : 'false' }}"
                                :disabled="uploading"
                                class="btn btn-sm btn-secondary text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">JPG, PNG or GIF. Max 2MB.</p>
                        <p x-show="error" x-text="error" class="mt-1 text-xs text-red-600 dark:text-red-400"></p>
                        <p x-show="success" x-text="success" class="mt-1 text-xs text-green-600 dark:text-green-400">
                        </p>
                    </div>
                </div>
            </div>

            <!-- Display Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Display Name
                </label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                @error('name')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Email Address
                </label>
                <div class="mt-1 flex rounded-md shadow-xs">
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                        required
                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                    @if ($user->email_verified_at)
                        <span
                            class="ml-3 inline-flex items-center px-3 py-2 text-sm font-medium text-green-700 dark:text-green-400">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                    clip-rule="evenodd" />
                            </svg>
                            Verified
                        </span>
                    @endif
                </div>
                @error('email')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Bio (optional) -->
            <div>
                <label for="bio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Bio
                </label>
                <textarea name="bio" id="bio" rows="3" placeholder="Tell us about yourself..."
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs focus:border-primary-500 focus:ring-primary-500 sm:text-sm">{{ old('bio', $user->bio ?? '') }}</textarea>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Brief description for your profile.</p>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end gap-3">
                <button type="button" onclick="window.location.reload()" class="btn btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Account Statistics -->
<div class="mt-6 bg-white dark:bg-gray-800 shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Account Statistics</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Characters Created</div>
                <div class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['characters_created'] }}
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Training Sessions</div>
                <div class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['training_sessions'] }}
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Races Completed</div>
                <div class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['races_completed'] }}
                </div>
            </div>
        </div>
    </div>
</div>
