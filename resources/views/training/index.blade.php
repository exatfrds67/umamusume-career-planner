@extends('layouts.app')

@section('title', 'Training — ' . $character->name)

@section('content')
@php
    $statConfig = [
        'speed'   => ['label' => 'Speed',   'icon' => '⚡', 'color' => '#E879A0', 'bg' => '#FDF2F8'],
        'stamina' => ['label' => 'Stamina', 'icon' => '🌿', 'color' => '#10B981', 'bg' => '#F0FDF4'],
        'power'   => ['label' => 'Power',   'icon' => '🔥', 'color' => '#F59E0B', 'bg' => '#FFFBEB'],
        'guts'    => ['label' => 'Guts',    'icon' => '❤️', 'color' => '#EF4444', 'bg' => '#FEF2F2'],
        'wit'     => ['label' => 'Wit',     'icon' => '💙', 'color' => '#3B82F6', 'bg' => '#EFF6FF'],
    ];

    $energyLevel     = $character->energy_level ?? 100;
    $moodStatus      = $character->mood_status ?? 'normal';
    $careerComplete  = $character->current_turn >= 78;
    $currentStats    = is_array($character->current_stats) ? $character->current_stats : [];

    $moodMultiplier = match (strtolower($moodStatus)) {
        'great'  => '+20%',
        'good'   => '+10%',
        'bad'    => '−10%',
        'awful'  => '−20%',
        default  => '±0%',
    };

    $hasActiveBonds = false;
    $bondedCards    = [];
    try {
        $activeDeck = $character->activeSupportDeck()->with('supportCards')->first();
        if ($activeDeck) {
            foreach ($activeDeck->supportCards as $sc) {
                $bond = $sc->pivot?->getAttribute('bond_level') ?? 0;
                if ($bond >= 80) {
                    $hasActiveBonds = true;
                    $bondedCards[]  = $sc->name_en ?? $sc->title_en ?? 'Support Card';
                }
            }
        }
    } catch (\Throwable) {}
@endphp

<div class="space-y-5 pb-8"
     x-data="{
         selected: null,
         submitting: false,
         showResult: {{ session('training_result') ? 'true' : 'false' }},
         resultData: {{ json_encode(session('training_result', [])) }},
     }">

    {{-- ── Flash / Career Complete ─────────────────────────────────────────── --}}
    @if ($careerComplete)
        <div style="background:#EFF6FF; border:1px solid #BFDBFE; border-radius:12px;" class="p-4">
            <p class="font-bold" style="color:#1E40AF;">Career Complete!</p>
            <p class="text-sm mt-1" style="color:#3B82F6;">
                {{ $character->name }} has reached the maximum number of turns.
                <a href="{{ route('characters.show', $character) }}" class="underline">View final results →</a>
            </p>
        </div>
    @endif

    {{-- ── AI Recommendation Banner ─────────────────────────────────────────── --}}
    @if ($recommendedFacility && !$careerComplete)
        @php $rec = $statConfig[$recommendedFacility] ?? $statConfig['speed']; @endphp
        <div class="relative overflow-hidden rounded-2xl p-5" style="background: linear-gradient(135deg, #1E1033, #3B1F6E);">
            {{-- decorative orb --}}
            <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full pointer-events-none" style="background: rgba(232,121,160,0.12);"></div>
            <div class="relative flex items-center gap-4 flex-wrap">
                <span class="text-xs font-800 px-2 py-0.5 rounded-full" style="background:#E879A0; color:#fff; font-weight:800; letter-spacing:.5px;">AI</span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold" style="color:rgba(255,255,255,0.6);">
                        {{ $recommendedReason ?? 'Best training for current goals' }}
                    </p>
                    <p class="text-base font-bold mt-0.5" style="color:#fff;">
                        PICK:
                        <span class="ml-1 px-2 py-0.5 rounded-lg text-sm font-800" style="background:{{ $rec['color'] }}33; color:{{ $rec['color'] }}; font-weight:800;">
                            {{ $rec['icon'] }} {{ $rec['label'] }} Training
                        </span>
                    </p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-xs" style="color:rgba(255,255,255,0.4);">Mood</p>
                    <p class="text-sm font-bold" style="color:#F9A8D4;">{{ ucfirst($moodStatus) }} {{ $moodMultiplier }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Friendship Training Banner ───────────────────────────────────────── --}}
    @if ($hasActiveBonds)
        <div class="rounded-2xl p-4" style="background: linear-gradient(135deg, #ECFDF5, #D1FAE5); border: 1px solid #A7F3D0;">
            <div class="flex items-center gap-3">
                <span class="text-xl">🤝</span>
                <div>
                    <p class="text-sm font-bold" style="color:#065F46;">Friendship Training Active!</p>
                    <p class="text-xs mt-0.5" style="color:#047857;">
                        Bonded: {{ implode(', ', array_slice($bondedCards, 0, 3)) }}{{ count($bondedCards) > 3 ? ' +' . (count($bondedCards) - 3) . ' more' : '' }}
                        — Bond ≥ 80% grants bonus gains this turn.
                    </p>
                </div>
                <span class="ml-auto text-xs font-bold px-2 py-0.5 rounded-full" style="background:#10B981; color:#fff;">ACTIVE</span>
            </div>
        </div>
    @endif

    {{-- ── Page Header ──────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-xl font-black" style="color:#1E1033; font-size:22px; font-weight:900;">Training</h1>
            <p class="text-sm mt-0.5" style="color:#7C6FAB;">
                {{ $character->name }} · Turn {{ $character->current_turn }}/78 · {{ ucfirst($character->career_stage ?? 'junior') }}
            </p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            {{-- Energy pill --}}
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl" style="background:#F9F5FF; border:1px solid #EDE9FE;">
                <span class="text-xs font-bold" style="color:#7C6FAB;">⚡ Energy</span>
                <div class="w-16 h-2 rounded-full overflow-hidden" style="background:#EDE9FE;">
                    <div class="h-full rounded-full transition-all" style="width:{{ $energyLevel }}%; background: linear-gradient(90deg, #E879A0, #7C3AED);"></div>
                </div>
                <span class="text-xs font-bold" style="color:{{ $energyLevel < 30 ? '#EF4444' : ($energyLevel < 50 ? '#F59E0B' : '#10B981') }};">{{ $energyLevel }}</span>
            </div>
            <a href="{{ route('characters.show', $character) }}"
               class="px-4 py-2 rounded-xl text-sm font-bold transition-all"
               style="background:#EDE9FE; color:#7C3AED;">
                ← Back
            </a>
        </div>
    </div>

    {{-- ── Training Cards Grid ──────────────────────────────────────────────── --}}
    @if (!$careerComplete)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            @foreach ($statConfig as $type => $cfg)
                @php
                    $data       = $trainingData[$type] ?? ['gains' => [], 'failure_rate' => 0, 'energy_cost' => -20];
                    $isRec      = ($type === $recommendedFacility);
                    $primaryGain = collect($data['gains'])->filter(fn($v) => $v > 0)->sortDesc()->first() ?? 0;
                    $failRate   = $data['failure_rate'] ?? 0;
                @endphp
                <div class="relative cursor-pointer rounded-2xl transition-all duration-200 overflow-hidden"
                     :style="selected === '{{ $type }}'
                         ? 'border: 2px solid {{ $cfg['color'] }}; background: {{ $cfg['bg'] }}; box-shadow: 0 4px 20px {{ $cfg['color'] }}33;'
                         : 'border: 1px solid #EDE9FE; background: #fff; box-shadow: 0 2px 12px rgba(124,58,237,0.07);'"
                     @click="selected = '{{ $type }}'"
                     data-testid="training-card-{{ $type }}"
                     role="button"
                     :aria-pressed="selected === '{{ $type }}'"
                     tabindex="0"
                     @keydown.enter="selected = '{{ $type }}'">

                    {{-- AI BEST badge --}}
                    @if ($isRec)
                        <div class="absolute top-2 right-2 z-10 text-xs font-bold px-2 py-0.5 rounded-full" style="background:#E879A0; color:#fff;">AI BEST</div>
                    @endif

                    {{-- Card Header --}}
                    <div class="px-4 pt-4 pb-3" style="border-bottom: 1px solid #EDE9FE;">
                        <div class="flex items-center gap-2">
                            <span class="text-2xl leading-none">{{ $cfg['icon'] }}</span>
                            <span class="font-black text-base" style="color:#1E1033; font-weight:900;">{{ $cfg['label'] }}</span>
                        </div>
                        {{-- Facility level pills (read-only Lv 1–5) --}}
                        <div class="flex items-center gap-1 mt-2">
                            @for ($lv = 1; $lv <= 5; $lv++)
                                <div class="w-6 h-6 rounded-md flex items-center justify-center text-xs font-bold transition-all"
                                     style="background: {{ $lv === 1 ? $cfg['color'] : '#F9F5FF' }}; color: {{ $lv === 1 ? '#fff' : '#C4B5FD' }}; border: 1px solid {{ $lv === 1 ? $cfg['color'] : '#EDE9FE' }};">
                                    {{ $lv }}
                                </div>
                            @endfor
                            <span class="text-xs ml-1" style="color:#C4B5FD;">Lv</span>
                        </div>
                    </div>

                    {{-- Gains --}}
                    <div class="px-4 py-3 space-y-1.5">
                        @foreach ($data['gains'] as $stat => $gain)
                            @if ($gain > 0)
                                @php $gainCfg = $statConfig[$stat] ?? $statConfig['speed']; @endphp
                                <div class="flex justify-between items-center text-xs">
                                    <span class="font-semibold" style="color:#7C6FAB;">{{ $gainCfg['icon'] }} {{ ucfirst($stat) }}</span>
                                    <span class="font-black" style="color:{{ $gainCfg['color'] }}; font-weight:800;">+{{ $gain }}</span>
                                </div>
                            @endif
                        @endforeach

                        <div class="flex justify-between items-center text-xs pt-1.5" style="border-top: 1px solid #EDE9FE;">
                            <span style="color:#7C6FAB;">⚡ Energy</span>
                            <span class="font-bold" style="color:{{ $data['energy_cost'] < -15 ? '#EF4444' : '#7C6FAB' }};">
                                {{ $data['energy_cost'] > 0 ? '+' : '' }}{{ $data['energy_cost'] }}
                            </span>
                        </div>

                        @if ($failRate > 0)
                            <div class="flex justify-between items-center text-xs">
                                <span style="color:#7C6FAB;">Risk</span>
                                <span class="font-bold px-1.5 py-0.5 rounded" style="background:#FEF2F2; color:#EF4444;">{{ $failRate }}%</span>
                            </div>
                        @endif
                    </div>

                    {{-- Soft-cap warning --}}
                    @php $statVal = $currentStats[$type] ?? 0; @endphp
                    @if ($statVal >= 900 && $statVal < 1200)
                        <div class="px-3 pb-3">
                            <div class="text-xs px-2 py-1 rounded-lg font-bold" style="background:#FFFBEB; color:#B45309; border:1px solid #FDE68A;">
                                ⚠ Near cap ({{ $statVal }}/1200)
                            </div>
                        </div>
                    @elseif ($statVal >= 1200)
                        <div class="px-3 pb-3">
                            <div class="text-xs px-2 py-1 rounded-lg font-bold" style="background:#FEF2F2; color:#DC2626; border:1px solid #FECACA;">
                                🔴 Above cap ({{ $statVal }}/1200) — 50% gains
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Execute Button --}}
        <div class="flex justify-center">
            <form action="{{ route('training.store', $character) }}" method="POST" @submit="submitting = true">
                @csrf
                <input type="hidden" name="training_type" :value="selected">
                <button type="submit"
                        :disabled="!selected || submitting"
                        :class="{ 'opacity-50 cursor-not-allowed': !selected || submitting }"
                        class="px-8 py-3 rounded-xl text-base font-black transition-all"
                        style="background: linear-gradient(135deg, #E879A0, #7C3AED); color: #fff; box-shadow: 0 4px 12px rgba(232,121,160,.35); font-weight:900;"
                        data-testid="training-execute-btn">
                    <span x-show="!submitting">
                        <span x-show="selected">🏋️ Train <span x-text="selected ? selected.charAt(0).toUpperCase() + selected.slice(1) : ''"></span></span>
                        <span x-show="!selected">Select a training type above</span>
                    </span>
                    <span x-show="submitting">Training…</span>
                </button>
            </form>
        </div>
    @endif

    {{-- ── Current Stats Snapshot ───────────────────────────────────────────── --}}
    <div class="rounded-2xl overflow-hidden" style="background:#fff; border:1px solid #EDE9FE; box-shadow:0 2px 12px rgba(124,58,237,0.07);">
        <div class="px-5 py-4" style="border-bottom:1px solid #EDE9FE;">
            <h3 class="font-black text-sm" style="color:#1E1033; font-weight:800; font-size:15px;">Current Stats</h3>
        </div>
        <div class="px-5 py-4 space-y-3">
            @foreach ($statConfig as $type => $cfg)
                @php
                    $val       = $currentStats[$type] ?? 0;
                    $pct       = min(100, round(($val / 1200) * 100));
                    $capPct    = round((1000 / 1200) * 100);
                @endphp
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-xs font-bold" style="color:#7C6FAB;">{{ $cfg['icon'] }} {{ $cfg['label'] }}</span>
                        <span class="text-sm font-black" style="color:{{ $cfg['color'] }}; font-weight:900;">{{ number_format($val) }}</span>
                    </div>
                    <div class="relative h-2 rounded-full overflow-visible" style="background:#EDE9FE;">
                        {{-- Soft cap marker at 1000 --}}
                        <div class="absolute top-0 bottom-0 w-px z-10" style="left:{{ $capPct }}%; background:rgba(124,58,237,0.25);"></div>
                        {{-- Fill --}}
                        <div class="h-full rounded-full transition-all duration-600" style="width:{{ $pct }}%; background: linear-gradient(90deg, {{ $cfg['color'] }}99, {{ $cfg['color'] }});"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ── Turn Result Modal ────────────────────────────────────────────────── --}}
    @if (session('success') || session('warning') || session('error'))
        @php
            $resultType    = session('success') ? 'success' : (session('warning') ? 'warning' : 'error');
            $resultMessage = session($resultType);
        @endphp
        <div x-data="{ open: true }" x-show="open" class="fixed inset-0 z-50 flex items-center justify-center"
             style="background: rgba(15,10,40,0.75); backdrop-filter: blur(8px);"
             @keydown.escape.window="open = false"
             role="dialog" aria-modal="true" aria-label="Training result">
            <div class="w-full max-w-md rounded-3xl overflow-hidden shadow-2xl" style="background:#fff;">
                {{-- Header --}}
                <div class="px-6 py-5" style="background: {{ $resultType === 'success' ? 'linear-gradient(135deg,#1E1033,#3B1F6E)' : 'linear-gradient(135deg,#7F1D1D,#991B1B)' }};">
                    <div class="flex items-center gap-3">
                        <span class="text-3xl">{{ $resultType === 'success' ? '✅' : '⚠️' }}</span>
                        <div>
                            <h3 class="font-black text-xl" style="color:#fff; font-weight:900;">
                                {{ $resultType === 'success' ? 'Training Complete!' : 'Training Result' }}
                            </h3>
                            <p class="text-sm mt-0.5" style="color:rgba(255,255,255,0.7);">Turn {{ $character->current_turn }}/78</p>
                        </div>
                    </div>
                </div>
                {{-- Body --}}
                <div class="px-6 py-5">
                    <p class="text-sm font-semibold" style="color:#1E1033;">{{ $resultMessage }}</p>
                </div>
                {{-- Footer --}}
                <div class="px-6 pb-5 flex gap-3">
                    <button @click="open = false" type="button"
                            class="flex-1 py-2.5 rounded-xl font-bold text-sm transition-all"
                            style="background:#EDE9FE; color:#7C3AED;">
                        Continue Training
                    </button>
                    <a href="{{ route('characters.show', $character) }}"
                       class="flex-1 py-2.5 rounded-xl font-bold text-sm text-center"
                       style="background: linear-gradient(135deg,#E879A0,#7C3AED); color:#fff;">
                        View Character
                    </a>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
