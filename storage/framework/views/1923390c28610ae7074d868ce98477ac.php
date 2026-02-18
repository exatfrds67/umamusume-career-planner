<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'Umamusume Career Planner')); ?></title>

    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="<?php echo e(asset('favicon.ico')); ?>">
    <link rel="icon" type="image/x-icon" sizes="16x16"
        href="<?php echo e(asset('images/app_logo/uma_musume_race_planner_logo_16.ico')); ?>">
    <link rel="icon" type="image/x-icon" sizes="32x32"
        href="<?php echo e(asset('images/app_logo/uma_musume_race_planner_logo_32.ico')); ?>">
    <link rel="icon" type="image/png" sizes="128x128"
        href="<?php echo e(asset('images/app_logo/uma_musume_race_planner_logo_128.png')); ?>">
    <link rel="icon" type="image/png" sizes="256x256"
        href="<?php echo e(asset('images/app_logo/uma_musume_race_planner_logo_256.png')); ?>">
    <link rel="icon" type="image/png" sizes="512x512"
        href="<?php echo e(asset('images/app_logo/uma_musume_race_planner_logo_512.png')); ?>">
    <link rel="apple-touch-icon" sizes="128x128"
        href="<?php echo e(asset('images/app_logo/uma_musume_race_planner_logo_128.png')); ?>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="dns-prefetch" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500" rel="stylesheet"
        media="print" onload="this.media='all'" />
    <noscript>
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500" rel="stylesheet" />
    </noscript>

    <!-- Synchronous Theme Initialization (prevents flash/mismatch on page load) -->
    <script>
        (function() {
            const stored = localStorage.getItem('theme');
            let theme;

            if (stored) {
                theme = stored;
            } else {
                theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                // Store the system preference so it's consistent across pages
                localStorage.setItem('theme', theme);
            }

            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    <!-- Scripts & Styles -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>

<body class="h-full font-sans antialiased text-gray-900 dark:text-gray-100" x-data="{ sidebarOpen: false }"
    @keydown.escape.window="sidebarOpen = false">

    <!-- Skip to Content (Accessibility) -->
    <a href="#main-content"
        class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 z-50 px-4 py-2 bg-blue-600 text-white rounded-md shadow-lg">
        Skip to content
    </a>

    <!-- Fixed Background with Theme-Aware Images -->
    <div id="app-background" class="fixed inset-0 z-0 bg-cover bg-center bg-no-repeat transition-opacity duration-500"
        data-bg-light-desktop="/images/app_bg/uma_musume_race_planner_bg_light_1536x1028.png"
        data-bg-light-mobile="/images/app_bg/uma_musume_race_planner_bg_light_1028x1536.png"
        data-bg-dark-desktop="/images/app_bg/uma_musume_race_planner_bg_dark_1536x1028.png"
        data-bg-dark-mobile="/images/app_bg/uma_musume_race_planner_bg_dark_1028x1536.png" aria-hidden="true">
    </div>

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/80 z-40 lg:hidden" aria-hidden="true"
        @click="sidebarOpen = false"></div>

    <!-- Mobile Sidebar (shown when sidebarOpen is true) -->
    <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform"
        x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 z-50 w-72 bg-white dark:bg-gray-800 shadow-xl lg:hidden">
        <?php if (isset($component)) { $__componentOriginal790df3a3003b05a46d3e5fdd59aeab47 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal790df3a3003b05a46d3e5fdd59aeab47 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app.sidebar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app.sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal790df3a3003b05a46d3e5fdd59aeab47)): ?>
<?php $attributes = $__attributesOriginal790df3a3003b05a46d3e5fdd59aeab47; ?>
<?php unset($__attributesOriginal790df3a3003b05a46d3e5fdd59aeab47); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal790df3a3003b05a46d3e5fdd59aeab47)): ?>
<?php $component = $__componentOriginal790df3a3003b05a46d3e5fdd59aeab47; ?>
<?php unset($__componentOriginal790df3a3003b05a46d3e5fdd59aeab47); ?>
<?php endif; ?>
    </div>

    <!-- Desktop Sidebar (always visible on lg screens) -->
    <div x-data :class="$store.sidebar.minimized ? 'lg:w-20' : 'lg:w-72'"
        class="hidden sm:hidden md:hidden lg:fixed lg:inset-y-0 lg:left-0 lg:z-50 lg:flex lg:flex-col lg:border-r lg:border-gray-200 dark:lg:border-gray-700 lg:bg-white dark:lg:bg-gray-800 transition-all duration-300 ease-in-out">
        <?php if (isset($component)) { $__componentOriginal790df3a3003b05a46d3e5fdd59aeab47 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal790df3a3003b05a46d3e5fdd59aeab47 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app.sidebar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app.sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal790df3a3003b05a46d3e5fdd59aeab47)): ?>
<?php $attributes = $__attributesOriginal790df3a3003b05a46d3e5fdd59aeab47; ?>
<?php unset($__attributesOriginal790df3a3003b05a46d3e5fdd59aeab47); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal790df3a3003b05a46d3e5fdd59aeab47)): ?>
<?php $component = $__componentOriginal790df3a3003b05a46d3e5fdd59aeab47; ?>
<?php unset($__componentOriginal790df3a3003b05a46d3e5fdd59aeab47); ?>
<?php endif; ?>
    </div>

    <!-- Main Column of Content -->
    <div x-data :class="$store.sidebar.minimized ? 'lg:pl-20' : 'lg:pl-72'"
        class="flex flex-col min-h-screen transition-all duration-300 ease-in-out relative z-10">
        <?php
            $topStatus = $topStatus ?? [];
        ?>

        <!-- Sticky Header -->
        <header
            class="sticky top-0 z-40 border-b border-gray-200 dark:border-gray-700 bg-white/80 dark:bg-gray-800/80 backdrop-blur-md shadow-sm">
            <div class="flex flex-col">
                
                <?php if (isset($component)) { $__componentOriginal0aef750c747fe83eb00868ad99bfbb99 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0aef750c747fe83eb00868ad99bfbb99 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.top-status-bar','data' => ['currentTurn' => $topStatus['currentTurn'] ?? null,'maxTurns' => $topStatus['maxTurns'] ?? null,'spAvailable' => $topStatus['spAvailable'] ?? null,'storageMode' => $topStatus['storageMode'] ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('top-status-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['current-turn' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($topStatus['currentTurn'] ?? null),'max-turns' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($topStatus['maxTurns'] ?? null),'sp-available' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($topStatus['spAvailable'] ?? null),'storage-mode' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($topStatus['storageMode'] ?? null)]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0aef750c747fe83eb00868ad99bfbb99)): ?>
<?php $attributes = $__attributesOriginal0aef750c747fe83eb00868ad99bfbb99; ?>
<?php unset($__attributesOriginal0aef750c747fe83eb00868ad99bfbb99); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0aef750c747fe83eb00868ad99bfbb99)): ?>
<?php $component = $__componentOriginal0aef750c747fe83eb00868ad99bfbb99; ?>
<?php unset($__componentOriginal0aef750c747fe83eb00868ad99bfbb99); ?>
<?php endif; ?>

                <div class="flex h-16 shrink-0 items-center gap-x-4 px-4 sm:gap-x-6 sm:px-6 lg:px-8">
                    <button type="button" class="-m-2.5 p-2.5 text-gray-700 dark:text-gray-200 lg:hidden"
                        @click="sidebarOpen = true">
                        <span class="sr-only">Open sidebar</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>

                    <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                        <?php if (isset($component)) { $__componentOriginal6f648324bf790658b48f4e99fb28ec74 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6f648324bf790658b48f4e99fb28ec74 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app.header','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app.header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6f648324bf790658b48f4e99fb28ec74)): ?>
<?php $attributes = $__attributesOriginal6f648324bf790658b48f4e99fb28ec74; ?>
<?php unset($__attributesOriginal6f648324bf790658b48f4e99fb28ec74); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6f648324bf790658b48f4e99fb28ec74)): ?>
<?php $component = $__componentOriginal6f648324bf790658b48f4e99fb28ec74; ?>
<?php unset($__componentOriginal6f648324bf790658b48f4e99fb28ec74); ?>
<?php endif; ?>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Header (if provided via slot) -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($header)): ?>
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <?php echo e($header); ?>

                </div>
            </header>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Main Content -->
        <main class="py-10 pb-24 lg:pb-10" id="main-content">
            <div class="px-4 sm:px-6 lg:px-8">
                <?php echo $__env->yieldContent('content'); ?>
                <?php echo e($slot ?? ''); ?>

            </div>
        </main>
    </div>

    <?php if (isset($component)) { $__componentOriginal6ef049aab2f7e75bb3760a1106bdfcdb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ef049aab2f7e75bb3760a1106bdfcdb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.bottom-nav-bar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('bottom-nav-bar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ef049aab2f7e75bb3760a1106bdfcdb)): ?>
<?php $attributes = $__attributesOriginal6ef049aab2f7e75bb3760a1106bdfcdb; ?>
<?php unset($__attributesOriginal6ef049aab2f7e75bb3760a1106bdfcdb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ef049aab2f7e75bb3760a1106bdfcdb)): ?>
<?php $component = $__componentOriginal6ef049aab2f7e75bb3760a1106bdfcdb; ?>
<?php unset($__componentOriginal6ef049aab2f7e75bb3760a1106bdfcdb); ?>
<?php endif; ?>

    <!-- Offline Indicator (Task 2.2.1) -->
    <?php if (isset($component)) { $__componentOriginal30ed851a7370ef0c75347addc2809e2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal30ed851a7370ef0c75347addc2809e2c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.offline-indicator','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('offline-indicator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal30ed851a7370ef0c75347addc2809e2c)): ?>
<?php $attributes = $__attributesOriginal30ed851a7370ef0c75347addc2809e2c; ?>
<?php unset($__attributesOriginal30ed851a7370ef0c75347addc2809e2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal30ed851a7370ef0c75347addc2809e2c)): ?>
<?php $component = $__componentOriginal30ed851a7370ef0c75347addc2809e2c; ?>
<?php unset($__componentOriginal30ed851a7370ef0c75347addc2809e2c); ?>
<?php endif; ?>

    <!-- Accessibility Settings Panel (WCAG 2.2 AA) -->
    <?php if (isset($component)) { $__componentOriginaldb8cd85b4bb8d97824ed48d5b3ff8a8b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldb8cd85b4bb8d97824ed48d5b3ff8a8b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.accessibility-settings-panel','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('accessibility-settings-panel'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldb8cd85b4bb8d97824ed48d5b3ff8a8b)): ?>
<?php $attributes = $__attributesOriginaldb8cd85b4bb8d97824ed48d5b3ff8a8b; ?>
<?php unset($__attributesOriginaldb8cd85b4bb8d97824ed48d5b3ff8a8b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldb8cd85b4bb8d97824ed48d5b3ff8a8b)): ?>
<?php $component = $__componentOriginaldb8cd85b4bb8d97824ed48d5b3ff8a8b; ?>
<?php unset($__componentOriginaldb8cd85b4bb8d97824ed48d5b3ff8a8b); ?>
<?php endif; ?>

    <!-- Screen Reader Announcer for Sidebar State -->
    <div id="sidebar-announcer" class="sr-only" role="status" aria-live="polite" aria-atomic="true"></div>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/layouts/app.blade.php ENDPATH**/ ?>