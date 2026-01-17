@extends('layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto space-y-6" x-data="characterWizard()">
        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1
                    class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
                    Create New Character
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Set up your Umamusume for career training
                </p>
            </div>
            <div class="mt-4 sm:ml-4 sm:mt-0">
                <a href="{{ route('characters.index') }}" class="btn btn-secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Characters
                </a>
            </div>
        </div>

        <!-- Progress Steps -->
        <div class="glass-card rounded-xl p-6">
            <nav aria-label="Progress">
                <ol role="list" class="flex items-center justify-between">
                    @php
                        $steps = [
                            [
                                'id' => 1,
                                'name' => 'Basic Info',
                                'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                            ],
                            [
                                'id' => 2,
                                'name' => 'Stats',
                                'icon' =>
                                    'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                            ],
                            [
                                'id' => 3,
                                'name' => 'Aptitudes',
                                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                            ],
                            [
                                'id' => 4,
                                'name' => 'Review',
                                'icon' =>
                                    'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                            ],
                        ];
                    @endphp
                    @foreach ($steps as $index => $step)
                        <li class="relative {{ $index < count($steps) - 1 ? 'pr-8 sm:pr-20' : '' }} flex-1">
                            @if ($index < count($steps) - 1)
                                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                    <div class="h-0.5 w-full"
                                        :class="currentStep > {{ $step['id'] }} ? 'bg-primary-600' :
                                            'bg-gray-200 dark:bg-gray-700'">
                                    </div>
                                </div>
                            @endif
                            <button type="button" @click="goToStep({{ $step['id'] }})"
                                class="relative flex items-center justify-center w-10 h-10 rounded-full transition-all"
                                :class="currentStep === {{ $step['id'] }} ?
                                    'bg-primary-600 ring-4 ring-primary-100 dark:ring-primary-900/30' :
                                    currentStep > {{ $step['id'] }} ? 'bg-primary-600' :
                                    'bg-gray-200 dark:bg-gray-700'"
                                :aria-current="currentStep === {{ $step['id'] }} ? 'step' : null">
                                <svg class="w-5 h-5"
                                    :class="currentStep >= {{ $step['id'] }} ? 'text-white' :
                                        'text-gray-500 dark:text-gray-400'"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="{{ $step['icon'] }}" />
                                </svg>
                                <span class="sr-only">{{ $step['name'] }}</span>
                            </button>
                            <span class="absolute top-12 left-1/2 -translate-x-1/2 whitespace-nowrap text-xs font-medium"
                                :class="currentStep === {{ $step['id'] }} ? 'text-primary-600 dark:text-primary-400' :
                                    'text-gray-500 dark:text-gray-400'">
                                {{ $step['name'] }}
                            </span>
                        </li>
                    @endforeach
                </ol>
            </nav>
        </div>

        <!-- Flash Messages -->
        @if ($errors->any())
            <div class="alert alert-error">
                <div class="flex items-start">
                    <svg class="w-5 h-5 mr-2 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    <div class="flex-1">
                        <p class="font-semibold mb-2">Please correct the following errors:</p>
                        <ul class="list-disc list-inside space-y-1 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('characters.store') }}" id="character-form">
            @csrf

            <!-- Step 1: Basic Information -->
            <div x-show="currentStep === 1" x-transition class="glass-card rounded-xl">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Basic Information</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Enter your character's name and select a
                        scenario</p>
                </div>
                <div class="card-body space-y-6">
                    <div>
                        <label for="title" class="form-label">Character Title/Variant</label>
                        <input type="text" id="title" name="title" x-model="formData.title" maxlength="100"
                            class="form-input" placeholder="e.g. Special Dreamer, Hopp'n♪Happy Heart, Innocent Silence">
                        <p class="form-help">Optional title or variant (e.g. [Special Dreamer], [Hopp'n♪Happy Heart])</p>
                    </div>

                    <div>
                        <label for="name" class="form-label">Character Name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" x-model="formData.name" required maxlength="100"
                            class="form-input" placeholder="e.g. Special Week, Silence Suzuka">
                        <p class="form-help">The base name of your Umamusume trainee</p>
                    </div>

                    <!-- Full Name Preview -->
                    <div x-show="formData.title || formData.name"
                        class="p-4 bg-linear-to-r from-primary-50 to-purple-50 dark:from-primary-900/20 dark:to-purple-900/20 rounded-lg border border-primary-200 dark:border-primary-800">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-primary-600 dark:text-primary-400 shrink-0" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            <div class="flex-1">
                                <p class="text-xs font-semibold text-primary-700 dark:text-primary-300 mb-1">Full Character
                                    Name Preview:</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">
                                    <span x-show="formData.title" class="text-primary-600 dark:text-primary-400"
                                        x-text="'[' + formData.title + '] '"></span>
                                    <span x-text="formData.name || 'Enter name...'"></span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Character Avatar/Image -->
                    <div>
                        <label class="form-label block mb-4">Character Avatar/Image</label>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Left Column: Avatar Preview -->
                            <div class="flex items-center justify-center">
                                <div
                                    class="flex flex-col items-center justify-center p-8 bg-gray-50 dark:bg-gray-700/50 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 w-full">
                                    <div class="w-64 h-64 mb-4 relative bg-gray-800 dark:bg-gray-900 overflow-hidden"
                                        tabindex="0" @keydown.arrow-up.prevent="moveImage(0, -5)"
                                        @keydown.arrow-down.prevent="moveImage(0, 5)"
                                        @keydown.arrow-left.prevent="moveImage(-5, 0)"
                                        @keydown.arrow-right.prevent="moveImage(5, 0)">
                                        <!-- Base image layer - fills entire container, positioned behind mask -->
                                        <div x-show="formData.avatar_preview" class="absolute inset-0"
                                            :style="`transform: translate(${formData.imageX}px, ${formData.imageY}px);`">
                                            <img :src="formData.avatar_preview" alt="Avatar preview"
                                                class="w-full h-full object-cover pointer-events-none"
                                                :style="`transform: scale(${formData.imageZoom}) rotate(${formData.imageRotation}deg) scaleX(${formData.imageFlipH ? -1 : 1}); transform-origin: center center;`">
                                        </div>
                                        <!-- Translucent mask layer - HIDES image except in circular cutout -->
                                        <div x-show="formData.avatar_preview"
                                            class="absolute inset-0 pointer-events-none bg-gray-900/60 dark:bg-gray-950/70"
                                            style="mask-image: radial-gradient(circle 96px at center, transparent 0%, transparent 96px, black 96px, black 100%); -webkit-mask-image: radial-gradient(circle 96px at center, transparent 0%, transparent 96px, black 96px, black 100%);">
                                        </div>
                                        <!-- Draggable overlay for interaction -->
                                        <div x-show="formData.avatar_preview" class="absolute inset-0 cursor-move"
                                            @mousedown="startDrag($event)" @touchstart="startDrag($event)">
                                        </div>
                                        <!-- Circular border to define the focus area -->
                                        <div
                                            class="absolute top-1/2 left-1/2 w-48 h-48 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-primary-400 dark:border-primary-500 pointer-events-none">
                                        </div>
                                        <!-- Empty state -->
                                        <div x-show="!formData.avatar_preview"
                                            class="absolute inset-0 flex items-center justify-center text-gray-400 dark:text-gray-500">
                                            <svg class="w-32 h-32" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                    </div>
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 text-center">
                                        <span x-show="formData.avatar_preview">Use arrow keys or drag to position</span>
                                        <span x-show="!formData.avatar_preview">No avatar selected</span>
                                    </p>
                                </div>
                            </div>

                            <!-- Right Column: Controls -->
                            <div class="space-y-4">
                                <!-- Action Buttons -->
                                <div class="space-y-2">
                                    <!-- Upload Custom Image -->
                                    <div>
                                        <label class="btn btn-secondary w-full cursor-pointer justify-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            Upload Custom Image
                                            <input type="file" name="avatar_upload" accept="image/*" class="hidden"
                                                @change="handleImageUpload($event)">
                                        </label>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 text-center">JPG, PNG, GIF
                                            (max 2MB)</p>
                                    </div>

                                    <!-- Select from Gallery -->
                                    <button type="button" @click="showGallery = !showGallery"
                                        class="btn btn-secondary w-full justify-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                        <span x-text="showGallery ? 'Hide Gallery' : 'Select from Gallery'"></span>
                                    </button>

                                    <!-- Use Default -->
                                    <button type="button" @click="useDefaultAvatar()"
                                        class="btn btn-outline w-full justify-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                        </svg>
                                        Use Default Avatar
                                    </button>
                                </div>

                                <!-- Image Editor Panel -->
                                <div x-show="formData.avatar_preview"
                                    class="glass-card-inner border border-gray-200 dark:border-gray-700 rounded-lg">
                                    <div class="card-header">
                                        <h4
                                            class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                            </svg>
                                            Image Editor
                                        </h4>
                                    </div>
                                    <div class="card-body space-y-4">
                                        <!-- Zoom Control -->
                                        <div>
                                            <label class="form-label text-xs flex items-center justify-between mb-2">
                                                <span>Zoom</span>
                                                <span class="text-primary-600 dark:text-primary-400 font-mono"
                                                    x-text="Math.round(formData.imageZoom * 100) + '%'"></span>
                                            </label>
                                            <div class="flex items-center gap-2">
                                                <button type="button" @click="adjustZoom(-0.1)"
                                                    class="btn btn-sm btn-outline px-2">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" />
                                                    </svg>
                                                </button>
                                                <input type="range" min="0.5" max="2" step="0.1"
                                                    x-model.number="formData.imageZoom"
                                                    class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer">
                                                <button type="button" @click="adjustZoom(0.1)"
                                                    class="btn btn-sm btn-outline px-2">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Rotation Control -->
                                        <div>
                                            <label class="form-label text-xs flex items-center justify-between mb-2">
                                                <span>Rotation</span>
                                                <span class="text-primary-600 dark:text-primary-400 font-mono"
                                                    x-text="formData.imageRotation + '°'"></span>
                                            </label>
                                            <div class="flex items-center gap-2">
                                                <button type="button" @click="rotateImage(-15)"
                                                    class="btn btn-sm btn-outline px-2">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                                    </svg>
                                                </button>
                                                <input type="range" min="0" max="360" step="15"
                                                    x-model.number="formData.imageRotation"
                                                    class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer">
                                                <button type="button" @click="rotateImage(15)"
                                                    class="btn btn-sm btn-outline px-2">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Quick Actions -->
                                        <div class="flex gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                                            <button type="button" @click="flipImageHorizontal()"
                                                class="btn btn-sm btn-outline flex-1 justify-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                                </svg>
                                                Flip
                                            </button>
                                            <button type="button" @click="resetImageEdits()"
                                                class="btn btn-sm btn-outline flex-1 justify-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                                Reset
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Image Gallery -->
                        <div x-show="showGallery" x-transition class="mt-4 p-6 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 text-center">Select from
                                Trainee
                                Gallery
                            </h4>
                            <div
                                class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-9 gap-3 max-h-80 overflow-y-auto">
                                @php
                                    $traineeImages = [
                                        '/images/trainee_images/__agnes_tachyon_umamusume_drawn_by_welchino__sample-1db2ca428e2545fcae81fe526d7a8e96.jpg' =>
                                            'Agnes Tachyon',
                                        '/images/trainee_images/__daiwa_scarlet_umamusume_drawn_by_kurokawa_heuy__sample-9576ae268cdddfe167c2300d5453f2cf.jpg' =>
                                            'Daiwa Scarlet',
                                        '/images/trainee_images/__el_condor_pasa_umamusume_drawn_by_nekogusa_kinako__85cfefb3697093d2c40c9db031ab46a5.jpg' =>
                                            'El Condor Pasa',
                                        '/images/trainee_images/__gold_ship_umamusume_drawn_by_advarcher__sample-2713426899554240b99dc00440e97745.jpg' =>
                                            'Gold Ship',
                                        '/images/trainee_images/__haru_urara_umamusume_drawn_by_advarcher__sample-7d1c3c431ef193e5e061bdda73f97fd5.jpg' =>
                                            'Haru Urara',
                                        '/images/trainee_images/__maruzensky_umamusume_drawn_by_kamishima_kanon__sample-297ecca0da3990374954a514f06bea2b.jpg' =>
                                            'Maruzensky',
                                        '/images/trainee_images/__narita_brian_umamusume_drawn_by_no_uwazumi__sample-0f3c352063a7077cb5708b8284dd7217.jpg' =>
                                            'Narita Brian',
                                        '/images/trainee_images/__tokai_teio_umamusume_drawn_by_so_on__305c01834a0c0cf3fe3593c281a0b05b.jpg' =>
                                            'Tokai Teio',
                                        '/images/trainee_images/__vodka_umamusume_drawn_by_mayata__41166bfaeb2670ae37c8785af4566d58.jpg' =>
                                            'Vodka',
                                    ];
                                @endphp
                                @foreach ($traineeImages as $imagePath => $imageName)
                                    <button type="button" @click="selectGalleryImage('{{ $imagePath }}')"
                                        class="relative group aspect-square rounded-lg overflow-hidden border-2 transition-all hover:border-primary-500 hover:scale-105"
                                        :class="formData.avatar_url === '{{ $imagePath }}' ?
                                            'border-primary-500 ring-2 ring-primary-500' :
                                            'border-gray-300 dark:border-gray-600'">
                                        <img src="{{ $imagePath }}" alt="{{ $imageName }}"
                                            class="w-full h-full object-cover">
                                        <div
                                            class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <span
                                                class="text-white text-xs font-medium text-center px-2">{{ $imageName }}</span>
                                        </div>
                                        <div x-show="formData.avatar_url === '{{ $imagePath }}'"
                                            class="absolute top-1 right-1 bg-primary-500 rounded-full p-1">
                                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <input type="hidden" name="avatar_url" x-model="formData.avatar_url">
                    </div>

                    <div>
                        <label class="form-label mb-3">Scenario Type <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label
                                class="relative flex flex-col p-4 rounded-lg border-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all"
                                :class="formData.scenario_type === 'ura_finale' ?
                                    'border-primary-500 bg-primary-50 dark:bg-primary-900/20' :
                                    'border-gray-200 dark:border-gray-700'">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-base font-semibold text-gray-900 dark:text-white">URA Finale</span>
                                    <input type="radio" name="scenario_type" value="ura_finale"
                                        x-model="formData.scenario_type" required
                                        class="h-4 w-4 text-primary-600 border-gray-300 focus:ring-primary-500">
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Traditional individual development path
                                    with classic races</p>
                                <div class="mt-3 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    <span>Classic Mode</span>
                                </div>
                            </label>

                            <label
                                class="relative flex flex-col p-4 rounded-lg border-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all"
                                :class="formData.scenario_type === 'unity_cup' ?
                                    'border-primary-500 bg-primary-50 dark:bg-primary-900/20' :
                                    'border-gray-200 dark:border-gray-700'">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-base font-semibold text-gray-900 dark:text-white">Unity Cup</span>
                                    <input type="radio" name="scenario_type" value="unity_cup"
                                        x-model="formData.scenario_type" required
                                        class="h-4 w-4 text-primary-600 border-gray-300 focus:ring-primary-500">
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Team-based training with Spirit Burst
                                    mechanics</p>
                                <div class="mt-3 flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span>Team Mode</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-footer flex justify-between">
                    <button type="button" disabled class="btn btn-secondary opacity-50 cursor-not-allowed">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Previous
                    </button>
                    <button type="button" @click="nextStep()" :disabled="!formData.name || !formData.scenario_type"
                        class="btn btn-primary"
                        :class="!formData.name || !formData.scenario_type ? 'opacity-50 cursor-not-allowed' : ''">
                        Next
                        <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Step 2: Stats -->
            <div x-show="currentStep === 2" x-transition class="glass-card rounded-xl">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Current Stats</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Set initial stat values (0-1200)</p>
                </div>
                <div class="card-body">
                    @php
                        $stats = [
                            'speed' => [
                                'label' => 'Speed',
                                'priority' => '★★★★★',
                                'color' => 'blue',
                                'desc' => 'Acceleration & top speed',
                            ],
                            'stamina' => [
                                'label' => 'Stamina',
                                'priority' => '★★★★',
                                'color' => 'orange',
                                'desc' => 'Endurance for long races',
                            ],
                            'power' => [
                                'label' => 'Power',
                                'priority' => '★★★',
                                'color' => 'red',
                                'desc' => 'Uphill & final sprint',
                            ],
                            'guts' => [
                                'label' => 'Guts',
                                'priority' => '★',
                                'color' => 'pink',
                                'desc' => 'Maintain position',
                            ],
                            'wit' => [
                                'label' => 'Wit',
                                'priority' => '★★',
                                'color' => 'green',
                                'desc' => 'Skill activation rate',
                            ],
                        ];
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach ($stats as $stat => $info)
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label for="stat_{{ $stat }}" class="form-label flex items-center gap-2">
                                        <span class="font-semibold">{{ $info['label'] }}</span>
                                        <span class="text-xs text-primary-500"
                                            title="Priority">{{ $info['priority'] }}</span>
                                    </label>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $info['desc'] }}</span>
                                </div>
                                <div class="relative">
                                    <input type="number" id="stat_{{ $stat }}"
                                        name="stats[{{ $stat }}]"
                                        x-model.number="formData.stats.{{ $stat }}" min="0"
                                        max="1200" step="10" required
                                        class="form-input pr-16 text-lg font-semibold"
                                        @input="validateStat('{{ $stat }}')">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <span class="text-sm font-medium px-2 py-1 rounded"
                                            :class="getGradeColor(formData.stats.{{ $stat }})"
                                            x-text="getGrade(formData.stats.{{ $stat }})"></span>
                                    </div>
                                </div>
                                <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-{{ $info['color'] }}-500 transition-all duration-300"
                                        :style="`width: ${(formData.stats.{{ $stat }} / 1200) * 100}%`"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div
                        class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            <div class="text-sm text-blue-800 dark:text-blue-200">
                                <p class="font-semibold mb-1">Training Tips:</p>
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                    <li>901-1200: Optimal training range with best gains</li>
                                    <li>1200+: Diminishing returns, focus on other stats</li>
                                    <li>Hidden race boost adds +400 to displayed stats</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer flex justify-between">
                    <button type="button" @click="previousStep()" class="btn btn-secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Previous
                    </button>
                    <button type="button" @click="nextStep()" class="btn btn-primary">
                        Next
                        <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Step 3: Aptitudes -->
            <div x-show="currentStep === 3" x-transition class="glass-card rounded-xl">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Aptitudes</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Select aptitude grades for distance, surface,
                        and running style</p>
                </div>
                <div class="card-body">
                    @php $grades = ['G', 'F', 'E', 'D', 'C', 'B', 'A', 'S', 'SS']; @endphp
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Distance -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b-2 border-primary-500">
                                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                                    Distance</h4>
                            </div>
                            <div class="space-y-3">
                                @foreach (['sprint' => 'Sprint (1000-1400m)', 'mile' => 'Mile (1401-1800m)', 'medium' => 'Medium (1801-2400m)', 'long' => 'Long (2401m+)'] as $key => $label)
                                    <div>
                                        <label class="form-label text-xs font-semibold">{{ $label }}</label>
                                        <select name="aptitudes[distance][{{ $key }}]"
                                            x-model="formData.aptitudes.distance.{{ $key }}" required
                                            class="form-select text-sm">
                                            <option value="">Select Grade</option>
                                            @foreach ($grades as $grade)
                                                <option value="{{ $grade }}">{{ $grade }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Surface -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b-2 border-green-500">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                                    Surface</h4>
                            </div>
                            <div class="space-y-3">
                                @foreach (['turf' => 'Turf', 'dirt' => 'Dirt'] as $key => $label)
                                    <div>
                                        <label class="form-label text-xs font-semibold">{{ $label }}</label>
                                        <select name="aptitudes[surface][{{ $key }}]"
                                            x-model="formData.aptitudes.surface.{{ $key }}" required
                                            class="form-select text-sm">
                                            <option value="">Select Grade</option>
                                            @foreach ($grades as $grade)
                                                <option value="{{ $grade }}">{{ $grade }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Running Style -->
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b-2 border-purple-500">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                                    Running Style</h4>
                            </div>
                            <div class="space-y-3">
                                @foreach (['front_runner' => 'Front Runner', 'pace_chaser' => 'Pace Chaser', 'late_surger' => 'Late Surger', 'end_closer' => 'End Closer'] as $key => $label)
                                    <div>
                                        <label class="form-label text-xs font-semibold">{{ $label }}</label>
                                        <select name="aptitudes[style][{{ $key }}]"
                                            x-model="formData.aptitudes.style.{{ $key }}" required
                                            class="form-select text-sm">
                                            <option value="">Select Grade</option>
                                            @foreach ($grades as $grade)
                                                <option value="{{ $grade }}">{{ $grade }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer flex justify-between">
                    <button type="button" @click="previousStep()" class="btn btn-secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Previous
                    </button>
                    <button type="button" @click="nextStep()" :disabled="!isStep3Valid()" class="btn btn-primary"
                        :class="!isStep3Valid() ? 'opacity-50 cursor-not-allowed' : ''">
                        Next
                        <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Step 4: Review -->
            <div x-show="currentStep === 4" x-transition class="glass-card rounded-xl">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Review & Confirm</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Review your character details before creating
                    </p>
                </div>
                <div class="card-body space-y-6">
                    <!-- Basic Info Summary -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Basic Information</h4>
                        <dl class="grid grid-cols-1 gap-4">
                            <!-- Avatar Preview -->
                            <div x-show="formData.avatar_preview" class="flex items-center gap-4">
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Avatar</dt>
                                <dd>
                                    <div
                                        class="w-24 h-24 rounded-full border-2 border-primary-400 dark:border-primary-500 overflow-hidden relative bg-gray-200 dark:bg-gray-700">
                                        <!-- Replicate the editor's 256px container scaled down to 96px (96/256 = 0.375) -->
                                        <div class="absolute"
                                            style="width: 256px; height: 256px; left: 50%; top: 50%; transform: translate(-50%, -50%) scale(0.375); transform-origin: center center;">
                                            <div class="absolute inset-0"
                                                :style="`transform: translate(${formData.imageX}px, ${formData.imageY}px);`">
                                                <img :src="formData.avatar_preview" alt="Character avatar"
                                                    class="w-full h-full object-cover"
                                                    :style="`transform: scale(${formData.imageZoom}) rotate(${formData.imageRotation}deg) scaleX(${formData.imageFlipH ? -1 : 1}); transform-origin: center center;`">
                                            </div>
                                        </div>
                                    </div>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Full Character Name</dt>
                                <dd class="text-base font-bold text-gray-900 dark:text-white">
                                    <span x-show="formData.title" class="text-primary-600 dark:text-primary-400"
                                        x-text="'[' + formData.title + '] '"></span>
                                    <span x-text="formData.name || 'Not set'"></span>
                                </dd>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-xs text-gray-500 dark:text-gray-400">Scenario</dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-white"
                                        x-text="formData.scenario_type === 'ura_finale' ? 'URA Finale' : 'Unity Cup'"></dd>
                                </div>
                            </div>
                        </dl>
                    </div>

                    <!-- Stats Summary -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Stats</h4>
                        <div class="grid grid-cols-5 gap-3">
                            <template x-for="(value, stat) in formData.stats" :key="stat">
                                <div class="text-center">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 uppercase mb-1" x-text="stat">
                                    </div>
                                    <div class="text-lg font-bold text-gray-900 dark:text-white" x-text="value"></div>
                                    <div class="text-xs font-medium px-2 py-0.5 rounded inline-block mt-1"
                                        :class="getGradeColor(value)" x-text="getGrade(value)"></div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Aptitudes Summary -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Aptitudes</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                            <div>
                                <div class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Distance</div>
                                <template x-for="(value, key) in formData.aptitudes.distance" :key="key">
                                    <div class="flex justify-between py-1">
                                        <span class="text-gray-600 dark:text-gray-400 capitalize" x-text="key"></span>
                                        <span class="font-medium text-gray-900 dark:text-white"
                                            x-text="value || '-'"></span>
                                    </div>
                                </template>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Surface</div>
                                <template x-for="(value, key) in formData.aptitudes.surface" :key="key">
                                    <div class="flex justify-between py-1">
                                        <span class="text-gray-600 dark:text-gray-400 capitalize" x-text="key"></span>
                                        <span class="font-medium text-gray-900 dark:text-white"
                                            x-text="value || '-'"></span>
                                    </div>
                                </template>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-700 dark:text-gray-300 mb-2">Running Style</div>
                                <template x-for="(value, key) in formData.aptitudes.style" :key="key">
                                    <div class="flex justify-between py-1">
                                        <span class="text-gray-600 dark:text-gray-400 capitalize"
                                            x-text="key.replace('_', ' ')"></span>
                                        <span class="font-medium text-gray-900 dark:text-white"
                                            x-text="value || '-'"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer flex justify-between">
                    <div class="flex gap-2">
                        <button type="button" @click="previousStep()" class="btn btn-secondary">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                            Previous
                        </button>
                        <button type="button"
                            @click="if(confirm('Clear all draft data? This cannot be undone.')) { clearStorage(); location.reload(); }"
                            class="btn btn-outline text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Clear Draft
                        </button>
                    </div>
                    <button type="submit" @click="clearStorage()" class="btn btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Create Character
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function characterWizard() {
            return {
                currentStep: 1,
                showGallery: false,
                formData: {
                    title: '',
                    name: '',
                    avatar_url: '',
                    avatar_preview: '',
                    imageZoom: 1,
                    imageRotation: 0,
                    imageFlipH: false,
                    imageX: 0,
                    imageY: 0,
                    scenario_type: '',
                    stats: {
                        speed: 0,
                        stamina: 0,
                        power: 0,
                        guts: 0,
                        wit: 0
                    },
                    aptitudes: {
                        distance: {
                            sprint: '',
                            mile: '',
                            medium: '',
                            long: ''
                        },
                        surface: {
                            turf: '',
                            dirt: ''
                        },
                        style: {
                            front_runner: '',
                            pace_chaser: '',
                            late_surger: '',
                            end_closer: ''
                        }
                    }
                },
                isDragging: false,
                dragStartX: 0,
                dragStartY: 0,

                init() {
                    // Load saved data from localStorage
                    this.loadFromStorage();

                    // Save to localStorage whenever formData changes
                    this.$watch('formData', () => {
                        this.saveToStorage();
                    }, {
                        deep: true
                    });

                    // Add global mouse/touch event listeners for dragging
                    document.addEventListener('mousemove', (e) => this.onDrag(e));
                    document.addEventListener('mouseup', () => this.stopDrag());
                    document.addEventListener('touchmove', (e) => this.onDrag(e));
                    document.addEventListener('touchend', () => this.stopDrag());
                },

                loadFromStorage() {
                    const saved = localStorage.getItem('characterWizardData');
                    if (saved) {
                        try {
                            const data = JSON.parse(saved);
                            // Merge saved data with default formData
                            Object.assign(this.formData, data);
                        } catch (e) {
                            console.error('Failed to load saved data:', e);
                        }
                    }
                },

                saveToStorage() {
                    try {
                        localStorage.setItem('characterWizardData', JSON.stringify(this.formData));
                    } catch (e) {
                        console.error('Failed to save data:', e);
                    }
                },

                clearStorage() {
                    localStorage.removeItem('characterWizardData');
                },

                nextStep() {
                    if (this.currentStep < 4) {
                        this.currentStep++;
                    }
                },

                previousStep() {
                    if (this.currentStep > 1) {
                        this.currentStep--;
                    }
                },

                goToStep(step) {
                    // Allow navigation to previous steps or current step
                    if (step <= this.currentStep) {
                        this.currentStep = step;
                    }
                },

                handleImageUpload(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    // Validate file type
                    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                    if (!validTypes.includes(file.type)) {
                        alert('Please upload a valid image file (JPG, PNG, or GIF)');
                        return;
                    }

                    // Validate file size (max 2MB)
                    const maxSize = 2 * 1024 * 1024; // 2MB in bytes
                    if (file.size > maxSize) {
                        alert('File size must be less than 2MB');
                        return;
                    }

                    // Create preview URL
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.formData.avatar_preview = e.target.result;
                        this.formData.avatar_url = 'custom_upload'; // Mark as custom upload
                        this.showGallery = false;
                    };
                    reader.readAsDataURL(file);
                },

                selectGalleryImage(imagePath) {
                    this.formData.avatar_url = imagePath;
                    this.formData.avatar_preview = imagePath;
                    this.showGallery = false;
                },

                useDefaultAvatar() {
                    this.formData.avatar_url = '';
                    this.formData.avatar_preview = '';
                    this.showGallery = false;
                    this.resetImageEdits();
                },

                adjustZoom(delta) {
                    const newZoom = this.formData.imageZoom + delta;
                    if (newZoom >= 0.5 && newZoom <= 2) {
                        this.formData.imageZoom = Math.round(newZoom * 10) / 10;
                    }
                },

                rotateImage(degrees) {
                    this.formData.imageRotation = (this.formData.imageRotation + degrees) % 360;
                    if (this.formData.imageRotation < 0) {
                        this.formData.imageRotation += 360;
                    }
                },

                flipImageHorizontal() {
                    this.formData.imageFlipH = !this.formData.imageFlipH;
                },

                resetImageEdits() {
                    this.formData.imageZoom = 1;
                    this.formData.imageRotation = 0;
                    this.formData.imageFlipH = false;
                    this.formData.imageX = 0;
                    this.formData.imageY = 0;
                },

                moveImage(deltaX, deltaY) {
                    this.formData.imageX += deltaX;
                    this.formData.imageY += deltaY;
                },

                startDrag(event) {
                    this.isDragging = true;
                    const clientX = event.touches ? event.touches[0].clientX : event.clientX;
                    const clientY = event.touches ? event.touches[0].clientY : event.clientY;
                    this.dragStartX = clientX - this.formData.imageX;
                    this.dragStartY = clientY - this.formData.imageY;
                },

                onDrag(event) {
                    if (!this.isDragging) return;
                    event.preventDefault();
                    const clientX = event.touches ? event.touches[0].clientX : event.clientX;
                    const clientY = event.touches ? event.touches[0].clientY : event.clientY;
                    this.formData.imageX = clientX - this.dragStartX;
                    this.formData.imageY = clientY - this.dragStartY;
                },

                stopDrag() {
                    this.isDragging = false;
                },

                validateStat(stat) {
                    const value = this.formData.stats[stat];
                    if (value < 0) {
                        this.formData.stats[stat] = 0;
                    } else if (value > 1200) {
                        this.formData.stats[stat] = 1200;
                    }
                },

                getGrade(value) {
                    if (value >= 1200) return 'SS';
                    if (value >= 1100) return 'S';
                    if (value >= 1000) return 'A+';
                    if (value >= 900) return 'A';
                    if (value >= 800) return 'B+';
                    if (value >= 700) return 'B';
                    if (value >= 600) return 'C+';
                    if (value >= 500) return 'C';
                    if (value >= 400) return 'D+';
                    if (value >= 300) return 'D';
                    if (value >= 200) return 'E+';
                    if (value >= 100) return 'E';
                    if (value >= 50) return 'F';
                    return 'G';
                },

                getGradeColor(value) {
                    if (value >= 1200) return 'bg-linear-to-r from-yellow-400 to-amber-500 text-white';
                    if (value >= 1100) return 'bg-linear-to-r from-purple-500 to-pink-500 text-white';
                    if (value >= 1000) return 'bg-red-500 text-white';
                    if (value >= 900) return 'bg-red-500 text-white';
                    if (value >= 800) return 'bg-orange-500 text-white';
                    if (value >= 700) return 'bg-orange-500 text-white';
                    if (value >= 600) return 'bg-yellow-500 text-gray-900';
                    if (value >= 500) return 'bg-yellow-500 text-gray-900';
                    if (value >= 400) return 'bg-green-500 text-white';
                    if (value >= 300) return 'bg-green-500 text-white';
                    if (value >= 200) return 'bg-blue-500 text-white';
                    if (value >= 100) return 'bg-blue-500 text-white';
                    if (value >= 50) return 'bg-gray-500 text-white';
                    return 'bg-gray-400 text-white';
                },

                isStep3Valid() {
                    // Check if all aptitudes are selected
                    const distance = Object.values(this.formData.aptitudes.distance).every(v => v !== '');
                    const surface = Object.values(this.formData.aptitudes.surface).every(v => v !== '');
                    const style = Object.values(this.formData.aptitudes.style).every(v => v !== '');
                    return distance && surface && style;
                }
            };
        }
    </script>
@endsection
