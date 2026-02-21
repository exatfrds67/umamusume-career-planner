<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['currentRoute' => null]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['currentRoute' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="flex grow flex-col gap-y-5 overflow-y-auto bg-white dark:bg-gray-800 px-6 pb-4" id="sidebar-navigation"
    :aria-expanded="!$store.sidebar.minimized ? 'true' : 'false'" x-data="{
        dataOpen: false,
        analyticsOpen: false,
        aiOpen: false,
        toolsOpen: false,
        adminOpen: false
    }">
    <!-- Logo Section with Hover Toggle -->
    <div x-data="{ showToggle: false }" @mouseenter="showToggle = true" @mouseleave="showToggle = false"
        class="relative flex h-16 shrink-0 items-center border-b border-gray-200 dark:border-gray-700"
        :class="$store.sidebar.minimized ? 'justify-center' : 'gap-3'">
        <!-- Logo -->
        <img src="/images/app_logo/uma_musume_race_planner_logo_128.png"
            alt="<?php echo e(config('app.name', 'Umamusume Career Planner')); ?> logo" class="h-10 w-10 shrink-0" width="40"
            height="40" loading="eager">

        <!-- Logo Text (Expanded Only) -->
        <span x-show="!$store.sidebar.minimized" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="text-base font-bold text-primary-600 dark:text-primary-400 leading-tight">
            Umamusume<br>Career Planner
        </span>

        <!-- Toggle Button (appears on hover) -->
        <div x-show="showToggle" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" class="absolute top-2 right-2" style="display: none;">
            <button @click="$store.sidebar.toggle()" type="button"
                class="p-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500"
                :aria-label="$store.sidebar.minimized ? 'Expand sidebar' : 'Minimize sidebar'"
                :aria-pressed="$store.sidebar.minimized ? 'true' : 'false'" aria-controls="sidebar-navigation"
                :title="$store.sidebar.minimized ? 'Expand sidebar' : 'Minimize sidebar'">
                <!-- Chevron Double Left (Minimize) -->
                <svg x-show="!$store.sidebar.minimized" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m18.75 4.5-7.5 7.5 7.5 7.5m-6-15L5.25 12l7.5 7.5" />
                </svg>

                <!-- Chevron Double Right (Expand) -->
                <svg x-show="$store.sidebar.minimized" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15 7.5 7.5-7.5 7.5" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex flex-1 flex-col">
        <ul class="flex flex-1 flex-col gap-y-7">
            <li>
                <ul class="-mx-2 space-y-1">
                    <!-- Primary Navigation (Always Visible) -->
                    <!-- Dashboard -->
                    <li>
                        <a href="<?php echo e(route('dashboard') ?? '#'); ?>"
                            class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 <?php echo e(request()->routeIs('dashboard') ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400' : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-primary-400'); ?>">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                            Dashboard
                        </a>
                    </li>

                    <!-- Characters -->
                    <li>
                        <a href="<?php echo e(route('characters.index') ?? '#'); ?>"
                            class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 <?php echo e(request()->routeIs('characters.*') ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400' : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-primary-400'); ?>">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                            </svg>
                            Characters
                        </a>
                    </li>

                    <!-- Training -->
                    <li>
                        <a href="<?php echo e(route('training.predictions') ?? '#'); ?>"
                            class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 <?php echo e(request()->routeIs('training.*') ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400' : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-primary-400'); ?>">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
                            </svg>
                            Training
                        </a>
                    </li>

                    <!-- Races -->
                    <li>
                        <a href="<?php echo e(route('races.index') ?? '#'); ?>"
                            class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 <?php echo e(request()->routeIs('races.*') ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400' : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-primary-400'); ?>">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                            </svg>
                            Races
                        </a>
                    </li>

                    <!-- Skills -->
                    <li>
                        <a href="<?php echo e(route('skills.index') ?? '#'); ?>"
                            class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 <?php echo e(request()->routeIs('skills.*') ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400' : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-primary-400'); ?>">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 13.5 10.5 6.75m0 0L7.5 3.75m3 3L13.5 3.75M3.75 19.5l6.75-6.75m0 0 3 3m-3-3 3-3m6.75 6.75-6.75-6.75m0 0 3-3m-3 3-3-3" />
                            </svg>
                            Skills
                        </a>
                    </li>

                    <!-- Support Cards -->
                    <li>
                        <a href="<?php echo e(route('support-cards.index') ?? '#'); ?>"
                            class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 <?php echo e(request()->routeIs('support-cards.*') ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400' : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-primary-400'); ?>">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3" />
                            </svg>
                            Support Cards
                        </a>
                    </li>

                    <!-- Divider -->
                    <li class="border-t border-gray-200 dark:border-gray-700 my-2"></li>

                    <!-- Secondary Navigation (Collapsible Groups - Available to All Users) -->

                    <!-- Data Management Group -->
                    <li>
                        <button @click="dataOpen = !dataOpen" type="button" aria-label="Toggle Data Management menu"
                            class="group flex w-full items-center gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 text-gray-700 hover:bg-gray-50 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-primary-400">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                            </svg>
                            <span class="flex-1 text-left">Data Management</span>
                            <svg class="h-5 w-5 shrink-0 transition-transform" :class="dataOpen ? 'rotate-90' : ''"
                                fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                        <ul x-show="dataOpen" x-collapse class="mt-1 space-y-1 pl-11">
                            <li>
                                <a href="<?php echo e(route('data-management.index') ?? '#'); ?>"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 <?php echo e(request()->routeIs('data-management.*') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-700 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400'); ?>">
                                    Data Hub
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('import.index') ?? '#'); ?>"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 <?php echo e(request()->routeIs('import.*') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-700 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400'); ?>">
                                    Import Data
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('export.index') ?? '#'); ?>"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 <?php echo e(request()->routeIs('export.*') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-700 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400'); ?>">
                                    Export Data
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('migration.index') ?? '#'); ?>"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 <?php echo e(request()->routeIs('migration.*') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-700 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400'); ?>">
                                    Migration
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('backup.index') ?? '#'); ?>"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 <?php echo e(request()->routeIs('backup.*') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-700 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400'); ?>">
                                    Backup & Restore
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Analytics & Reports Group -->
                    <li>
                        <button @click="analyticsOpen = !analyticsOpen" type="button"
                            aria-label="Toggle Analytics & Reports menu"
                            class="group flex w-full items-center gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 text-gray-700 hover:bg-gray-50 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-primary-400">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                            </svg>
                            <span class="flex-1 text-left">Analytics & Reports</span>
                            <svg class="h-5 w-5 shrink-0 transition-transform"
                                :class="analyticsOpen ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                        <ul x-show="analyticsOpen" x-collapse class="mt-1 space-y-1 pl-11">
                            <li>
                                <a href="<?php echo e(route('reports.index') ?? '#'); ?>"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 <?php echo e(request()->routeIs('reports.*') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-700 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400'); ?>">
                                    Career Reports
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('historical.index') ?? '#'); ?>"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 <?php echo e(request()->routeIs('historical.*') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-700 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400'); ?>">
                                    Historical Tracking
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- AI & Tools Group -->
                    <li>
                        <button @click="aiOpen = !aiOpen" type="button" aria-label="Toggle AI & Tools menu"
                            class="group flex w-full items-center gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 text-gray-700 hover:bg-gray-50 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-primary-400">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                            </svg>
                            <span class="flex-1 text-left">AI & Tools</span>
                            <svg class="h-5 w-5 shrink-0 transition-transform" :class="aiOpen ? 'rotate-90' : ''"
                                fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                        <ul x-show="aiOpen" x-collapse class="mt-1 space-y-1 pl-11">
                            <li>
                                <a href="<?php echo e(route('ai.dashboard') ?? '#'); ?>"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 <?php echo e(request()->routeIs('ai.dashboard') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-700 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400'); ?>">
                                    AI Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('ai.chat') ?? '#'); ?>"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 <?php echo e(request()->routeIs('ai.chat') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-700 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400'); ?>">
                                    AI Chat
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('mcp.dashboard') ?? '#'); ?>"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 <?php echo e(request()->routeIs('mcp.dashboard') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-700 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400'); ?>">
                                    MCP Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('ocr.upload') ?? '#'); ?>"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 <?php echo e(request()->routeIs('ocr.*') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-700 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400'); ?>">
                                    OCR Upload
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- External Resources Group -->
                    <li>
                        <button @click="toolsOpen = !toolsOpen" type="button"
                            aria-label="Toggle External Resources menu"
                            class="group flex w-full items-center gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 text-gray-700 hover:bg-gray-50 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-primary-400">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                            <span class="flex-1 text-left">External Resources</span>
                            <svg class="h-5 w-5 shrink-0 transition-transform" :class="toolsOpen ? 'rotate-90' : ''"
                                fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                        <ul x-show="toolsOpen" x-collapse class="mt-1 space-y-1 pl-11">
                            <li>
                                <a href="<?php echo e(route('external-data.browse') ?? '#'); ?>"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 <?php echo e(request()->routeIs('external-data.*') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-700 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400'); ?>">
                                    Browse External Data
                                </a>
                            </li>
                        </ul>
                    </li>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isAdmin()): ?>
                            <!-- Admin Divider -->
                            <li class="border-t-2 border-amber-300 dark:border-amber-700 my-3"></li>

                            <!-- Admin Panel (Only visible to admin users) -->
                            <li>
                                <button @click="adminOpen = !adminOpen" type="button"
                                    aria-label="Toggle Admin Panel menu"
                                    class="group flex w-full items-center gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 bg-amber-50 text-amber-700 hover:bg-amber-100 hover:text-amber-800 dark:bg-amber-900/20 dark:text-amber-400 dark:hover:bg-amber-900/30 dark:hover:text-amber-300">
                                    <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                    </svg>
                                    <span class="flex-1 text-left">Admin Panel</span>
                                    <svg class="h-5 w-5 shrink-0 transition-transform"
                                        :class="adminOpen ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                </button>
                                <ul x-show="adminOpen" x-collapse
                                    class="mt-1 space-y-1 pl-11 bg-amber-50/50 dark:bg-amber-900/10 rounded-md py-2">
                                    <!-- System Administration -->
                                    <li
                                        class="px-2 py-1 text-xs font-semibold text-amber-800 dark:text-amber-300 uppercase tracking-wider">
                                        System Administration
                                    </li>
                                    <li>
                                        <a href="/admin/users"
                                            class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 text-gray-700 hover:text-amber-700 dark:text-gray-400 dark:hover:text-amber-400">
                                            User Management
                                        </a>
                                    </li>
                                    <li>
                                        <a href="/admin/system-settings"
                                            class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 text-gray-700 hover:text-amber-700 dark:text-gray-400 dark:hover:text-amber-400">
                                            System Settings
                                        </a>
                                    </li>
                                    <li>
                                        <a href="/admin/logs"
                                            class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 text-gray-700 hover:text-amber-700 dark:text-gray-400 dark:hover:text-amber-400">
                                            System Logs
                                        </a>
                                    </li>

                                    <!-- Database Management -->
                                    <li class="border-t border-amber-200 dark:border-amber-800 my-2"></li>
                                    <li
                                        class="px-2 py-1 text-xs font-semibold text-amber-800 dark:text-amber-300 uppercase tracking-wider">
                                        Database Management
                                    </li>
                                    <li>
                                        <a href="/admin/database/maintenance"
                                            class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 text-gray-700 hover:text-amber-700 dark:text-gray-400 dark:hover:text-amber-400">
                                            Database Maintenance
                                        </a>
                                    </li>
                                    <li>
                                        <a href="/admin/database/seeders"
                                            class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 text-gray-700 hover:text-amber-700 dark:text-gray-400 dark:hover:text-amber-400">
                                            Run Seeders
                                        </a>
                                    </li>

                                    <!-- Monitoring & Diagnostics -->
                                    <li class="border-t border-amber-200 dark:border-amber-800 my-2"></li>
                                    <li
                                        class="px-2 py-1 text-xs font-semibold text-amber-800 dark:text-amber-300 uppercase tracking-wider">
                                        Monitoring & Diagnostics
                                    </li>
                                    <li>
                                        <a href="<?php echo e(route('performance.apm.dashboard') ?? '#'); ?>"
                                            class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 <?php echo e(request()->routeIs('performance.*') ? 'text-amber-700 font-semibold dark:text-amber-400' : 'text-gray-700 hover:text-amber-700 dark:text-gray-400 dark:hover:text-amber-400'); ?>">
                                            Performance Monitor
                                        </a>
                                    </li>
                                    <li>
                                        <a href="/admin/queue-monitor"
                                            class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 text-gray-700 hover:text-amber-700 dark:text-gray-400 dark:hover:text-amber-400">
                                            Queue Monitor
                                        </a>
                                    </li>
                                    <li>
                                        <a href="/telescope" target="_blank"
                                            class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 text-gray-700 hover:text-amber-700 dark:text-gray-400 dark:hover:text-amber-400">
                                            Telescope
                                        </a>
                                    </li>
                                    <li>
                                        <a href="/horizon" target="_blank"
                                            class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 text-gray-700 hover:text-amber-700 dark:text-gray-400 dark:hover:text-amber-400">
                                            Horizon
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ul>
            </li>

            <!-- Bottom Navigation -->
            <li class="mt-auto">
                <ul class="-mx-2 space-y-1">
                    <!-- Profile -->
                    <li>
                        <a href="<?php echo e(route('profile.show') ?? '#'); ?>"
                            class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 <?php echo e(request()->routeIs('profile.*') ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400' : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-primary-400'); ?>">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            Profile
                        </a>
                    </li>
                    <!-- Settings -->
                    <li>
                        <a href="<?php echo e(route('settings.index') ?? '#'); ?>"
                            class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 <?php echo e(request()->routeIs('settings.*') ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400' : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-primary-400'); ?>">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            Settings
                        </a>
                    </li>
                    <!-- Help -->
                    <li>
                        <a href="<?php echo e(route('help.index') ?? '#'); ?>"
                            class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 <?php echo e(request()->routeIs('help.*') ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400' : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-primary-400'); ?>">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                            </svg>
                            Help
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
</div>
<?php /**PATH C:\XAMPP\htdocs\umamusume-career-planner\resources\views/components/app/sidebar.blade.php ENDPATH**/ ?>