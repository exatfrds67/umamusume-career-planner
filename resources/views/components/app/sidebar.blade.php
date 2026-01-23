@props(['currentRoute' => null])

<div class="flex grow flex-col gap-y-5 overflow-y-auto bg-white dark:bg-gray-800 px-6 pb-4">
    <!-- Logo -->
    <div class="flex h-16 shrink-0 items-center gap-3 border-b border-gray-200 dark:border-gray-700">
        <img src="/images/app_logo/uma_musume_race_planner_logo_128.png"
            alt="{{ config('app.name', 'Umamusume Career Planner') }} logo" class="h-10 w-10 shrink-0" width="40"
            height="40" loading="eager">
        <span class="text-base font-bold text-primary-600 dark:text-primary-400 leading-tight">
            Umamusume<br>Career Planner
        </span>
    </div>

    <!-- Navigation -->
    <nav class="flex flex-1 flex-col">
        <ul class="flex flex-1 flex-col gap-y-7">
            <li>
                <ul class="-mx-2 space-y-1">
                    <!-- Dashboard -->
                    <li>
                        <a href="{{ route('dashboard') ?? '#' }}"
                            class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 {{ request()->routeIs('dashboard') ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400' : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-primary-400' }}">
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
                        <a href="{{ route('characters.index') ?? '#' }}"
                            class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 {{ request()->routeIs('characters.*') ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400' : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-primary-400' }}">
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
                        <a href="{{ route('training.predictions') ?? '#' }}"
                            class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 {{ request()->routeIs('training.*') ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400' : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-primary-400' }}">
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
                        <a href="{{ route('races.index') ?? '#' }}"
                            class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 {{ request()->routeIs('races.*') ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400' : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-primary-400' }}">
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
                        <a href="{{ route('skills.index') ?? '#' }}"
                            class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 {{ request()->routeIs('skills.*') ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400' : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-primary-400' }}">
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
                        <a href="{{ route('support-cards.index') ?? '#' }}"
                            class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 {{ request()->routeIs('support-cards.*') ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400' : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-primary-400' }}">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3" />
                            </svg>
                            Support Cards
                        </a>
                    </li>
                </ul>
            </li>
            <li class="mt-auto">
                <a href="{{ route('settings.index') ?? '#' }}"
                    class="group -mx-2 flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 {{ request()->routeIs('settings.*') ? 'bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400' : 'text-gray-700 hover:bg-gray-50 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-primary-400' }}">
                    <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    Settings
                </a>
            </li>
        </ul>
    </nav>
</div>
