                    <div>
                        <label for="title" class="form-label">Character Title/Variant</label>
                        <input type="text" id="title" name="title" x-model="formData.title" maxlength="100"
                            class="form-input" placeholder="e.g. Special Dreamer, Hopp'n♪Happy Heart, Innocent Silence">
                        <p class="form-help">Optional title or variant (e.g. [Special Dreamer], [Hopp'n♪Happy Heart])</p>
                    </div>

                    <div>
                        <label for="name" class="form-label">Character Name <span class="text-red-500"
                                aria-label="required">*</span></label>
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
                                                loading="lazy" decoding="async"
                                                class="w-full h-full object-cover pointer-events-none"
                                                x-on:error="$el.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent(formData.name || 'User') + '&background=random'"
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
                                <?php
                                    $traineeImages = [
                                        '/images/trainee_images/__special_week_umamusume_drawn_by_mikawa_ayumu__c5289136bde8f5e1cb096090308a8496.jpg' => 'Special Week',
                                        '/images/trainee_images/bb962aabeafaee5cbf7831e4d178ca64.jpg' => 'Silence Suzuka',
                                        '/images/trainee_images/__tokai_teio_umamusume_drawn_by_so_on__305c01834a0c0cf3fe3593c281a0b05b.jpg' => 'Tokai Teio',
                                        '/images/trainee_images/__maruzensky_umamusume_drawn_by_kamishima_kanon__sample-297ecca0da3990374954a514f06bea2b.jpg' => 'Maruzensky',
                                        '/images/trainee_images/__gold_ship_umamusume_drawn_by_advarcher__sample-2713426899554240b99dc00440e97745.jpg' => 'Gold Ship',
                                        '/images/trainee_images/__vodka_umamusume_drawn_by_mayata__41166bfaeb2670ae37c8785af4566d58.jpg' => 'Vodka',
                                        '/images/trainee_images/__daiwa_scarlet_umamusume_drawn_by_kurokawa_heuy__sample-9576ae268cdddfe167c2300d5453f2cf.jpg' => 'Daiwa Scarlet',
                                        '/images/trainee_images/__special_week_and_grass_wonder_umamusume_drawn_by_murasaki_himuro__c5cd811241a372d775e9ba2e2d09c65f.jpg' => 'Grass Wonder',
                                        '/images/trainee_images/__el_condor_pasa_umamusume_drawn_by_nekogusa_kinako__85cfefb3697093d2c40c9db031ab46a5.jpg' => 'El Condor Pasa',
                                        '/images/trainee_images/__t_m_opera_o_umamusume_drawn_by_eriario__7e3d265d1e3e405cf79bdce81ba87adf.jpg' => 'T.M. Opera O',
                                        '/images/trainee_images/__narita_brian_and_biwa_hayahide_umamusume_drawn_by_hitoto__sample-e1edfe57e7e12f49d5a724698f738783.jpg' => 'Narita Brian',
                                        '/images/trainee_images/__rice_shower_umamusume_drawn_by_jjjsss__ab0944b4a248893cddca61ce1ee4a1e9.jpg' => 'Rice Shower',
                                        '/images/trainee_images/__agnes_tachyon_umamusume_drawn_by_welchino__sample-1db2ca428e2545fcae81fe526d7a8e96.jpg' => 'Agnes Tachyon',
                                        '/images/trainee_images/__smart_falcon_umamusume_drawn_by_motsutoko__695387267327663d2f7a9eb898107126.jpg' => 'Smart Falcon',
                                        '/images/trainee_images/__kitasan_black_umamusume_drawn_by_mattya122__47e067dbb97ad9a28758789d05c28f90.jpg' => 'Kitasan Black',
                                        '/images/trainee_images/__haru_urara_umamusume_drawn_by_advarcher__sample-7d1c3c431ef193e5e061bdda73f97fd5.jpg' => 'Haru Urara',
                                        '/images/trainee_images/__twin_turbo_umamusume_drawn_by_urujika__718f48540797d2872f7a1d578edae44a.jpg' => 'Twin Turbo',
                                        '/images/trainee_images/__nice_nature_umamusume_drawn_by_sekiyu_inu__d1d0d3773cf6b911f6ac6b1f753acff0.jpg' => 'Nice Nature',
                                    ];
                                ?>
                                <?php $__currentLoopData = $traineeImages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $imagePath => $imageName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <button type="button" @click="selectGalleryImage('<?php echo e($imagePath); ?>')"
                                        class="relative group aspect-square rounded-lg overflow-hidden border-2 transition-all hover:border-primary-500 hover:scale-105"
                                        :class="formData.avatar_url === '<?php echo e($imagePath); ?>' ?
                                            'border-primary-500 ring-2 ring-primary-500' :
                                            'border-gray-300 dark:border-gray-600'">
                                        <img src="<?php echo e($imagePath); ?>" alt="<?php echo e($imageName); ?>" loading="lazy"
                                            decoding="async" class="w-full h-full object-cover">
                                        <div
                                            class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <span
                                                class="text-white text-xs font-medium text-center px-2"><?php echo e($imageName); ?></span>
                                        </div>
                                        <div x-show="formData.avatar_url === '<?php echo e($imagePath); ?>'"
                                            class="absolute top-1 right-1 bg-primary-500 rounded-full p-1">
                                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>

                        <input type="hidden" name="avatar_url" x-model="formData.avatar_url">
                    </div>

                    <fieldset>
                        <legend class="form-label mb-3">Scenario Type <span class="text-red-500"
                                aria-label="required">*</span></legend>
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
                    </fieldset><?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/characters/partials/basic-info-fields.blade.php ENDPATH**/ ?>