<div class="flex grow flex-col gap-y-5 overflow-y-auto pb-4"
    style="background: linear-gradient(180deg, #150D35 0%, #0A0620 100%);" id="sidebar-navigation" role="navigation"
    aria-label="Main navigation" :class="$store.sidebar.minimized ? 'px-2' : 'px-6'"
    :aria-expanded="!$store.sidebar.minimized ? 'true' : 'false'" x-data="{
        dataOpen: false,
        analyticsOpen: false,
        aiOpen: false,
        toolsOpen: false,
        adminOpen: false
    }">
    @php
        $activeCharacterId = session('current_character_id');
        $resolvedCharacterId = null;

        if (auth()->check()) {
            if (is_numeric($activeCharacterId)) {
                $resolvedCharacterId = \App\Models\Character::query()
                    ->where('user_id', (int) auth()->id())
                    ->whereKey((int) $activeCharacterId)
                    ->value('id');
            }

            if (!is_numeric($resolvedCharacterId)) {
                $resolvedCharacterId = \App\Models\Character::query()
                    ->where('user_id', (int) auth()->id())
                    ->latest('updated_at')
                    ->value('id');
            }
        }

        $careerPlanRoute =
            $resolvedCharacterId !== null
                ? route('characters.show', $resolvedCharacterId) . '#career-plan-visualizer'
                : route('characters.index');
    @endphp

    <!-- Logo Section with Hover Toggle -->
    <div x-data="{ showToggle: false }" @mouseenter="showToggle = true" @mouseleave="showToggle = false"
        class="relative flex h-16 shrink-0 items-center" style="border-bottom: 1px solid rgba(255,255,255,0.06);"
        :class="$store.sidebar.minimized ? 'justify-center' : 'gap-3'">
        <!-- Logo -->
        <div class="h-10 w-10 shrink-0 rounded-lg flex items-center justify-center"
            style="background: linear-gradient(135deg, #E879A0, #7C3AED); border-radius: 10px;">
            <span class="text-white text-lg leading-none" aria-hidden="true">🏇</span>
        </div>

        <!-- Logo Text (Expanded Only) -->
        <span x-show="!$store.sidebar.minimized" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="text-base font-bold leading-tight"
            style="font-family: 'Nunito', sans-serif; font-weight: 900; background: linear-gradient(90deg, #F9A8D4, #C4B5FD); -webkit-background-clip: text; color: transparent;">
            Uma Planner
        </span>

        <!-- Toggle Button (appears on hover) -->
        <div x-show="showToggle" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" class="absolute top-2 right-2" style="display: none;">
            <button @click="$store.sidebar.toggle()" type="button"
                class="p-2 rounded-lg transition-colors focus:outline-hidden focus:ring-2 focus:ring-inset focus:ring-pink-500"
                style="color: #7C6FAB;" x-data="{ btnHover: false }" @mouseenter="btnHover = true"
                @mouseleave="btnHover = false"
                :style="btnHover ? 'background-color: #3B1F6E; color: #FFFFFF;' : 'color: #7C6FAB;'"
                :aria-label="$store.sidebar.minimized ? 'Expand sidebar' : 'Minimize sidebar'"
                :aria-pressed="$store.sidebar.minimized ? 'true' : 'false'" aria-controls="sidebar-navigation"
                :title="$store.sidebar.minimized ? 'Expand sidebar' : 'Minimize sidebar'"
                data-testid="sidebar-collapse-toggle">
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
                        <a href="{{ route('dashboard') }}" x-data="{ showTooltip: false, hovered: false }"
                            @mouseenter="hovered = true; if ($store.sidebar.minimized) showTooltip = true"
                            @mouseleave="hovered = false; showTooltip = false"
                            class="relative group flex rounded-md p-2 text-sm font-semibold leading-6 transition-all duration-200"
                            :style="hovered && !{{ request()->routeIs('dashboard') ? 'true' : 'false' }} ?
                                'background-color: #3B1F6E; color: #FFFFFF;' : ''"
                            style="{{ request()->routeIs('dashboard') ? 'background: linear-gradient(135deg, #E879A0, #7C3AED); color: #FFFFFF;' : 'color: #7C6FAB;' }}"
                            :aria-current="request()->routeIs('dashboard') ? 'page' : null"
                            data-testid="sidebar-nav-dashboard"
                            :class="$store.sidebar.minimized ? 'justify-center' : 'gap-x-3'">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                            <span x-show="!$store.sidebar.minimized" class="whitespace-nowrap">Dashboard</span>
                            <div x-show="showTooltip" x-transition:enter="transition ease-out duration-150 delay-200"
                                x-transition:enter-start="opacity-0 translate-x-2"
                                x-transition:enter-end="opacity-100 translate-x-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-x-0"
                                x-transition:leave-end="opacity-0 translate-x-2"
                                class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-3 py-2 text-sm font-medium text-white bg-[#0A0620] rounded-lg shadow-lg whitespace-nowrap z-50 pointer-events-none"
                                role="tooltip" style="display: none;">Dashboard<div
                                    class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-[#0A0620] rotate-45">
                                </div>
                            </div>
                        </a>
                    </li>

                    <!-- Characters -->
                    <li>
                        <a href="{{ route('characters.index') }}" x-data="{ showTooltip: false, hovered: false }"
                            @mouseenter="hovered = true; if ($store.sidebar.minimized) showTooltip = true"
                            @mouseleave="hovered = false; showTooltip = false"
                            class="relative group flex rounded-md p-2 text-sm font-semibold leading-6 transition-all duration-200"
                            :style="hovered && !{{ request()->routeIs('characters.*') ? 'true' : 'false' }} ?
                                'background-color: #3B1F6E; color: #FFFFFF;' : ''"
                            style="{{ request()->routeIs('characters.*') ? 'background: linear-gradient(135deg, #E879A0, #7C3AED); color: #FFFFFF;' : 'color: #7C6FAB;' }}"
                            :aria-current="request()->routeIs('characters.*') ? 'page' : null"
                            data-testid="sidebar-nav-characters"
                            :class="$store.sidebar.minimized ? 'justify-center' : 'gap-x-3'">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                            </svg>
                            <span x-show="!$store.sidebar.minimized" class="whitespace-nowrap">Characters</span>
                            <div x-show="showTooltip" x-transition:enter="transition ease-out duration-150 delay-200"
                                x-transition:enter-start="opacity-0 translate-x-2"
                                x-transition:enter-end="opacity-100 translate-x-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-x-0"
                                x-transition:leave-end="opacity-0 translate-x-2"
                                class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-3 py-2 text-sm font-medium text-white bg-[#0A0620] rounded-lg shadow-lg whitespace-nowrap z-50 pointer-events-none"
                                role="tooltip" style="display: none;">Characters<div
                                    class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-[#0A0620] rotate-45">
                                </div>
                            </div>
                        </a>
                    </li>

                    <!-- Training -->
                    <li>
                        <a href="{{ route('training.predictions') }}" x-data="{ showTooltip: false, hovered: false }"
                            @mouseenter="hovered = true; if ($store.sidebar.minimized) showTooltip = true"
                            @mouseleave="hovered = false; showTooltip = false"
                            class="relative group flex rounded-md p-2 text-sm font-semibold leading-6 transition-all duration-200"
                            :style="hovered && !{{ request()->routeIs('training.*') ? 'true' : 'false' }} ?
                                'background-color: #3B1F6E; color: #FFFFFF;' : ''"
                            style="{{ request()->routeIs('training.*') ? 'background: linear-gradient(135deg, #E879A0, #7C3AED); color: #FFFFFF;' : 'color: #7C6FAB;' }}"
                            :aria-current="request()->routeIs('training.*') ? 'page' : null"
                            data-testid="sidebar-nav-training"
                            :class="$store.sidebar.minimized ? 'justify-center' : 'gap-x-3'">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
                            </svg>
                            <span x-show="!$store.sidebar.minimized" class="whitespace-nowrap">Training</span>
                            <div x-show="showTooltip" x-transition:enter="transition ease-out duration-150 delay-200"
                                x-transition:enter-start="opacity-0 translate-x-2"
                                x-transition:enter-end="opacity-100 translate-x-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-x-0"
                                x-transition:leave-end="opacity-0 translate-x-2"
                                class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-3 py-2 text-sm font-medium text-white bg-[#0A0620] rounded-lg shadow-lg whitespace-nowrap z-50 pointer-events-none"
                                role="tooltip" style="display: none;">Training<div
                                    class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-[#0A0620] rotate-45">
                                </div>
                            </div>
                        </a>
                    </li>

                    <!-- Races -->
                    <li>
                        <a href="{{ route('races.index') }}" x-data="{ showTooltip: false, hovered: false }"
                            @mouseenter="hovered = true; if ($store.sidebar.minimized) showTooltip = true"
                            @mouseleave="hovered = false; showTooltip = false"
                            class="relative group flex rounded-md p-2 text-sm font-semibold leading-6 transition-all duration-200"
                            :style="hovered && !{{ request()->routeIs('races.*') ? 'true' : 'false' }} ?
                                'background-color: #3B1F6E; color: #FFFFFF;' : ''"
                            style="{{ request()->routeIs('races.*') ? 'background: linear-gradient(135deg, #E879A0, #7C3AED); color: #FFFFFF;' : 'color: #7C6FAB;' }}"
                            :aria-current="request()->routeIs('races.*') ? 'page' : null"
                            data-testid="sidebar-nav-races"
                            :class="$store.sidebar.minimized ? 'justify-center' : 'gap-x-3'">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                            </svg>
                            <span x-show="!$store.sidebar.minimized" class="whitespace-nowrap">Races</span>
                            <div x-show="showTooltip" x-transition:enter="transition ease-out duration-150 delay-200"
                                x-transition:enter-start="opacity-0 translate-x-2"
                                x-transition:enter-end="opacity-100 translate-x-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-x-0"
                                x-transition:leave-end="opacity-0 translate-x-2"
                                class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-3 py-2 text-sm font-medium text-white bg-[#0A0620] rounded-lg shadow-lg whitespace-nowrap z-50 pointer-events-none"
                                role="tooltip" style="display: none;">Races<div
                                    class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-[#0A0620] rotate-45">
                                </div>
                            </div>
                        </a>
                    </li>

                    <!-- Skills -->
                    <li>
                        <a href="{{ route('skills.index') }}" x-data="{ showTooltip: false, hovered: false }"
                            @mouseenter="hovered = true; if ($store.sidebar.minimized) showTooltip = true"
                            @mouseleave="hovered = false; showTooltip = false"
                            class="relative group flex rounded-md p-2 text-sm font-semibold leading-6 transition-all duration-200"
                            :style="hovered && !{{ request()->routeIs('skills.*') ? 'true' : 'false' }} ?
                                'background-color: #3B1F6E; color: #FFFFFF;' : ''"
                            style="{{ request()->routeIs('skills.*') ? 'background: linear-gradient(135deg, #E879A0, #7C3AED); color: #FFFFFF;' : 'color: #7C6FAB;' }}"
                            :aria-current="request()->routeIs('skills.*') ? 'page' : null"
                            data-testid="sidebar-nav-skills"
                            :class="$store.sidebar.minimized ? 'justify-center' : 'gap-x-3'">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 13.5 10.5 6.75m0 0L7.5 3.75m3 3L13.5 3.75M3.75 19.5l6.75-6.75m0 0 3 3m-3-3 3-3m6.75 6.75-6.75-6.75m0 0 3-3m-3 3-3-3" />
                            </svg>
                            <span x-show="!$store.sidebar.minimized" class="whitespace-nowrap">Skills</span>
                            <div x-show="showTooltip" x-transition:enter="transition ease-out duration-150 delay-200"
                                x-transition:enter-start="opacity-0 translate-x-2"
                                x-transition:enter-end="opacity-100 translate-x-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-x-0"
                                x-transition:leave-end="opacity-0 translate-x-2"
                                class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-3 py-2 text-sm font-medium text-white bg-[#0A0620] rounded-lg shadow-lg whitespace-nowrap z-50 pointer-events-none"
                                role="tooltip" style="display: none;">Skills<div
                                    class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-[#0A0620] rotate-45">
                                </div>
                            </div>
                        </a>
                    </li>

                    <!-- Support Cards -->
                    <li>
                        <a href="{{ route('support-cards.index') }}" x-data="{ showTooltip: false, hovered: false }"
                            @mouseenter="hovered = true; if ($store.sidebar.minimized) showTooltip = true"
                            @mouseleave="hovered = false; showTooltip = false"
                            class="relative group flex rounded-md p-2 text-sm font-semibold leading-6 transition-all duration-200"
                            :style="hovered && !{{ request()->routeIs('support-cards.*') ? 'true' : 'false' }} ?
                                'background-color: #3B1F6E; color: #FFFFFF;' : ''"
                            style="{{ request()->routeIs('support-cards.*') ? 'background: linear-gradient(135deg, #E879A0, #7C3AED); color: #FFFFFF;' : 'color: #7C6FAB;' }}"
                            :class="$store.sidebar.minimized ? 'justify-center' : 'gap-x-3'"
                            :aria-current="request()->routeIs('support-cards.*') ? 'page' : null"
                            data-testid="sidebar-nav-support-cards">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3" />
                            </svg>
                            <span x-show="!$store.sidebar.minimized" class="whitespace-nowrap">Support Cards</span>
                            <div x-show="showTooltip" x-transition:enter="transition ease-out duration-150 delay-200"
                                x-transition:enter-start="opacity-0 translate-x-2"
                                x-transition:enter-end="opacity-100 translate-x-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-x-0"
                                x-transition:leave-end="opacity-0 translate-x-2"
                                class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-3 py-2 text-sm font-medium text-white bg-[#0A0620] rounded-lg shadow-lg whitespace-nowrap z-50 pointer-events-none"
                                role="tooltip" style="display: none;">Support Cards<div
                                    class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-[#0A0620] rotate-45">
                                </div>
                            </div>
                        </a>
                    </li>

                    <!-- AI Advisor -->
                    <li>
                        <a href="{{ route('ai.chat') }}" x-data="{ showTooltip: false, hovered: false }"
                            @mouseenter="hovered = true; if ($store.sidebar.minimized) showTooltip = true"
                            @mouseleave="hovered = false; showTooltip = false"
                            class="relative group flex rounded-md p-2 text-sm font-semibold leading-6 transition-all duration-200"
                            :style="hovered && !{{ request()->routeIs('ai.*') ? 'true' : 'false' }} ?
                                'background-color: #3B1F6E; color: #FFFFFF;' : ''"
                            style="{{ request()->routeIs('ai.*') ? 'background: linear-gradient(135deg, #E879A0, #7C3AED); color: #FFFFFF;' : 'color: #7C6FAB;' }}"
                            :class="$store.sidebar.minimized ? 'justify-center' : 'gap-x-3'"
                            :aria-current="request()->routeIs('ai.*') ? 'page' : null"
                            data-testid="sidebar-nav-ai-advisor">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9.348 14.652a3.75 3.75 0 0 1 0-5.304m5.304 0a3.75 3.75 0 0 1 0 5.304m-7.425 2.121a6.75 6.75 0 0 1 0-9.546m9.546 0a6.75 6.75 0 0 1 0 9.546M5.106 18.894c-3.808-3.808-3.808-9.98 0-13.789m13.788 0c3.808 3.809 3.808 9.981 0 13.789M12 12a3 3 0 1 1 0-6 3 3 0 0 1 0 6Z" />
                            </svg>
                            <span x-show="!$store.sidebar.minimized" class="whitespace-nowrap">AI Advisor</span>
                            <div x-show="showTooltip" x-transition:enter="transition ease-out duration-150 delay-200"
                                x-transition:enter-start="opacity-0 translate-x-2"
                                x-transition:enter-end="opacity-100 translate-x-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-x-0"
                                x-transition:leave-end="opacity-0 translate-x-2"
                                class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-3 py-2 text-sm font-medium text-white bg-[#0A0620] rounded-lg shadow-lg whitespace-nowrap z-50 pointer-events-none"
                                role="tooltip" style="display: none;">AI Advisor<div
                                    class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-[#0A0620] rotate-45">
                                </div>
                            </div>
                        </a>
                    </li>

                    <!-- Achievements -->
                    <li>
                        <a href="{{ route('achievements.index') }}" x-data="{ showTooltip: false, hovered: false }"
                            @mouseenter="hovered = true; if ($store.sidebar.minimized) showTooltip = true"
                            @mouseleave="hovered = false; showTooltip = false"
                            class="relative group flex rounded-md p-2 text-sm font-semibold leading-6 transition-all duration-200"
                            :style="hovered && !{{ request()->routeIs('achievements.*') ? 'true' : 'false' }} ?
                                'background-color: #3B1F6E; color: #FFFFFF;' : ''"
                            style="{{ request()->routeIs('achievements.*') ? 'background: linear-gradient(135deg, #E879A0, #7C3AED); color: #FFFFFF;' : 'color: #7C6FAB;' }}"
                            :class="$store.sidebar.minimized ? 'justify-center' : 'gap-x-3'"
                            :aria-current="request()->routeIs('achievements.*') ? 'page' : null"
                            data-testid="sidebar-nav-achievements">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0" />
                            </svg>
                            <span x-show="!$store.sidebar.minimized" class="whitespace-nowrap">Achievements</span>
                            <div x-show="showTooltip" x-transition:enter="transition ease-out duration-150 delay-200"
                                x-transition:enter-start="opacity-0 translate-x-2"
                                x-transition:enter-end="opacity-100 translate-x-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-x-0"
                                x-transition:leave-end="opacity-0 translate-x-2"
                                class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-3 py-2 text-sm font-medium text-white bg-[#0A0620] rounded-lg shadow-lg whitespace-nowrap z-50 pointer-events-none"
                                role="tooltip" style="display: none;">Achievements<div
                                    class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-[#0A0620] rotate-45">
                                </div>
                            </div>
                        </a>
                    </li>

                    <!-- Settings -->
                    <li>
                        <a href="{{ route('settings.index') }}" x-data="{ showTooltip: false, hovered: false }"
                            @mouseenter="hovered = true; if ($store.sidebar.minimized) showTooltip = true"
                            @mouseleave="hovered = false; showTooltip = false"
                            class="relative group flex rounded-md p-2 text-sm font-semibold leading-6 transition-all duration-200"
                            :style="hovered && !{{ request()->routeIs('settings.*') ? 'true' : 'false' }} ?
                                'background-color: #3B1F6E; color: #FFFFFF;' : ''"
                            style="{{ request()->routeIs('settings.*') ? 'background: linear-gradient(135deg, #E879A0, #7C3AED); color: #FFFFFF;' : 'color: #7C6FAB;' }}"
                            :class="$store.sidebar.minimized ? 'justify-center' : 'gap-x-3'"
                            :aria-current="request()->routeIs('settings.*') ? 'page' : null"
                            data-testid="sidebar-nav-settings">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.592c.55 0 1.02.398 1.11.94a6.047 6.047 0 0 1 1.366 1.466c.247.382.645.643 1.11.643.465 0 .863-.261 1.11-.643a6.047 6.047 0 0 1 1.366-1.466c.09-.542.56-.94 1.11-.94h2.592c.55 0 1.02.398 1.11.94a6.065 6.065 0 0 1 1.378 1.54 6.005 6.005 0 0 1 1.07 1.678c.11.271.033.715-.175.975-.205.26-.565.529-.975.529-.41 0-.77-.269-.975-.529-.24-.31-.645-.643-1.228-.643-.583 0-.988.333-1.228.643-.205.26-.565.529-.975.529-.41 0-.77-.269-.975-.529-.18-.26-.259-.704-.175-.975a6.001 6.001 0 0 1 1.07-1.678 6.062 6.062 0 0 1 1.378-1.54 6.065 6.065 0 0 0-1.378-1.54c-.09-.542-.56-.94-1.11-.94h-2.592c-.55 0-1.02.398-1.11.94a6.065 6.065 0 0 0-1.366 1.466 6.004 6.004 0 0 1-1.07 1.678c-.11.271-.033.715.175.975.205.26.565.529.975.529.41 0 .77-.269.975-.529.24-.31.645-.643 1.228-.643.583 0 .988.333 1.228.643.205.26.565.529.975.529.41 0 .77-.269.975-.529.18-.26.259-.704.175-.975a6.001 6.001 0 0 0-1.07-1.678 6.062 6.062 0 0 0-1.378-1.54ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
                            </svg>
                            <span x-show="!$store.sidebar.minimized" class="whitespace-nowrap">Settings</span>
                            <div x-show="showTooltip" x-transition:enter="transition ease-out duration-150 delay-200"
                                x-transition:enter-start="opacity-0 translate-x-2"
                                x-transition:enter-end="opacity-100 translate-x-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-x-0"
                                x-transition:leave-end="opacity-0 translate-x-2"
                                class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-3 py-2 text-sm font-medium text-white bg-[#0A0620] rounded-lg shadow-lg whitespace-nowrap z-50 pointer-events-none"
                                role="tooltip" style="display: none;">Settings<div
                                    class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-[#0A0620] rotate-45">
                                </div>
                            </div>
                        </a>
                    </li>

                    <!-- Divider -->
                    <li class="border-t" style="border-color: #1E1033;" my-2"></li>

                    <!-- Secondary Navigation (Collapsible Groups - Available to All Users) -->

                    <!-- Data Management Group -->
                    <li>
                        <button x-data="{ showTooltip: false, hovered: false }"
                            @click="if ($store.sidebar.minimized) { $store.sidebar.expand() } else { dataOpen = !dataOpen }"
                            @mouseenter="hovered = true; if ($store.sidebar.minimized) showTooltip = true"
                            @mouseleave="hovered = false; showTooltip = false" type="button"
                            aria-label="Toggle Data Management menu"
                            class="relative group flex w-full items-center rounded-md p-2 text-sm font-semibold leading-6 transition-all duration-200"
                            :style="hovered ? 'background-color: #3B1F6E; color: #FFFFFF;' : 'color: #7C6FAB;'"
                            :class="$store.sidebar.minimized ? 'justify-center' : 'gap-x-3'">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                            </svg>
                            <span x-show="!$store.sidebar.minimized" class="flex-1 text-left whitespace-nowrap">Data
                                Management</span>
                            <svg x-show="!$store.sidebar.minimized" class="h-5 w-5 shrink-0 transition-transform"
                                :class="dataOpen ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                            <div x-show="showTooltip" x-transition:enter="transition ease-out duration-150 delay-200"
                                x-transition:enter-start="opacity-0 translate-x-2"
                                x-transition:enter-end="opacity-100 translate-x-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-x-0"
                                x-transition:leave-end="opacity-0 translate-x-2"
                                class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-3 py-2 text-sm font-medium text-white bg-[#0A0620]  rounded-lg shadow-lg whitespace-nowrap z-50 pointer-events-none"
                                role="tooltip" style="display: none;">Data Management<div
                                    class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-[#0A0620]  rotate-45">
                                </div>
                            </div>
                        </button>
                        <ul x-show="dataOpen && !$store.sidebar.minimized" x-collapse class="mt-1 space-y-1 pl-11">
                            <li>
                                <a href="{{ route('data-management.index') }}"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 transition-all duration-200"
                                    x-data="{ subHover: false }" @mouseenter="subHover = true"
                                    @mouseleave="subHover = false"
                                    :style="subHover && !{{ request()->routeIs('data-management.*') ? 'true' : 'false' }} ?
                                        'color: #FFFFFF; background-color: rgba(59, 31, 110, 0.3);' : ''"
                                    style="{{ request()->routeIs('data-management.*') ? 'color: #E879A0; font-weight: 800;' : 'color: #7C6FAB;' }}">
                                    Data Hub
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('import.index') }}"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 transition-all duration-200"
                                    x-data="{ subHover: false }" @mouseenter="subHover = true"
                                    @mouseleave="subHover = false"
                                    :style="subHover && !{{ request()->routeIs('import.*') ? 'true' : 'false' }} ?
                                        'color: #FFFFFF; background-color: rgba(59, 31, 110, 0.3);' : ''"
                                    style="{{ request()->routeIs('import.*') ? 'color: #E879A0; font-weight: 800;' : 'color: #7C6FAB;' }}">
                                    Import Data
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('export.index') }}"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 transition-all duration-200"
                                    x-data="{ subHover: false }" @mouseenter="subHover = true"
                                    @mouseleave="subHover = false"
                                    :style="subHover && !{{ request()->routeIs('export.*') ? 'true' : 'false' }} ?
                                        'color: #FFFFFF; background-color: rgba(59, 31, 110, 0.3);' : ''"
                                    style="{{ request()->routeIs('export.*') ? 'color: #E879A0; font-weight: 800;' : 'color: #7C6FAB;' }}">
                                    Export Data
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('migration.index') }}"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 transition-all duration-200"
                                    x-data="{ subHover: false }" @mouseenter="subHover = true"
                                    @mouseleave="subHover = false"
                                    :style="subHover && !{{ request()->routeIs('migration.*') ? 'true' : 'false' }} ?
                                        'color: #FFFFFF; background-color: rgba(59, 31, 110, 0.3);' : ''"
                                    style="{{ request()->routeIs('migration.*') ? 'color: #E879A0; font-weight: 800;' : 'color: #7C6FAB;' }}">
                                    Migration
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('backup.index') }}"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 transition-all duration-200"
                                    x-data="{ subHover: false }" @mouseenter="subHover = true"
                                    @mouseleave="subHover = false"
                                    :style="subHover && !{{ request()->routeIs('backup.*') ? 'true' : 'false' }} ?
                                        'color: #FFFFFF; background-color: rgba(59, 31, 110, 0.3);' : ''"
                                    style="{{ request()->routeIs('backup.*') ? 'color: #E879A0; font-weight: 800;' : 'color: #7C6FAB;' }}">
                                    Backup & Restore
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Analytics & Reports Group -->
                    <li>
                        <button x-data="{ showTooltip: false, hovered: false }"
                            @click="if ($store.sidebar.minimized) { $store.sidebar.expand() } else { analyticsOpen = !analyticsOpen }"
                            @mouseenter="hovered = true; if ($store.sidebar.minimized) showTooltip = true"
                            @mouseleave="hovered = false; showTooltip = false" type="button"
                            aria-label="Toggle Analytics & Reports menu"
                            class="relative group flex w-full items-center rounded-md p-2 text-sm font-semibold leading-6 transition-all duration-200"
                            :style="hovered ? 'background-color: #3B1F6E; color: #FFFFFF;' : 'color: #7C6FAB;'"
                            :class="$store.sidebar.minimized ? 'justify-center' : 'gap-x-3'">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                            </svg>
                            <span x-show="!$store.sidebar.minimized"
                                class="flex-1 text-left whitespace-nowrap">Analytics & Reports</span>
                            <svg x-show="!$store.sidebar.minimized" class="h-5 w-5 shrink-0 transition-transform"
                                :class="analyticsOpen ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                            <div x-show="showTooltip" x-transition:enter="transition ease-out duration-150 delay-200"
                                x-transition:enter-start="opacity-0 translate-x-2"
                                x-transition:enter-end="opacity-100 translate-x-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-x-0"
                                x-transition:leave-end="opacity-0 translate-x-2"
                                class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-3 py-2 text-sm font-medium text-white bg-[#0A0620]  rounded-lg shadow-lg whitespace-nowrap z-50 pointer-events-none"
                                role="tooltip" style="display: none;">Analytics &amp; Reports<div
                                    class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-[#0A0620]  rotate-45">
                                </div>
                            </div>
                        </button>
                        <ul x-show="analyticsOpen && !$store.sidebar.minimized" x-collapse
                            class="mt-1 space-y-1 pl-11">
                            <li>
                                <a href="{{ route('reports.index') }}"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 transition-all duration-200"
                                    x-data="{ subHover: false }" @mouseenter="subHover = true"
                                    @mouseleave="subHover = false"
                                    :style="subHover && !{{ request()->routeIs('reports.*') ? 'true' : 'false' }} ?
                                        'color: #FFFFFF; background-color: rgba(59, 31, 110, 0.3);' : ''"
                                    style="{{ request()->routeIs('reports.*') ? 'color: #E879A0; font-weight: 800;' : 'color: #7C6FAB;' }}">
                                    Career Reports
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('historical.index') }}"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 transition-all duration-200"
                                    x-data="{ subHover: false }" @mouseenter="subHover = true"
                                    @mouseleave="subHover = false"
                                    :style="subHover && !{{ request()->routeIs('historical.*') ? 'true' : 'false' }} ?
                                        'color: #FFFFFF; background-color: rgba(59, 31, 110, 0.3);' : ''"
                                    style="{{ request()->routeIs('historical.*') ? 'color: #E879A0; font-weight: 800;' : 'color: #7C6FAB;' }}">
                                    Historical Tracking
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('analytics.patterns') }}"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 transition-all duration-200"
                                    x-data="{ subHover: false }" @mouseenter="subHover = true"
                                    @mouseleave="subHover = false"
                                    :style="subHover && !{{ request()->routeIs('analytics.*') ? 'true' : 'false' }} ?
                                        'color: #FFFFFF; background-color: rgba(59, 31, 110, 0.3);' : ''"
                                    style="{{ request()->routeIs('analytics.*') ? 'color: #E879A0; font-weight: 800;' : 'color: #7C6FAB;' }}">
                                    Pattern Analytics
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('simulation.index') }}"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 transition-all duration-200"
                                    x-data="{ subHover: false }" @mouseenter="subHover = true"
                                    @mouseleave="subHover = false"
                                    :style="subHover && !{{ request()->routeIs('simulation.*') ? 'true' : 'false' }} ?
                                        'color: #FFFFFF; background-color: rgba(59, 31, 110, 0.3);' : ''"
                                    style="{{ request()->routeIs('simulation.*') ? 'color: #E879A0; font-weight: 800;' : 'color: #7C6FAB;' }}">
                                    Simulation
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('performance.apm.dashboard') }}"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 transition-all duration-200"
                                    x-data="{ subHover: false }" @mouseenter="subHover = true"
                                    @mouseleave="subHover = false"
                                    :style="subHover && !{{ request()->routeIs('performance.*') ? 'true' : 'false' }} ?
                                        'color: #FFFFFF; background-color: rgba(59, 31, 110, 0.3);' : ''"
                                    style="{{ request()->routeIs('performance.*') ? 'color: #E879A0; font-weight: 800;' : 'color: #7C6FAB;' }}">
                                    Performance APM
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- AI & Tools Group -->
                    <li>
                        <button x-data="{ showTooltip: false, hovered: false }"
                            @click="if ($store.sidebar.minimized) { $store.sidebar.expand() } else { aiOpen = !aiOpen }"
                            @mouseenter="hovered = true; if ($store.sidebar.minimized) showTooltip = true"
                            @mouseleave="hovered = false; showTooltip = false" type="button"
                            aria-label="Toggle AI & Tools menu"
                            class="relative group flex w-full items-center rounded-md p-2 text-sm font-semibold leading-6 transition-all duration-200"
                            :style="hovered ? 'background-color: #3B1F6E; color: #FFFFFF;' : 'color: #7C6FAB;'"
                            :class="$store.sidebar.minimized ? 'justify-center' : 'gap-x-3'">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                            </svg>
                            <span x-show="!$store.sidebar.minimized" class="flex-1 text-left whitespace-nowrap">AI &
                                Tools</span>
                            <svg x-show="!$store.sidebar.minimized" class="h-5 w-5 shrink-0 transition-transform"
                                :class="aiOpen ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                            <div x-show="showTooltip" x-transition:enter="transition ease-out duration-150 delay-200"
                                x-transition:enter-start="opacity-0 translate-x-2"
                                x-transition:enter-end="opacity-100 translate-x-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-x-0"
                                x-transition:leave-end="opacity-0 translate-x-2"
                                class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-3 py-2 text-sm font-medium text-white bg-[#0A0620]  rounded-lg shadow-lg whitespace-nowrap z-50 pointer-events-none"
                                role="tooltip" style="display: none;">AI &amp; Tools<div
                                    class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-[#0A0620]  rotate-45">
                                </div>
                            </div>
                        </button>
                        <ul x-show="aiOpen && !$store.sidebar.minimized" x-collapse class="mt-1 space-y-1 pl-11">
                            <li>
                                <a href="{{ route('ai.dashboard') }}"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 transition-all duration-200"
                                    x-data="{ subHover: false }" @mouseenter="subHover = true"
                                    @mouseleave="subHover = false"
                                    :style="subHover && !{{ request()->routeIs('ai.dashboard') ? 'true' : 'false' }} ?
                                        'color: #FFFFFF; background-color: rgba(59, 31, 110, 0.3);' : ''"
                                    style="{{ request()->routeIs('ai.dashboard') ? 'color: #E879A0; font-weight: 800;' : 'color: #7C6FAB;' }}">
                                    AI Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('ai.chat') }}"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 transition-all duration-200"
                                    x-data="{ subHover: false }" @mouseenter="subHover = true"
                                    @mouseleave="subHover = false"
                                    :style="subHover && !{{ request()->routeIs('ai.chat') ? 'true' : 'false' }} ?
                                        'color: #FFFFFF; background-color: rgba(59, 31, 110, 0.3);' : ''"
                                    style="{{ request()->routeIs('ai.chat') ? 'color: #E879A0; font-weight: 800;' : 'color: #7C6FAB;' }}">
                                    AI Chat
                                </a>
                            </li>
                            <li>
                                <a href="{{ $careerPlanRoute }}"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 transition-all duration-200"
                                    x-data="{ subHover: false }" @mouseenter="subHover = true"
                                    @mouseleave="subHover = false"
                                    :style="subHover && !{{ request()->routeIs('characters.show') ? 'true' : 'false' }} ?
                                        'color: #FFFFFF; background-color: rgba(59, 31, 110, 0.3);' : ''"
                                    style="{{ request()->routeIs('characters.show') ? 'color: #E879A0; font-weight: 800;' : 'color: #7C6FAB;' }}">
                                    AI Career Plan
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('mcp.dashboard') }}"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 transition-all duration-200"
                                    x-data="{ subHover: false }" @mouseenter="subHover = true"
                                    @mouseleave="subHover = false"
                                    :style="subHover && !{{ request()->routeIs('mcp.dashboard') ? 'true' : 'false' }} ?
                                        'color: #FFFFFF; background-color: rgba(59, 31, 110, 0.3);' : ''"
                                    style="{{ request()->routeIs('mcp.dashboard') ? 'color: #E879A0; font-weight: 800;' : 'color: #7C6FAB;' }}">
                                    MCP Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('ocr.upload') }}"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 transition-all duration-200"
                                    x-data="{ subHover: false }" @mouseenter="subHover = true"
                                    @mouseleave="subHover = false"
                                    :style="subHover && !{{ request()->routeIs('ocr.*') ? 'true' : 'false' }} ?
                                        'color: #FFFFFF; background-color: rgba(59, 31, 110, 0.3);' : ''"
                                    style="{{ request()->routeIs('ocr.*') ? 'color: #E879A0; font-weight: 800;' : 'color: #7C6FAB;' }}">
                                    OCR Upload
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- External Resources Group -->
                    <li>
                        <button x-data="{ showTooltip: false, hovered: false }"
                            @click="if ($store.sidebar.minimized) { $store.sidebar.expand() } else { toolsOpen = !toolsOpen }"
                            @mouseenter="hovered = true; if ($store.sidebar.minimized) showTooltip = true"
                            @mouseleave="hovered = false; showTooltip = false" type="button"
                            aria-label="Toggle External Resources menu"
                            class="relative group flex w-full items-center rounded-md p-2 text-sm font-semibold leading-6 transition-all duration-200"
                            :style="hovered ? 'background-color: #3B1F6E; color: #FFFFFF;' : 'color: #7C6FAB;'"
                            :class="$store.sidebar.minimized ? 'justify-center' : 'gap-x-3'">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                            <span x-show="!$store.sidebar.minimized"
                                class="flex-1 text-left whitespace-nowrap">External Resources</span>
                            <svg x-show="!$store.sidebar.minimized" class="h-5 w-5 shrink-0 transition-transform"
                                :class="toolsOpen ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                            <div x-show="showTooltip" x-transition:enter="transition ease-out duration-150 delay-200"
                                x-transition:enter-start="opacity-0 translate-x-2"
                                x-transition:enter-end="opacity-100 translate-x-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-x-0"
                                x-transition:leave-end="opacity-0 translate-x-2"
                                class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-3 py-2 text-sm font-medium text-white bg-[#0A0620]  rounded-lg shadow-lg whitespace-nowrap z-50 pointer-events-none"
                                role="tooltip" style="display: none;">External Resources<div
                                    class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-[#0A0620]  rotate-45">
                                </div>
                            </div>
                        </button>
                        <ul x-show="toolsOpen && !$store.sidebar.minimized" x-collapse class="mt-1 space-y-1 pl-11">
                            <li>
                                <a href="{{ route('external-data.browse') }}"
                                    class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 transition-all duration-200"
                                    x-data="{ subHover: false }" @mouseenter="subHover = true"
                                    @mouseleave="subHover = false"
                                    :style="subHover && !{{ request()->routeIs('external-data.*') ? 'true' : 'false' }} ?
                                        'color: #FFFFFF; background-color: rgba(59, 31, 110, 0.3);' : ''"
                                    style="{{ request()->routeIs('external-data.*') ? 'color: #E879A0; font-weight: 800;' : 'color: #7C6FAB;' }}">
                                    Browse External Data
                                </a>
                            </li>
                        </ul>
                    </li>

                    @auth
                        @if (auth()->user()->isAdmin())
                            <!-- Admin Divider -->
                            <li class="border-t-2" style="border-color: #3B1F6E;" my-3"></li>

                            <!-- Admin Panel (Only visible to admin users) -->
                            <li>
                                <button x-data="{ showTooltip: false, hovered: false }"
                                    @click="if ($store.sidebar.minimized) { $store.sidebar.expand() } else { adminOpen = !adminOpen }"
                                    @mouseenter="hovered = true; if ($store.sidebar.minimized) showTooltip = true"
                                    @mouseleave="hovered = false; showTooltip = false" type="button"
                                    aria-label="Toggle Admin Panel menu"
                                    class="relative group flex w-full items-center rounded-md p-2 text-sm font-semibold leading-6 transition-all duration-200"
                                    :style="hovered ? 'background-color: #3B1F6E; color: #FFFFFF;' : 'color: #7C6FAB;'"
                                    :class="$store.sidebar.minimized ? 'justify-center' : 'gap-x-3'">
                                    <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                    </svg>
                                    <span x-show="!$store.sidebar.minimized"
                                        class="flex-1 text-left whitespace-nowrap">Admin Panel</span>
                                    <svg x-show="!$store.sidebar.minimized" class="h-5 w-5 shrink-0 transition-transform"
                                        :class="adminOpen ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                    </svg>
                                    <div x-show="showTooltip"
                                        x-transition:enter="transition ease-out duration-150 delay-200"
                                        x-transition:enter-start="opacity-0 translate-x-2"
                                        x-transition:enter-end="opacity-100 translate-x-0"
                                        x-transition:leave="transition ease-in duration-100"
                                        x-transition:leave-start="opacity-100 translate-x-0"
                                        x-transition:leave-end="opacity-0 translate-x-2"
                                        class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-3 py-2 text-sm font-medium text-amber-100 bg-[#0A0620]  rounded-lg shadow-lg whitespace-nowrap z-50 pointer-events-none"
                                        role="tooltip" style="display: none;">Admin Panel<div
                                            class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-[#0A0620]  rotate-45">
                                        </div>
                                    </div>
                                </button>
                                <ul x-show="adminOpen && !$store.sidebar.minimized" x-collapse
                                    class="mt-1 space-y-1 pl-11 bg-amber-50/50 dark:bg-[#0A0620]/10 rounded-md py-2">
                                    <!-- System Administration -->
                                    <li class="px-2 py-1 text-xs font-semibold " style="color: #F59E0B;" uppercase
                                        tracking-wider">
                                        System Administration
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.users.index') }}"
                                            class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 transition-all duration-200"
                                            x-data="{ subHover: false }" @mouseenter="subHover = true"
                                            @mouseleave="subHover = false"
                                            :style="subHover ? 'color: #FFFFFF; background-color: rgba(59, 31, 110, 0.3);' :
                                                'color: #7C6FAB;'">
                                            User Management
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.system-settings.index') }}"
                                            class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 transition-all duration-200"
                                            x-data="{ subHover: false }" @mouseenter="subHover = true"
                                            @mouseleave="subHover = false"
                                            :style="subHover ? 'color: #FFFFFF; background-color: rgba(59, 31, 110, 0.3);' :
                                                'color: #7C6FAB;'">
                                            System Settings
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.logs.index') }}"
                                            class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 transition-all duration-200"
                                            x-data="{ subHover: false }" @mouseenter="subHover = true"
                                            @mouseleave="subHover = false"
                                            :style="subHover ? 'color: #FFFFFF; background-color: rgba(59, 31, 110, 0.3);' :
                                                'color: #7C6FAB;'">
                                            System Logs
                                        </a>
                                    </li>

                                    <!-- Database Management -->
                                    <li class="border-t " style="border-color: #3B1F6E;" my-2"></li>
                                    <li class="px-2 py-1 text-xs font-semibold " style="color: #F59E0B;" uppercase
                                        tracking-wider">
                                        Database Management
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.database.maintenance') }}"
                                            class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 transition-all duration-200"
                                            x-data="{ subHover: false }" @mouseenter="subHover = true"
                                            @mouseleave="subHover = false"
                                            :style="subHover ? 'color: #FFFFFF; background-color: rgba(59, 31, 110, 0.3);' :
                                                'color: #7C6FAB;'">
                                            Database Maintenance
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.database.seeders') }}"
                                            class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 transition-all duration-200"
                                            x-data="{ subHover: false }" @mouseenter="subHover = true"
                                            @mouseleave="subHover = false"
                                            :style="subHover ? 'color: #FFFFFF; background-color: rgba(59, 31, 110, 0.3);' :
                                                'color: #7C6FAB;'">
                                            Run Seeders
                                        </a>
                                    </li>

                                    <!-- Monitoring & Diagnostics -->
                                    <li class="border-t " style="border-color: #3B1F6E;" my-2"></li>
                                    <li class="px-2 py-1 text-xs font-semibold " style="color: #F59E0B;" uppercase
                                        tracking-wider">
                                        Monitoring & Diagnostics
                                    </li>
                                    <li>
                                        <a href="{{ route('performance.apm.dashboard') }}"
                                            class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 transition-all duration-200"
                                            x-data="{ subHover: false }" @mouseenter="subHover = true"
                                            @mouseleave="subHover = false"
                                            :style="subHover && !{{ request()->routeIs('performance.*') ? 'true' : 'false' }} ?
                                                'color: #FFFFFF; background-color: rgba(59, 31, 110, 0.3);' : ''"
                                            style="{{ request()->routeIs('performance.*') ? 'color: #E879A0; font-weight: 800;' : 'color: #7C6FAB;' }}">
                                            Performance Monitor
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.queue.index') }}"
                                            class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 transition-all duration-200"
                                            x-data="{ subHover: false }" @mouseenter="subHover = true"
                                            @mouseleave="subHover = false"
                                            :style="subHover ? 'color: #FFFFFF; background-color: rgba(59, 31, 110, 0.3);' :
                                                'color: #7C6FAB;'">
                                            Queue Monitor
                                        </a>
                                    </li>
                                    <li>
                                        <a href="/telescope" target="_blank" rel="noopener noreferrer"
                                            class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 transition-all duration-200"
                                            x-data="{ subHover: false }" @mouseenter="subHover = true"
                                            @mouseleave="subHover = false"
                                            :style="subHover ? 'color: #FFFFFF; background-color: rgba(59, 31, 110, 0.3);' :
                                                'color: #7C6FAB;'">
                                            Telescope
                                        </a>
                                    </li>
                                    <li>
                                        <a href="/horizon" target="_blank" rel="noopener noreferrer"
                                            class="block rounded-md py-2 pr-2 pl-2 text-sm leading-6 transition-all duration-200"
                                            x-data="{ subHover: false }" @mouseenter="subHover = true"
                                            @mouseleave="subHover = false"
                                            :style="subHover ? 'color: #FFFFFF; background-color: rgba(59, 31, 110, 0.3);' :
                                                'color: #7C6FAB;'">
                                            Horizon
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    @endauth
                </ul>
            </li>

            <!-- Bottom Navigation -->
            <li class="mt-auto">
                <ul class="-mx-2 space-y-1">
                    <!-- Profile -->
                    <li>
                        <a href="{{ route('profile.show') }}" x-data="{ showTooltip: false }"
                            @mouseenter="if ($store.sidebar.minimized) showTooltip = true"
                            @mouseleave="showTooltip = false"
                            class="relative group flex rounded-md p-2 text-sm font-semibold leading-6 transition-all duration-200"
                            style="{{ request()->routeIs('profile.*') ? 'background: rgba(232,121,160,0.18); border-left: 3px solid #E879A0; color: #F9A8D4;' : 'color: rgba(255,255,255,0.55); border-left: 3px solid transparent;' }}"
                            x-data="{ hovered: false }" @mouseenter="hovered = true" @mouseleave="hovered = false"
                            :style="hovered && !{{ request()->routeIs('profile.*') ? 'true' : 'false' }} ?
                                'background-color: #3B1F6E; color: #FFFFFF; border-left: 3px solid transparent;' : ''"
                            :class="$store.sidebar.minimized ? 'justify-center' : 'gap-x-3'">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <span x-show="!$store.sidebar.minimized" class="whitespace-nowrap">Profile</span>
                            <div x-show="showTooltip" x-transition:enter="transition ease-out duration-150 delay-200"
                                x-transition:enter-start="opacity-0 translate-x-2"
                                x-transition:enter-end="opacity-100 translate-x-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-x-0"
                                x-transition:leave-end="opacity-0 translate-x-2"
                                class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-3 py-2 text-sm font-medium text-white bg-[#0A0620]  rounded-lg shadow-lg whitespace-nowrap z-50 pointer-events-none"
                                role="tooltip" style="display: none;">Profile<div
                                    class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-[#0A0620]  rotate-45">
                                </div>
                            </div>
                        </a>
                    </li>
                    <!-- Settings -->
                    <li>
                        <a href="{{ route('settings.index') }}" x-data="{ showTooltip: false }"
                            @mouseenter="if ($store.sidebar.minimized) showTooltip = true"
                            @mouseleave="showTooltip = false"
                            class="relative group flex rounded-md p-2 text-sm font-semibold leading-6 transition-all duration-200"
                            style="{{ request()->routeIs('settings.*') ? 'background: rgba(232,121,160,0.18); border-left: 3px solid #E879A0; color: #F9A8D4;' : 'color: rgba(255,255,255,0.55); border-left: 3px solid transparent;' }}"
                            x-data="{ hovered: false }" @mouseenter="hovered = true" @mouseleave="hovered = false"
                            :style="hovered && !{{ request()->routeIs('settings.*') ? 'true' : 'false' }} ?
                                'background-color: #3B1F6E; color: #FFFFFF; border-left: 3px solid transparent;' : ''"
                            :class="$store.sidebar.minimized ? 'justify-center' : 'gap-x-3'">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <span x-show="!$store.sidebar.minimized" class="whitespace-nowrap">Settings</span>
                            <div x-show="showTooltip" x-transition:enter="transition ease-out duration-150 delay-200"
                                x-transition:enter-start="opacity-0 translate-x-2"
                                x-transition:enter-end="opacity-100 translate-x-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-x-0"
                                x-transition:leave-end="opacity-0 translate-x-2"
                                class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-3 py-2 text-sm font-medium text-white bg-[#0A0620]  rounded-lg shadow-lg whitespace-nowrap z-50 pointer-events-none"
                                role="tooltip" style="display: none;">Settings<div
                                    class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-[#0A0620]  rotate-45">
                                </div>
                            </div>
                        </a>
                    </li>
                    <!-- Design Preview -->
                    <li>
                        <a href="{{ route('design.preview') }}" target="_blank" rel="noopener"
                            x-data="{ showTooltip: false }" @mouseenter="if ($store.sidebar.minimized) showTooltip = true"
                            @mouseleave="showTooltip = false"
                            class="relative group flex rounded-md p-2 text-sm font-semibold leading-6 transition-all duration-200"
                            style="color: rgba(255,255,255,0.55); border-left: 3px solid transparent;"
                            x-data="{ hovered: false }" @mouseenter="hovered = true" @mouseleave="hovered = false"
                            :style="hovered ? 'background-color: #3B1F6E; color: #FFFFFF; border-left: 3px solid transparent;' :
                                ''"
                            :class="$store.sidebar.minimized ? 'justify-center' : 'gap-x-3'">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42" />
                            </svg>
                            <span x-show="!$store.sidebar.minimized" class="whitespace-nowrap">Design Preview</span>
                            <div x-show="showTooltip" x-transition:enter="transition ease-out duration-150 delay-200"
                                x-transition:enter-start="opacity-0 translate-x-2"
                                x-transition:enter-end="opacity-100 translate-x-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-x-0"
                                x-transition:leave-end="opacity-0 translate-x-2"
                                class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-3 py-2 text-sm font-medium text-white bg-[#0A0620]  rounded-lg shadow-lg whitespace-nowrap z-50 pointer-events-none"
                                role="tooltip" style="display: none;">Design Preview<div
                                    class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-[#0A0620]  rotate-45">
                                </div>
                            </div>
                        </a>
                    </li>

                    <!-- Help -->
                    <li>
                        <a href="{{ route('help.index') }}" x-data="{ showTooltip: false }"
                            @mouseenter="if ($store.sidebar.minimized) showTooltip = true"
                            @mouseleave="showTooltip = false"
                            class="relative group flex rounded-md p-2 text-sm font-semibold leading-6 transition-all duration-200"
                            style="{{ request()->routeIs('help.*') ? 'background: rgba(232,121,160,0.18); border-left: 3px solid #E879A0; color: #F9A8D4;' : 'color: rgba(255,255,255,0.55); border-left: 3px solid transparent;' }}"
                            x-data="{ hovered: false }" @mouseenter="hovered = true" @mouseleave="hovered = false"
                            :style="hovered && !{{ request()->routeIs('help.*') ? 'true' : 'false' }} ?
                                'background-color: #3B1F6E; color: #FFFFFF; border-left: 3px solid transparent;' : ''"
                            :class="$store.sidebar.minimized ? 'justify-center' : 'gap-x-3'">
                            <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                            </svg>
                            <span x-show="!$store.sidebar.minimized" class="whitespace-nowrap">Help</span>
                            <div x-show="showTooltip" x-transition:enter="transition ease-out duration-150 delay-200"
                                x-transition:enter-start="opacity-0 translate-x-2"
                                x-transition:enter-end="opacity-100 translate-x-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-x-0"
                                x-transition:leave-end="opacity-0 translate-x-2"
                                class="absolute left-full ml-2 top-1/2 -translate-y-1/2 px-3 py-2 text-sm font-medium text-white bg-[#0A0620]  rounded-lg shadow-lg whitespace-nowrap z-50 pointer-events-none"
                                role="tooltip" style="display: none;">Help<div
                                    class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-[#0A0620]  rotate-45">
                                </div>
                            </div>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
</div>
