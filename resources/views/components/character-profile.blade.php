<div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden" {{ $attributes }}>
    <div class="{{ $layoutClasses() }}">
        <!-- Image Section -->
        @if($image)
            <div class="{{ $imageSectionClasses() }}">
                <div class="relative w-full {{ $imageSizeClasses() }} overflow-hidden rounded-lg {{ $showBorder ? 'border-2 border-gray-300 dark:border-gray-600' : '' }} bg-gray-100 dark:bg-gray-700">
                    <img 
                        src="{{ $image }}" 
                        alt="{{ $name }}"
                        class="w-full h-full object-cover"
                        loading="lazy"
                    />
                </div>
            </div>
        @endif

        <!-- Content Section -->
        <div class="{{ $contentSectionClasses() }} py-4">
            @if($name || $title)
                <div class="mb-4">
                    @if($name)
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ $name }}
                        </h2>
                    @endif
                    
                    @if($title)
                        <p class="text-lg text-gray-600 dark:text-gray-400 mt-1">
                            {{ $title }}
                        </p>
                    @endif
                </div>
            @endif

            <!-- Slot for additional content -->
            <div class="space-y-6">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>