@extends('layouts.app')

@section('title', $character->name)

@section('content')
    @php
        /** @var \App\Models\Character $character */
        $statColors = [
            'speed' => '#E879A0',
            'stamina' => '#10B981',
            'power' => '#F59E0B',
            'guts' => '#EF4444',
            'wit' => '#3B82F6',
        ];
        $statIcons = [
            'speed' => '⚡',
            'stamina' => '🌿',
            'power' => '🔥',
            'guts' => '❤️',
            'wit' => '💙',
        ];
        $statGrade = function (int $v): string {
            if ($v >= 1000) {
                return 'S';
            }
            if ($v >= 800) {
                return 'A';
            }
            if ($v >= 600) {
                return 'B';
            }
            if ($v >= 400) {
                return 'C';
            }
            if ($v >= 200) {
                return 'D';
            }
            if ($v >= 100) {
                return 'E';
            }
            return 'F';
        };
        $gradeColors = [
            'S' => '#F59E0B',
            'A' => '#E879A0',
            'B' => '#7C3AED',
            'C' => '#3B82F6',
            'D' => '#6B7280',
            'E' => '#9CA3AF',
            'F' => '#D1D5DB',
        ];
        $tabs = ['Stats', 'Aptitudes', 'Factors', 'Goals', 'History', 'Snapshots'];
    @endphp

    <div class="max-w-5xl mx-auto space-y-6 pb-10" x-data="{ activeTab: 'Stats' }">

        {{-- Back + header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('characters.index') }}"
                    class="flex items-center gap-1.5 rounded-xl border border-[#EDE9FE] bg-[#EDE9FE] px-3 py-2 text-sm font-bold text-[#7C3AED] transition hover:bg-[#DDD6FE]"
                    aria-label="Back to characters list">
                    ← Back
                </a>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-[22px] font-black text-[#1E1033] leading-tight">{{ $character->name }}</h1>
                        <span class="rounded-lg px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wider text-white"
                            style="background: linear-gradient(135deg,#F59E0B,#F97316)">SSR</span>
                    </div>
                    <p class="mt-0.5 text-sm text-[#7C6FAB]">
                        {{ ucwords(str_replace('_', ' ', $character->scenario_type ?? '')) }}
                        · Turn {{ $character->current_turn }}/{{ $character->getMaxTurns() }}
                        · <span
                            class="{{ match ($character->career_stage) {'junior' => 'text-[#3B82F6]','classic' => 'text-[#7C3AED]','senior' => 'text-[#E879A0]',default => 'text-[#7C6FAB]'} }} font-semibold">{{ ucfirst($character->career_stage) }}</span>
                    </p>
                </div>
            </div>

            {{-- Quick actions --}}
            <div class="flex flex-wrap items-center gap-2">
                <form action="{{ route('characters.toggle-pin', $character) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="rounded-xl border border-[#EDE9FE] px-3 py-2 text-sm font-bold transition {{ $character->isPinnedBy(Auth::id()) ? 'bg-[#E879A0] text-white' : 'bg-white text-[#7C6FAB] hover:bg-[#F9F5FF]' }}"
                        aria-label="{{ $character->isPinnedBy(Auth::id()) ? 'Unpin' : 'Pin' }} {{ $character->name }}">
                        {{ $character->isPinnedBy(Auth::id()) ? '📌 Pinned' : '📌 Pin' }}
                    </button>
                </form>
                <a href="{{ route('characters.edit', $character) }}"
                    class="rounded-xl bg-linear-to-r from-[#E879A0] to-[#7C3AED] px-4 py-2 text-sm font-bold text-white shadow-[0_4px_12px_rgba(232,121,160,.35)] transition hover:brightness-105"
                    aria-label="Edit {{ $character->name }}">
                    Edit
                </a>
            </div>
        </div>

        {{-- Character overview card --}}
        <div
            class="relative overflow-hidden rounded-[20px] bg-linear-to-r from-[#1E1033] via-[#3B1F6E] to-[#4C1060] p-6 text-white shadow-[0_8px_40px_rgba(124,58,237,0.18)]">
            {{-- Decorative orbs --}}
            <div class="pointer-events-none absolute -right-8 -top-8 h-48 w-48 rounded-full bg-[#E879A0]/12"
                aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-8 -left-8 h-36 w-36 rounded-full bg-[#7C3AED]/10"
                aria-hidden="true"></div>

            <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                {{-- Character avatar --}}
                @if ($character->avatar_url)
                    <img src="{{ $character->avatar_url }}" alt="{{ $character->name }}"
                        class="hidden sm:block h-28 w-20 rounded-xl object-cover shrink-0 shadow-lg" loading="lazy"
                        onerror="this.style.display='none'" />
                @endif

                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-widest text-white/50">🏇 Active Career Run</p>
                    <p class="text-2xl font-black tracking-tight">{{ $character->name }}</p>
                    @php
                        $moodStyles = [
                            'great' => ['bg' => '#D1FAE5', 'color' => '#065F46', 'icon' => '✨'],
                            'good' => ['bg' => '#EDE9FE', 'color' => '#5B21B6', 'icon' => '😊'],
                            'normal' => ['bg' => '#F3F4F6', 'color' => '#374151', 'icon' => '😐'],
                            'bad' => ['bg' => '#FEE2E2', 'color' => '#991B1B', 'icon' => '😟'],
                            'awful' => ['bg' => '#FEE2E2', 'color' => '#7F1D1D', 'icon' => '😫'],
                        ];
                        $moodStyle = $moodStyles[$character->mood_status ?? 'normal'] ?? $moodStyles['normal'];
                    @endphp
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-bold"
                            style="background: {{ $moodStyle['bg'] }}; color: {{ $moodStyle['color'] }}">
                            {{ $moodStyle['icon'] }} {{ ucfirst($character->mood_status ?? 'normal') }}
                        </span>
                        <span class="rounded-lg bg-white/10 px-2.5 py-1 text-xs font-semibold text-white/80">
                            ⚡ Energy {{ $character->energy_level }}%
                        </span>
                    </div>
                </div>

                <div class="text-right">
                    <p class="text-[11px] font-bold uppercase tracking-widest text-white/45">Career Progress</p>
                    <p class="text-5xl font-black text-[#F9A8D4]">{{ $character->current_turn }}</p>
                    <p class="text-xs text-white/50">of {{ $character->getMaxTurns() }} turns</p>
                    @php $pct = round(($character->current_turn / max(1, $character->getMaxTurns())) * 100); @endphp
                    <div class="mt-2 h-1.5 w-40 rounded-full bg-white/10">
                        <div class="h-full rounded-full bg-linear-to-r from-[#E879A0] to-[#7C3AED] transition-all"
                            style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="mt-1 text-[10px] text-white/40">{{ $pct }}% complete</p>
                </div>
            </div>
        </div>

        {{-- Support Deck summary --}}
        <div class="rounded-[20px] border border-[#EDE9FE] bg-white p-5 shadow-[0_2px_12px_rgba(124,58,237,0.07)]">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-[15px] font-extrabold text-[#1E1033]">Support Deck</h2>
                    <p class="mt-0.5 text-xs text-[#7C6FAB]">
                        {{ $character->supportCards->count() }} / 6 cards assigned
                    </p>
                </div>
                <a href="{{ route('characters.deck-builder', $character) }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-linear-to-r from-[#E879A0] to-[#7C3AED] px-4 py-2 text-sm font-bold text-white shadow-[0_4px_12px_rgba(232,121,160,.35)] transition hover:brightness-105">
                    Manage Deck
                </a>
            </div>

            @if ($character->supportCards->isNotEmpty())
                <div class="mt-4 grid grid-cols-6 gap-2">
                    @foreach (range(1, 6) as $slot)
                        @php $card = $character->supportCards->firstWhere('position_slot', $slot); @endphp
                        <div
                            class="rounded-xl border {{ $card ? 'border-[#7C3AED]/30 bg-[#F9F5FF]' : 'border-dashed border-[#C4B5FD] bg-[#FAFBFF]' }} p-2 text-center">
                            @if ($card)
                                <p class="text-[10px] font-bold text-[#7C3AED] truncate">
                                    {{ $card->supportCard->name ?? 'Card' }}</p>
                                <p class="text-[9px] text-[#7C6FAB]">Slot {{ $slot }}</p>
                            @else
                                <p class="text-[10px] text-[#C4B5FD]">—</p>
                                <p class="text-[9px] text-[#C4B5FD]">Slot {{ $slot }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div
                    class="mt-4 rounded-xl border border-dashed border-[#C4B5FD] bg-[#F9F5FF] px-4 py-3 text-center text-xs text-[#7C6FAB]">
                    No cards assigned yet. Build your support deck to optimize training.
                </div>
            @endif
        </div>

        {{-- Tab bar --}}
        <nav class="flex rounded-xl bg-[#EDE9FE] p-1 gap-1" role="tablist" aria-label="Character detail sections">
            @foreach ($tabs as $tab)
                <button type="button" @click="activeTab = '{{ $tab }}'" role="tab"
                    :aria-selected="activeTab === '{{ $tab }}'" data-testid="tab-{{ strtolower($tab) }}"
                    class="flex-1 rounded-[9px] px-3 py-2 text-xs font-bold transition whitespace-nowrap"
                    :class="activeTab === '{{ $tab }}'
                        ?
                        'bg-white text-[#7C3AED] shadow-[0_1px_6px_rgba(124,58,237,0.15)]' :
                        'text-[#7C6FAB] hover:text-[#1E1033]'">
                    {{ $tab }}
                </button>
            @endforeach
        </nav>

        {{-- ── TAB: Stats ────────────────────────────────────────────── --}}
        <div x-show="activeTab === 'Stats'" x-transition role="tabpanel" aria-label="Stats">
            <div class="grid gap-5 lg:grid-cols-2">
                {{-- Stats overview --}}
                <x-card>
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-[15px] font-extrabold text-[#1E1033]">Stats Overview</h2>
                        <span class="text-xs text-[#7C6FAB]">Updated {{ $character->updated_at->diffForHumans() }}</span>
                    </div>
                    <div class="space-y-4">
                        @foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
                            @php
                                $val = $character->getStat($stat);
                                $color = $statColors[$stat];
                                $icon = $statIcons[$stat];
                                $grade = $statGrade($val);
                                $pctBar = min(100, round($val / 12));
                            @endphp
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="flex items-center gap-1.5 font-bold text-[#4A3570]">
                                        {{ $icon }} {{ ucfirst($stat) }}
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-md px-2 py-0.5 text-[10px] font-extrabold uppercase text-white"
                                            style="background: {{ $gradeColors[$grade] ?? '#9CA3AF' }}">{{ $grade }}</span>
                                        <span class="font-extrabold"
                                            style="color: {{ $color }}">{{ $val }}</span>
                                    </div>
                                </div>
                                <div class="relative h-2 overflow-hidden rounded-full bg-[#EDE9FE]" role="progressbar"
                                    aria-valuenow="{{ $val }}" aria-valuemin="0" aria-valuemax="1200"
                                    aria-label="{{ ucfirst($stat) }}: {{ $val }}">
                                    <div class="h-full rounded-full transition-all duration-[0.6s]"
                                        style="width: {{ $pctBar }}%; background: linear-gradient(90deg, {{ $color }}99, {{ $color }})">
                                    </div>
                                    {{-- Soft cap marker at 1000 --}}
                                    <div class="absolute top-0 bottom-0 w-px"
                                        style="left: 83.33%; background: rgba(124,58,237,0.25)"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-4 rounded-xl bg-[#F9F5FF] px-3 py-2 text-[11px] text-[#7C6FAB]">
                        <strong class="text-[#7C3AED]">Soft cap</strong> at 1000 · gains above 1000 are at 50% rate · max
                        stored 1200
                    </p>
                </x-card>

                {{-- Condition --}}
                <x-card>
                    <h2 class="mb-4 text-[15px] font-extrabold text-[#1E1033]">Condition</h2>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach ([['⚡', 'Energy', $character->energy_level . '%', '#7C3AED'], ['😊', 'Mood', ucfirst($character->mood_status ?? 'normal'), '#E879A0'], ['💚', 'Status', ucfirst($character->status ?? 'active'), '#10B981'], ['✨', 'SP', (string) ($character->available_sp ?? 0), '#F59E0B']] as [$icon, $label, $value, $color])
                            <div class="rounded-xl bg-[#F9F5FF] p-3 text-center">
                                <div class="text-xl">{{ $icon }}</div>
                                <p class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-[#7C6FAB]">
                                    {{ $label }}</p>
                                <p class="text-base font-extrabold" style="color: {{ $color }}">
                                    {{ $value }}</p>
                            </div>
                        @endforeach
                    </div>

                    {{-- SP budget bar --}}
                    @php
                        $sp = (int) ($character->available_sp ?? 0);
                        $spMax = 500;
                        $spPct = min(100, round(($sp / max(1, $spMax)) * 100));
                    @endphp
                    <div class="mt-4 space-y-1">
                        <div class="flex items-center justify-between text-xs text-[#7C6FAB]">
                            <span class="font-bold">SP Budget</span>
                            <span>{{ $sp }} / {{ $spMax }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-[#EDE9FE]">
                            <div class="h-full rounded-full transition-all"
                                style="width: {{ $spPct }}%; background: linear-gradient(90deg,#F59E0B,#F97316)">
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('training.index', $character) }}"
                        class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-linear-to-r from-[#E879A0] to-[#7C3AED] py-2.5 text-sm font-bold text-white shadow-[0_4px_12px_rgba(232,121,160,.35)] transition hover:brightness-105">
                        ⚡ Go to Training
                    </a>
                </x-card>
            </div>
        </div>

        {{-- ── TAB: Aptitudes ────────────────────────────────────────── --}}
        <div x-show="activeTab === 'Aptitudes'" x-transition role="tabpanel" aria-label="Aptitudes">
            <x-card>
                <h2 class="mb-5 text-[15px] font-extrabold text-[#1E1033]">Aptitudes</h2>

                @php
                    $aptitudeGroups = [
                        'Distance' => $character->aptitudes->filter(fn($a) => !is_null($a->distance_type)),
                        'Surface' => $character->aptitudes->filter(fn($a) => !is_null($a->surface_type)),
                        'Running Style' => $character->aptitudes->filter(fn($a) => !is_null($a->running_style)),
                    ];
                    $styleLabels = [
                        'nige' => 'Front Runner 逃げ',
                        'senkou' => 'Pace Chaser 先行',
                        'sashi' => 'Late Surger 差し',
                        'oikomi' => 'End Closer 追込',
                        'front_runner' => 'Front Runner 逃げ',
                        'pace_chaser' => 'Pace Chaser 先行',
                        'late_surger' => 'Late Surger 差し',
                        'end_closer' => 'End Closer 追込',
                    ];
                @endphp

                <div class="space-y-6">
                    @foreach ($aptitudeGroups as $groupName => $aptitudes)
                        <div>
                            <p class="mb-2 text-[11px] font-extrabold uppercase tracking-widest text-[#7C6FAB]">
                                {{ $groupName }}</p>
                            @if ($aptitudes->count() > 0)
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($aptitudes as $aptitude)
                                        @php
                                            $raw =
                                                $aptitude->distance_type ??
                                                ($aptitude->surface_type ?? ($aptitude->running_style ?? '?'));
                                            $label = $styleLabels[$raw] ?? ucwords(str_replace('_', ' ', $raw));
                                            $grade = strtoupper($aptitude->grade ?? 'F');
                                            // Clamp to S (never SS)
                                            if (
                                                $grade === 'SS' ||
                                                !in_array($grade, ['S', 'A', 'B', 'C', 'D', 'E', 'F', 'G'])
                                            ) {
                                                $grade = 'S';
                                            }
                                        @endphp
                                        <div
                                            class="flex items-center gap-2 rounded-xl bg-[#F9F5FF] border border-[#EDE9FE] px-3 py-2">
                                            <span class="text-sm font-bold text-[#4A3570]">{{ $label }}</span>
                                            <span class="rounded-md px-2 py-0.5 text-[10px] font-extrabold text-white"
                                                style="background: {{ $gradeColors[$grade] ?? '#9CA3AF' }}">{{ $grade }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-[#7C6FAB]">No {{ strtolower($groupName) }} aptitude data.</p>
                            @endif
                        </div>
                    @endforeach

                    @if ($character->aptitudes->isEmpty())
                        <div class="py-8 text-center text-sm text-[#7C6FAB]">No aptitude data available yet.</div>
                    @endif
                </div>
            </x-card>
        </div>

        {{-- ── TAB: Factors ──────────────────────────────────────────── --}}
        <div x-show="activeTab === 'Factors'" x-transition role="tabpanel" aria-label="Factors">
            <x-card>
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-[15px] font-extrabold text-[#1E1033]">Inherited Factors</h2>
                    @can('update', $character)
                        <a href="{{ route('characters.factors.manage', $character) }}"
                            class="rounded-xl bg-[#EDE9FE] px-3 py-1.5 text-xs font-bold text-[#7C3AED] transition hover:bg-[#DDD6FE]">
                            Manage
                        </a>
                    @endcan
                </div>

                @if ($character->factors->count() > 0)
                    @php
                        $factorsByType = $character->factors->groupBy('factor_type');
                        $factorTypeLabels = [
                            'blue_stats' => ['label' => 'Stat Bonuses', 'color' => '#3B82F6'],
                            'red_aptitudes' => ['label' => 'Aptitude Upgrades', 'color' => '#EF4444'],
                            'green_unique_skills' => ['label' => 'Unique Skills', 'color' => '#10B981'],
                            'white_normal_skills' => ['label' => 'Normal Skills', 'color' => '#9CA3AF'],
                        ];
                    @endphp

                    <div class="space-y-5">
                        @foreach ($factorsByType as $type => $factors)
                            @php $meta = $factorTypeLabels[$type] ?? ['label' => ucwords(str_replace('_', ' ', $type)), 'color' => '#7C6FAB']; @endphp
                            <div>
                                <p class="mb-2 text-[11px] font-extrabold uppercase tracking-widest"
                                    style="color: {{ $meta['color'] }}">
                                    {{ $meta['label'] }}
                                </p>
                                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach ($factors as $factor)
                                        @php
                                            $stars = (int) str_replace('_star', '', $factor->star_level ?? '1');
                                            $bonus = $factor->getStatBonus();
                                        @endphp
                                        <div
                                            class="rounded-2xl border border-[#C4B5FD] bg-linear-to-br from-[#F9F5FF] to-[#EDE9FE] p-3 {{ $factor->is_active ? '' : 'opacity-50' }}">
                                            <p class="text-sm font-extrabold text-[#1E1033]">{{ $factor->factor_name }}
                                            </p>
                                            <div class="mt-1 flex items-center gap-1"
                                                aria-label="{{ $stars }} stars">
                                                @for ($i = 1; $i <= 3; $i++)
                                                    <span
                                                        class="text-sm {{ $i <= $stars ? 'text-[#F59E0B]' : 'text-[#D1D5DB]' }}"
                                                        aria-hidden="true">★</span>
                                                @endfor
                                            </div>
                                            @if ($type === 'blue_stats' && $bonus > 0)
                                                <span
                                                    class="mt-1 inline-block rounded-lg bg-white px-2 py-0.5 text-xs font-bold text-[#3B82F6]">+{{ $bonus }}
                                                    {{ ucfirst($factor->stat_type ?? '') }}</span>
                                            @elseif ($type === 'red_aptitudes')
                                                <span
                                                    class="mt-1 inline-block rounded-lg bg-white px-2 py-0.5 text-xs font-bold text-[#EF4444]">+{{ $stars }}
                                                    grade{{ $stars > 1 ? 's' : '' }}</span>
                                            @endif
                                            @if (!$factor->is_active)
                                                <span class="mt-1 block text-[10px] text-[#9CA3AF]">Inactive</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-10 text-center text-sm text-[#7C6FAB]">
                        <p class="text-2xl mb-2">🧬</p>
                        No inherited factors yet.
                        @can('update', $character)
                            <a href="{{ route('characters.factors.manage', $character) }}"
                                class="block mt-2 font-bold text-[#7C3AED] hover:underline">Add Factors →</a>
                        @endcan
                    </div>
                @endif
            </x-card>
        </div>

        {{-- ── TAB: Goals ────────────────────────────────────────────── --}}
        <div x-show="activeTab === 'Goals'" x-transition role="tabpanel" aria-label="Goals">
            <x-card>
                <h2 class="mb-5 text-[15px] font-extrabold text-[#1E1033]">Active Goals</h2>

                @php
                    $goals = is_array($character->goals) ? $character->goals : [];
                    $goalList = $goals['list'] ?? [];
                    $targetStats = $goals['target_stats'] ?? [];
                @endphp

                @if (count($goalList) > 0)
                    <div class="space-y-3">
                        @foreach ($goalList as $goal)
                            @php
                                $status = $goal['status'] ?? 'in_progress';
                                $current = (int) ($goal['current'] ?? 0);
                                $target = (int) ($goal['target'] ?? 1);
                                $pct = $target > 0 ? min(100, round(($current / $target) * 100)) : 0;
                                $statusStyles = [
                                    'completed' => ['bg' => '#EDE9FE', 'text' => '#5B21B6', 'icon' => '✨'],
                                    'on_track' => ['bg' => '#D1FAE5', 'text' => '#065F46', 'icon' => '✅'],
                                    'at_risk' => ['bg' => '#FEE2E2', 'text' => '#991B1B', 'icon' => '🔴'],
                                    'in_progress' => ['bg' => '#EDE9FE', 'text' => '#5B21B6', 'icon' => '○'],
                                ];
                                $s = $statusStyles[$status] ?? $statusStyles['in_progress'];
                                $barColor =
                                    $status === 'at_risk'
                                        ? 'linear-gradient(90deg,#EF4444,#DC2626)'
                                        : 'linear-gradient(90deg,#E879A0,#7C3AED)';
                            @endphp
                            <div class="rounded-xl border border-[#EDE9FE] bg-[#F9F5FF] p-4">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-extrabold text-[#1E1033]">{{ $goal['label'] ?? 'Goal' }}</p>
                                    <span class="rounded-lg px-2 py-0.5 text-[10px] font-extrabold"
                                        style="background: {{ $s['bg'] }}; color: {{ $s['text'] }}">
                                        {{ $s['icon'] }} {{ ucfirst(str_replace('_', ' ', $status)) }}
                                    </span>
                                </div>
                                @if ($target > 1)
                                    <div class="mt-2">
                                        <div class="flex items-center justify-between text-[11px] text-[#7C6FAB] mb-1">
                                            <span>{{ $current }} / {{ $target }}</span>
                                            <span>{{ $pct }}%</span>
                                        </div>
                                        <div class="h-1.5 overflow-hidden rounded-full bg-[#EDE9FE]">
                                            <div class="h-full rounded-full transition-all"
                                                style="width: {{ $pct }}%; background: {{ $barColor }}">
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @elseif (count($targetStats) > 0)
                    <div class="space-y-3">
                        @foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
                            @if (isset($targetStats[$stat]) && $targetStats[$stat] > 0)
                                @php
                                    $target = (int) $targetStats[$stat];
                                    $current = $character->getStat($stat);
                                    $pct = min(100, round(($current / max(1, $target)) * 100));
                                    $status =
                                        $current >= $target
                                            ? 'completed'
                                            : ($pct >= 75
                                                ? 'on_track'
                                                : ($pct >= 40
                                                    ? 'in_progress'
                                                    : 'at_risk'));
                                    $barColor =
                                        $status === 'at_risk'
                                            ? 'linear-gradient(90deg,#EF4444,#DC2626)'
                                            : 'linear-gradient(90deg,' .
                                                $statColors[$stat] .
                                                '99,' .
                                                $statColors[$stat] .
                                                ')';
                                @endphp
                                <div class="rounded-xl border border-[#EDE9FE] bg-[#F9F5FF] p-4">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-sm font-extrabold text-[#1E1033]">{{ $statIcons[$stat] }}
                                            {{ ucfirst($stat) }} Goal</p>
                                        <span class="text-[11px] font-bold text-[#7C6FAB]">{{ $current }} /
                                            {{ $target }}</span>
                                    </div>
                                    <div class="mt-2">
                                        <div class="h-1.5 overflow-hidden rounded-full bg-[#EDE9FE]">
                                            <div class="h-full rounded-full transition-all"
                                                style="width: {{ $pct }}%; background: {{ $barColor }}">
                                            </div>
                                        </div>
                                        <p class="mt-1 text-right text-[10px] text-[#7C6FAB]">{{ $pct }}%</p>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="py-10 text-center text-sm text-[#7C6FAB]">
                        <p class="text-2xl mb-2">🎯</p>
                        No goals set yet. Edit this character to add stat targets.
                    </div>
                @endif
            </x-card>
        </div>

        {{-- ── TAB: History ──────────────────────────────────────────── --}}
        <div x-show="activeTab === 'History'" x-transition role="tabpanel" aria-label="History">
            <x-card>
                <h2 class="mb-5 text-[15px] font-extrabold text-[#1E1033]">Training History</h2>

                {{-- Career history requires final_grade column — planned for Phase 5 --}}
                <div class="py-8 text-center text-sm text-[#7C6FAB]">
                    <p class="text-2xl mb-2">📜</p>
                    Career run history will be available after the race system is complete.
                </div>

                {{-- Goal races --}}
                @if ($character->gameCharacter && $character->gameCharacter->goalRaces->isNotEmpty())
                    <div class="mt-6">
                        <p class="mb-3 text-[11px] font-extrabold uppercase tracking-widest text-[#7C6FAB]">Goal Races</p>
                        <div class="space-y-2">
                            @foreach ($character->gameCharacter->goalRaces->sortBy('pivot.priority') as $race)
                                <div
                                    class="flex items-center gap-3 rounded-xl bg-[#F9F5FF] border border-[#EDE9FE] px-3 py-2.5">
                                    <span class="rounded-md px-2 py-0.5 text-[10px] font-extrabold text-white"
                                        style="background: {{ $race->grade === 'G1' ? 'linear-gradient(135deg,#F59E0B,#F97316)' : '#EDE9FE' }}; color: {{ $race->grade === 'G1' ? '#fff' : '#7C3AED' }}">
                                        {{ $race->grade }}
                                    </span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-[#1E1033] truncate">{{ $race->name_en }}</p>
                                        <p class="text-[11px] text-[#7C6FAB]">
                                            {{ $race['distance_meters'] }}m · {{ ucfirst($race['distance_category']) }}
                                            @if (!empty($race['venue']))
                                                · {{ $race['venue'] }}
                                            @endif
                                            @if (!empty($race['pivot']['notes']))
                                                · {{ $race['pivot']['notes'] }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </x-card>
        </div>

        {{-- ── TAB: Snapshots ────────────────────────────────────────── --}}
        <div x-show="activeTab === 'Snapshots'" x-transition role="tabpanel" aria-label="Snapshots">
            <x-card>
                @livewire('snapshots.snapshot-manager', ['characterId' => $character->id], key('snapshot-manager-' . $character->id))
            </x-card>
        </div>

    </div>

    {{-- ── AI Career Plan Visualizer ───────────────────────────────────────── --}}
    <div id="career-plan-visualizer" class="mt-8">
        <div class="mb-4">
            <div class="flex items-center gap-3 mb-1">
                <h2 class="text-[15px] font-extrabold text-[#1E1033]">AI Career Plan</h2>
                <span class="text-[11px] font-extrabold uppercase tracking-widest text-[#7C6FAB]">Beta</span>
            </div>
            <p class="text-[13px] text-[#7C6FAB]">Generate a game-aware turn timeline using the current character state,
                skills, and races.</p>
        </div>
        <x-training-timeline :character-id="$character->id" :total-turns="78" :current-turn="$character->current_turn" :initial-plan="$latestCareerPlan?->plan ?? null" :initial-plan-meta="$latestCareerPlan
            ? [
                'id' => $latestCareerPlan->id,
                'goal' => $latestCareerPlan->goal,
                'locked' => (bool) $latestCareerPlan->is_locked,
            ]
            : null" />
    </div>

@endsection
