@extends('layouts.app')

@section('title', 'Your Profile')

@section('content')
    {{-- Breadcrumb Navigation --}}
    <x-breadcrumb :items="[['label' => 'Profile']]" />

    <div class="space-y-6" x-data="profileManager()">
        <!-- Page Header -->
        <div class="border-b border-gray-200 dark:border-gray-700 pb-5">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Your Profile</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Manage your account settings, preferences, and privacy controls
            </p>
        </div>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="rounded-md bg-green-50 dark:bg-green-900/20 p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                            clip-rule="evenodd" />
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-md bg-red-50 dark:bg-red-900/20 p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
                            clip-rule="evenodd" />
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-200">There were errors with your
                            submission</h3>
                        <ul class="mt-2 text-sm text-red-700 dark:text-red-300 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Single Form for All Tabs -->
        <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Tab Navigation -->
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex gap-2 overflow-x-auto scrollbar-hide" aria-label="Profile sections">
                    <button type="button" @click="activeTab = 'account'"
                        :class="activeTab === 'account' ?
                            'border-primary-500 text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20' :
                            'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800'"
                        class="whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm transition-all duration-200 rounded-t-lg"
                        role="tab" :aria-selected="activeTab === 'account'">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Account
                        </span>
                    </button>
                    <button type="button" @click="activeTab = 'preferences'"
                        :class="activeTab === 'preferences' ?
                            'border-primary-500 text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20' :
                            'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800'"
                        class="whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm transition-all duration-200 rounded-t-lg"
                        role="tab" :aria-selected="activeTab === 'preferences'">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Preferences
                        </span>
                    </button>
                    <button type="button" @click="activeTab = 'notifications'"
                        :class="activeTab === 'notifications' ?
                            'border-primary-500 text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20' :
                            'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800'"
                        class="whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm transition-all duration-200 rounded-t-lg"
                        role="tab" :aria-selected="activeTab === 'notifications'">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            Notifications
                        </span>
                    </button>
                    <button type="button" @click="activeTab = 'privacy'"
                        :class="activeTab === 'privacy' ?
                            'border-primary-500 text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20' :
                            'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800'"
                        class="whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm transition-all duration-200 rounded-t-lg"
                        role="tab" :aria-selected="activeTab === 'privacy'">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Privacy & Data
                        </span>
                    </button>
                    <button type="button" @click="activeTab = 'security'"
                        :class="activeTab === 'security' ?
                            'border-primary-500 text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20' :
                            'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300 hover:bg-gray-50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800'"
                        class="whitespace-nowrap py-3 px-4 border-b-2 font-medium text-sm transition-all duration-200 rounded-t-lg"
                        role="tab" :aria-selected="activeTab === 'security'">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            Security
                        </span>
                    </button>
                </nav>
            </div>

            <!-- Account Tab -->
            <div x-show="activeTab === 'account'" x-transition role="tabpanel">
                @include('profile.partials.account-tab-content', ['user' => $user, 'stats' => $stats])
            </div>

            <!-- Preferences Tab -->
            <div x-show="activeTab === 'preferences'" x-transition role="tabpanel">
                @include('profile.partials.preferences-tab-content', ['user' => $user])
            </div>

            <!-- Notifications Tab -->
            <div x-show="activeTab === 'notifications'" x-transition role="tabpanel">
                @include('profile.partials.notifications-tab-content', ['user' => $user])
            </div>

            <!-- Privacy & Data Tab -->
            <div x-show="activeTab === 'privacy'" x-transition role="tabpanel">
                @include('profile.partials.privacy-tab-content', ['user' => $user])
            </div>

            <!-- Security Tab (Separate Forms) -->
            <div x-show="activeTab === 'security'" x-transition role="tabpanel">
                @include('profile.partials.security-tab', ['user' => $user])
            </div>

            <!-- Save Button (shown for all tabs except Security) -->
            <div x-show="activeTab !== 'security'"
                class="flex justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                <button type="button" onclick="window.location.reload()" class="btn btn-secondary">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            function profileManager() {
                return {
                    activeTab: 'account',
                    loading: false,

                    init() {
                        // Check URL hash for tab
                        const hash = window.location.hash.replace('#', '');
                        if (hash && ['account', 'preferences', 'notifications', 'privacy', 'security'].includes(hash)) {
                            this.activeTab = hash;
                        }

                        // Update URL hash when tab changes
                        this.$watch('activeTab', (value) => {
                            window.location.hash = value;
                        });
                    }
                };
            }

            // Make avatarUploader globally available for Alpine.js
            window.avatarUploader = function avatarUploader() {
                return {
                    previewUrl: null,
                    uploading: false,
                    error: null,
                    success: null,

                    handleFileSelect(event) {
                        const file = event.target.files[0];
                        if (!file) return;

                        // Reset messages
                        this.error = null;
                        this.success = null;

                        // Validate file type
                        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                        if (!validTypes.includes(file.type)) {
                            this.error = 'Please select a valid image file (JPG, PNG, or GIF).';
                            return;
                        }

                        // Validate file size (2MB)
                        if (file.size > 2 * 1024 * 1024) {
                            this.error = 'File size must be less than 2MB.';
                            return;
                        }

                        // Show preview
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.previewUrl = e.target.result;
                        };
                        reader.readAsDataURL(file);

                        // Upload file
                        this.uploadAvatar(file);
                    },

                    async uploadAvatar(file) {
                        this.uploading = true;
                        this.error = null;

                        const formData = new FormData();
                        formData.append('avatar', file);

                        try {
                            const response = await fetch('{{ route('profile.avatar') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                },
                                body: formData
                            });

                            const data = await response.json();

                            if (!response.ok) {
                                throw new Error(data.message || 'Upload failed');
                            }

                            this.success = 'Avatar updated successfully!';

                            // Update preview with server URL if available
                            if (data.avatar_url) {
                                this.previewUrl = data.avatar_url;
                            }

                            // Clear success message after 3 seconds
                            setTimeout(() => {
                                this.success = null;
                            }, 3000);

                        } catch (error) {
                            this.error = error.message || 'Failed to upload avatar. Please try again.';
                            this.previewUrl = null;
                        } finally {
                            this.uploading = false;
                            // Reset file input
                            this.$refs.fileInput.value = '';
                        }
                    },

                    async removeAvatar() {
                        if (!confirm('Are you sure you want to remove your avatar?')) {
                            return;
                        }

                        this.uploading = true;
                        this.error = null;

                        try {
                            const response = await fetch('{{ route('profile.avatar.delete') }}', {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                },
                            });

                            const data = await response.json();

                            if (!response.ok) {
                                throw new Error(data.message || 'Failed to remove avatar');
                            }

                            this.previewUrl = null;
                            this.success = 'Avatar removed successfully!';

                            // Clear success message after 3 seconds
                            setTimeout(() => {
                                this.success = null;
                            }, 3000);

                        } catch (error) {
                            this.error = error.message || 'Failed to remove avatar. Please try again.';
                        } finally {
                            this.uploading = false;
                        }
                    }
                };
            }
        </script>
    @endpush
@endsection
