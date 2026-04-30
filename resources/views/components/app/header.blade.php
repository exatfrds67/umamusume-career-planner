@php
    // Pull topStatus data passed from controllers (available via $topStatus in layout)
    $topStatus = $topStatus ?? [];
    $currentTurn = $topStatus['currentTurn'] ?? null;
    $maxTurns = $topStatus['maxTurns'] ?? null;
    $spAvailable = $topStatus['spAvailable'] ?? null;
    $storageMode = $topStatus['storageMode'] ?? null;
    $energy = $topStatus['energy'] ?? null;
    $mood = $topStatus['mood'] ?? null;
    $pageTitle = $topStatus['pageTitle'] ?? null;
    $pageSubtitle = $topStatus['pageSubtitle'] ?? null;

    // Derive page title from current route if not explicitly set
    if (!$pageTitle) {
        $routeName = request()->route()?->getName() ?? '';
        $pageTitle = match (true) {
            str_starts_with($routeName, 'dashboard') => 'Dashboard',
            str_starts_with($routeName, 'characters') => 'Characters',
            str_starts_with($routeName, 'training') => 'Training',
            str_starts_with($routeName, 'races') => 'Races',
            str_starts_with($routeName, 'skills') => 'Skills',
            str_starts_with($routeName, 'support-cards') => 'Support Cards',
            str_starts_with($routeName, 'ai') => 'AI Advisor',
            str_starts_with($routeName, 'achievements') => 'Achievements',
            str_starts_with($routeName, 'reports') => 'Reports',
            str_starts_with($routeName, 'settings') => 'Settings',
            str_starts_with($routeName, 'admin') => 'Admin Panel',
            str_starts_with($routeName, 'profile') => 'Profile',
            default => config('app.name', 'Uma Planner'),
        };
    }

    // Build subtitle: stage · turn · scenario
    $subtitleParts = [];
    if ($currentTurn !== null && $maxTurns !== null) {
        $subtitleParts[] = 'Turn ' . $currentTurn . ' / ' . $maxTurns;
    }
    if ($storageMode) {
        $subtitleParts[] = ucfirst((string) $storageMode) . ' Mode';
    }
    $subtitle = $pageSubtitle ?? (count($subtitleParts) ? implode(' · ', $subtitleParts) : null);

    // Energy percentage
    $energyPct = $energy !== null ? min(100, max(0, (int) $energy)) : null;

    // Mood config
    $moodKey = strtolower((string) ($mood ?? 'normal'));
    $moodConfig = match ($moodKey) {
        'great' => ['bg' => '#D1FAE5', 'color' => '#065F46', 'icon' => '✨', 'label' => 'Great'],
        'good' => ['bg' => '#EDE9FE', 'color' => '#5B21B6', 'icon' => '😊', 'label' => 'Good'],
        'bad' => ['bg' => '#FEE2E2', 'color' => '#991B1B', 'icon' => '😟', 'label' => 'Bad'],
        'awful' => ['bg' => '#FEE2E2', 'color' => '#7F1D1D', 'icon' => '😢', 'label' => 'Awful'],
        default => ['bg' => '#F3F4F6', 'color' => '#374151', 'icon' => '😐', 'label' => 'Normal'],
    };

    // User initial for avatar
    $userName = Auth::user()?->name ?? 'User';
    $userInitial = strtoupper(mb_substr($userName, 0, 1));
    $isAdmin = Auth::user()?->isAdmin() ?? false;
@endphp

{{-- ============================================================
     DESIGN SPEC HEADER
     Height: 64px | bg: rgba(255,255,255,0.95) | blur(12px)
     Contents: Page title + subtitle | Energy pill | MoodChip | Bell | Avatar
     ============================================================ --}}
<div class="flex flex-1 items-center justify-between h-full gap-4" style="font-family: 'Nunito', sans-serif;">

    {{-- LEFT: Mobile sidebar toggle + Page title --}}
    <div class="flex items-center gap-3 min-w-0">
        {{-- Mobile hamburger (already rendered in layout, but keep for flex alignment) --}}
        <div class="flex flex-col min-w-0">
            <span
                style="font-size: 18px; font-weight: 900; color: #1E1033; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                {{ $pageTitle }}
            </span>
            @if ($subtitle)
                <span
                    style="font-size: 12px; color: #7C6FAB; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    {{ $subtitle }}
                </span>
            @endif
        </div>
    </div>

    {{-- RIGHT: Energy pill + Mood chip + SP + Notifications + Avatar --}}
    <div class="flex items-center gap-3 shrink-0">

        {{-- SP pill (shown when SP data available) --}}
        @if ($spAvailable !== null)
            <div
                style="display: flex; align-items: center; gap: 5px; background: #FFFBEB; border: 1px solid #FCD34D; border-radius: 20px; padding: 4px 10px;">
                <span style="font-size: 11px; color: #92400E; font-weight: 700;">✨</span>
                <span
                    style="font-size: 12px; font-weight: 800; color: #F59E0B;">{{ number_format((int) $spAvailable) }}</span>
                <span style="font-size: 10px; color: #92400E; font-weight: 600;">SP</span>
            </div>
        @endif

        {{-- Energy pill (shown when energy data available) --}}
        @if ($energyPct !== null)
            <div
                style="display: flex; align-items: center; gap: 6px; background: #F9F5FF; border-radius: 20px; padding: 5px 10px; border: 1px solid #EDE9FE;">
                <span style="font-size: 11px; color: #7C6FAB; font-weight: 700;">⚡</span>
                <div style="width: 60px; height: 6px; background: #EDE9FE; border-radius: 99px; overflow: hidden;">
                    <div
                        style="height: 100%; width: {{ $energyPct }}%; background: linear-gradient(90deg, #E879A0, #7C3AED); border-radius: 99px; transition: width 0.3s ease;">
                    </div>
                </div>
                <span style="font-size: 11px; font-weight: 700; color: #7C3AED;">{{ $energyPct }}%</span>
            </div>
        @endif

        {{-- Mood chip (shown when mood data available) --}}
        @if ($mood !== null)
            <span
                style="display: inline-flex; align-items: center; gap: 4px; border-radius: 20px; padding: 3px 10px; font-size: 12px; font-weight: 700; background: {{ $moodConfig['bg'] }}; color: {{ $moodConfig['color'] }};">
                <span aria-hidden="true">{{ $moodConfig['icon'] }}</span>
                <span>{{ $moodConfig['label'] }}</span>
            </span>
        @endif

        {{-- Notification bell --}}
        <livewire:notification-dropdown />

        {{-- Separator --}}
        <div class="hidden lg:block h-6 w-px" style="background: #EDE9FE;" aria-hidden="true"></div>

        {{-- User avatar + dropdown --}}
        <div x-data="{ open: false }" class="relative z-20" @keydown.escape.window="open = false"
            @popover-opened.window="if ($event.detail !== 'user-menu') open = false">
            <button type="button" class="flex items-center gap-2 rounded-lg px-2 py-1 transition-colors duration-150"
                style="background: transparent;" id="user-menu-btn"
                @click="open = !open; if(open) $dispatch('popover-opened', 'user-menu')" @click.away="open = false"
                aria-haspopup="true" :aria-expanded="open.toString()" data-testid="user-menu-btn">
                <span class="sr-only">Open user menu</span>

                {{-- Avatar circle --}}
                <div
                    style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #E879A0, #7C3AED); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <span
                        style="font-size: 14px; font-weight: 800; color: white; font-family: 'Nunito', sans-serif;">{{ $userInitial }}</span>
                </div>

                {{-- Name (desktop only) --}}
                <span class="hidden lg:block"
                    style="font-size: 13px; font-weight: 700; color: #1E1033; max-width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    {{ $userName }}
                    @if ($isAdmin)
                        <span
                            style="margin-left: 4px; font-size: 10px; font-weight: 700; background: #FEF3C7; color: #92400E; padding: 1px 5px; border-radius: 4px;">Admin</span>
                    @endif
                </span>

                <svg class="hidden lg:block h-4 w-4" style="color: #7C6FAB;" viewBox="0 0 20 20" fill="currentColor"
                    aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"
                        clip-rule="evenodd" />
                </svg>
            </button>

            {{-- Dropdown menu --}}
            <div x-show="open" x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute right-0 mt-2 w-48 origin-top-right rounded-xl shadow-lg focus:outline-none"
                style="background: #fff; border: 1px solid #EDE9FE; box-shadow: 0 8px 24px rgba(124,58,237,0.12); z-index: 50;"
                role="menu" aria-orientation="vertical" aria-labelledby="user-menu-btn">

                {{-- User info header --}}
                <div style="padding: 12px 14px; border-bottom: 1px solid #EDE9FE;">
                    <div style="font-size: 13px; font-weight: 800; color: #1E1033;">{{ $userName }}</div>
                    <div style="font-size: 11px; color: #7C6FAB; margin-top: 2px;">{{ Auth::user()?->email ?? '' }}
                    </div>
                </div>

                <div style="padding: 6px;">
                    <a href="{{ route('profile.show') }}"
                        class="flex items-center gap-2 rounded-lg px-3 py-2 transition-colors duration-150"
                        style="font-size: 13px; font-weight: 600; color: #1E1033; text-decoration: none;"
                        onmouseover="this.style.background='#F9F5FF'" onmouseout="this.style.background='transparent'"
                        role="menuitem">
                        <svg class="w-4 h-4" style="color: #7C6FAB;" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        Your Profile
                    </a>

                    <a href="{{ route('settings.index') }}"
                        class="flex items-center gap-2 rounded-lg px-3 py-2 transition-colors duration-150"
                        style="font-size: 13px; font-weight: 600; color: #1E1033; text-decoration: none;"
                        onmouseover="this.style.background='#F9F5FF'" onmouseout="this.style.background='transparent'"
                        role="menuitem">
                        <svg class="w-4 h-4" style="color: #7C6FAB;" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Settings
                    </a>

                    {{-- Theme toggle --}}
                    <button type="button" id="theme-toggle" x-data="{ isDark: document.documentElement.classList.contains('dark') }"
                        @theme-changed.window="isDark = $event.detail" @click="$dispatch('toggle-theme')"
                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 transition-colors duration-150"
                        style="font-size: 13px; font-weight: 600; color: #1E1033; background: transparent; border: none; cursor: pointer; text-align: left;"
                        onmouseover="this.style.background='#F9F5FF'" onmouseout="this.style.background='transparent'"
                        :aria-pressed="isDark.toString()" role="menuitem">
                        <svg x-show="!isDark" class="w-4 h-4" style="color: #7C6FAB;" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                        </svg>
                        <svg x-show="isDark" class="w-4 h-4" style="color: #7C6FAB;" fill="none"
                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                        </svg>
                        <span x-text="isDark ? 'Light Mode' : 'Dark Mode'"></span>
                    </button>

                    <div style="height: 1px; background: #EDE9FE; margin: 4px 0;"></div>

                    <form method="POST" action="{{ route('logout') }}" id="logout-form">
                        @csrf
                        <button type="submit" id="logout-btn"
                            class="flex w-full items-center gap-2 rounded-lg px-3 py-2 transition-colors duration-150"
                            style="font-size: 13px; font-weight: 600; color: #EF4444; background: transparent; border: none; cursor: pointer; text-align: left;"
                            onmouseover="this.style.background='#FEF2F2'"
                            onmouseout="this.style.background='transparent'" role="menuitem">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                            </svg>
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
