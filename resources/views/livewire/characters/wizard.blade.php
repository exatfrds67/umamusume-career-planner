<div class="space-y-8 pb-8" x-data="{ step: @entangle('step') }">

    {{-- Page header --}}
    <section class="rounded-[20px] bg-linear-to-br from-[#1E1033] to-[#3B1F6E] p-6 text-white shadow-[0_24px_80px_rgba(124,58,237,0.18)] sm:p-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-white/60">Character creation</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">Creation Wizard</h1>
                <p class="mt-2 max-w-2xl text-sm text-white/75 sm:text-base">Step through trainee selection, inheritance, support deck, and review.</p>
            </div>
            <div class="rounded-2xl bg-white/10 px-4 py-3 text-sm font-semibold text-white/80 backdrop-blur-sm">
                Step <span class="font-black text-white" x-text="step"></span> of 4
            </div>
        </div>
    </section>

    {{-- Step progress bar --}}
    <div class="flex items-center gap-2">
        @foreach ([1 => 'Trainee', 2 => 'Parents', 3 => 'Deck', 4 => 'Review'] as $n => $label)
            <div class="flex flex-1 flex-col items-center gap-1">
                <div class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-black transition"
                    :class="{
                        'bg-linear-to-r from-[#10B981] to-[#059669] text-white': step > {{ $n }},
                        'bg-linear-to-r from-[#E879A0] to-[#7C3AED] text-white shadow-[0_0_12px_rgba(232,121,160,.4)]': step === {{ $n }},
                        'bg-[#EDE9FE] text-[#7C6FAB]': step < {{ $n }},
                    }">
                    <span x-show="step > {{ $n }}">✓</span>
                    <span x-show="step <= {{ $n }}">{{ $n }}</span>
                </div>
                <span class="text-[10px] font-700 text-[#7C6FAB]">{{ $label }}</span>
            </div>
            @if ($n < 4)
                <div class="mb-5 h-px flex-1 transition" :class="step > {{ $n }} ? 'bg-[#10B981]' : 'bg-[#EDE9FE]'"></div>
            @endif
        @endforeach
    </div>

    {{-- ── STEP 1: Trainee + Scenario ───────────────────────────────── --}}
    <div x-show="step === 1" x-transition>
        <x-card>
            <div class="space-y-6">
                <div>
                    <h2 class="text-lg font-extrabold text-[#1E1033]">Choose Your Trainee</h2>
                    <p class="mt-1 text-sm text-[#7C6FAB]">Select the Uma Musume you want to train this career run.</p>
                </div>

                {{-- Search --}}
                <div class="flex items-center gap-3">
                    <div class="relative flex-1">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-[#C4B5FD]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" wire:model.live.debounce.200ms="search" placeholder="Search trainees…"
                            class="w-full rounded-xl border border-[#EDE9FE] bg-[#F9F5FF] py-2.5 pl-9 pr-4 text-sm font-medium text-[#1E1033] placeholder:text-[#C4B5FD] focus:border-[#E879A0] focus:ring-1 focus:ring-[#E879A0] outline-none"
                            aria-label="Search trainees" />
                    </div>
                </div>

                {{-- Trainee grid --}}
                <div class="grid gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5"
                    role="listbox" aria-label="Select a trainee" aria-required="true">
                    @forelse ($trainees as $trainee)
                        <button type="button"
                            wire:click="selectTrainee({{ $trainee->id }})"
                            role="option"
                            aria-selected="{{ $selectedTraineeId === $trainee->id ? 'true' : 'false' }}"
                            data-testid="trainee-card-{{ $trainee->id }}"
                            class="overflow-hidden rounded-2xl border bg-white text-left transition hover:-translate-y-0.5 hover:shadow-lg {{ $selectedTraineeId === $trainee->id ? 'border-[#E879A0] ring-2 ring-[#E879A0]/40 bg-linear-to-br from-[#FDF2F8] to-[#F5F3FF]' : 'border-[#EDE9FE]' }}">
                            <div class="aspect-[3/4] overflow-hidden bg-linear-to-br from-[#1E1033] to-[#3B1F6E] relative">
                                @if ($trainee->avatar_url)
                                    <img src="{{ $trainee->avatar_url }}" alt="{{ $trainee->name_en }}"
                                        class="h-full w-full object-cover" loading="lazy"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
                                @endif
                                <div class="{{ $trainee->avatar_url ? 'hidden' : 'flex' }} absolute inset-0 items-center justify-center">
                                    <span class="text-4xl">🐴</span>
                                </div>
                                @if ($selectedTraineeId === $trainee->id)
                                    <div class="absolute inset-0 flex items-center justify-center bg-[#E879A0]/20">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#E879A0] text-white font-black">✓</div>
                                    </div>
                                @endif
                            </div>
                            <div class="p-2 space-y-1">
                                <p class="text-xs font-extrabold text-[#1E1033] leading-tight truncate">{{ $trainee->name_en }}</p>
                                <span class="inline-block rounded-md bg-linear-to-r from-[#F59E0B] to-[#F97316] px-1.5 py-0.5 text-[9px] font-800 text-white uppercase tracking-wide">SSR</span>
                            </div>
                        </button>
                    @empty
                        <div class="col-span-5 py-10 text-center text-sm text-[#7C6FAB]">No trainees found matching your search.</div>
                    @endforelse
                </div>

                {{-- Scenario selector --}}
                <div class="rounded-2xl border border-[#EDE9FE] bg-[#F9F5FF] p-4">
                    <p class="mb-3 text-sm font-extrabold text-[#1E1033]">Choose Scenario</p>
                    <div class="flex flex-wrap gap-3">
                        <button type="button" wire:click="selectScenario('ura_finale')"
                            data-testid="scenario-ura-finale"
                            class="flex items-center gap-2 rounded-xl border-2 px-4 py-2.5 text-sm font-bold transition {{ $selectedScenario === 'ura_finale' ? 'border-[#E879A0] bg-[#FDF2F8] text-[#E879A0]' : 'border-[#EDE9FE] bg-white text-[#7C6FAB]' }}">
                            🏆 URA Finals
                        </button>
                        <button type="button" wire:click="selectScenario('unity_cup')"
                            data-testid="scenario-unity-cup"
                            class="flex items-center gap-2 rounded-xl border-2 px-4 py-2.5 text-sm font-bold transition {{ $selectedScenario === 'unity_cup' ? 'border-[#E879A0] bg-[#FDF2F8] text-[#E879A0]' : 'border-[#EDE9FE] bg-white text-[#7C6FAB]' }}">
                            🤝 Unity Cup
                        </button>
                    </div>
                    @if ($selectedScenario === 'unity_cup')
                        <p class="mt-2 text-xs text-[#7C6FAB]">Unity Cup extends the career run to 78 turns.</p>
                    @endif
                </div>
            </div>
        </x-card>
    </div>

    {{-- ── STEP 2: Parent Selection ──────────────────────────────────── --}}
    <div x-show="step === 2" x-transition>
        <x-card>
            <div class="space-y-6">
                <div>
                    <h2 class="text-lg font-extrabold text-[#1E1033]">Parent Inheritance</h2>
                    <p class="mt-1 text-sm text-[#7C6FAB]">Select Parent A and Parent B to inherit stat bonuses and aptitude upgrades.</p>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    {{-- Parent A --}}
                    <div class="space-y-3">
                        <p class="text-xs font-extrabold uppercase tracking-widest text-[#7C6FAB]">Parent A</p>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            @foreach ($parents as $parent)
                                <button type="button" wire:click="selectFather({{ $parent->id }})"
                                    data-testid="parent-a-{{ $parent->id }}"
                                    class="overflow-hidden rounded-xl border p-2 text-left transition hover:shadow-md {{ $selectedFatherId === $parent->id ? 'border-[#E879A0] bg-[#FDF2F8]' : 'border-[#EDE9FE] bg-white' }}">
                                    <p class="text-xs font-bold text-[#1E1033] truncate">{{ $parent->name }}</p>
                                    <p class="text-[10px] text-[#7C6FAB]">{{ ucwords(str_replace('_', ' ', $parent->scenario_type ?? '')) }}</p>
                                    @if ($selectedFatherId === $parent->id)
                                        <span class="mt-1 inline-block text-[10px] font-800 text-[#E879A0]">✓ Selected</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Parent B --}}
                    <div class="space-y-3">
                        <p class="text-xs font-extrabold uppercase tracking-widest text-[#7C6FAB]">Parent B</p>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            @foreach ($parents as $parent)
                                <button type="button" wire:click="selectMother({{ $parent->id }})"
                                    data-testid="parent-b-{{ $parent->id }}"
                                    class="overflow-hidden rounded-xl border p-2 text-left transition hover:shadow-md {{ $selectedMotherId === $parent->id ? 'border-[#7C3AED] bg-[#F5F3FF]' : 'border-[#EDE9FE] bg-white' }}">
                                    <p class="text-xs font-bold text-[#1E1033] truncate">{{ $parent->name }}</p>
                                    <p class="text-[10px] text-[#7C6FAB]">{{ ucwords(str_replace('_', ' ', $parent->scenario_type ?? '')) }}</p>
                                    @if ($selectedMotherId === $parent->id)
                                        <span class="mt-1 inline-block text-[10px] font-800 text-[#7C3AED]">✓ Selected</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Inheritance preview --}}
                @if ($selectedFatherId && $selectedMotherId)
                    <div class="rounded-2xl border border-[#C4B5FD] bg-linear-to-br from-[#F9F5FF] to-[#EDE9FE] p-4">
                        <p class="mb-3 text-sm font-extrabold text-[#1E1033]">✨ Inheritance Preview</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach (['speed' => ['⚡', '#E879A0'], 'stamina' => ['🌿', '#10B981'], 'power' => ['🔥', '#F59E0B'], 'guts' => ['❤️', '#EF4444'], 'wit' => ['💙', '#3B82F6']] as $stat => $meta)
                                <div class="flex items-center gap-1.5 rounded-xl border border-[#EDE9FE] bg-white px-3 py-2">
                                    <span>{{ $meta[0] }}</span>
                                    <span class="text-xs font-extrabold capitalize" style="color: {{ $meta[1] }}">{{ ucfirst($stat) }}</span>
                                    <span class="text-xs font-bold text-[#7C6FAB]">+0</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-2 text-[11px] text-[#7C6FAB]">Factor bonuses depend on parent factor data. 1★=+5, 2★=+12, 3★=+21.</p>
                    </div>
                @endif

                @if (! $selectedFatherId || ! $selectedMotherId)
                    <p class="text-center text-sm text-[#7C6FAB]">Select both parents to preview inheritance bonuses.</p>
                @endif
            </div>
        </x-card>
    </div>

    {{-- ── STEP 3: Support Deck ──────────────────────────────────────── --}}
    <div x-show="step === 3" x-transition>
        <x-card>
            <div class="space-y-6">
                <div>
                    <h2 class="text-lg font-extrabold text-[#1E1033]">Support Deck</h2>
                    <p class="mt-1 text-sm text-[#7C6FAB]">Fill up to 6 support card slots. At least 1 card required to proceed.</p>
                </div>

                {{-- 6-slot grid --}}
                <div class="grid grid-cols-3 gap-3 sm:grid-cols-6" role="list" aria-label="Support deck slots">
                    @foreach (range(0, 5) as $slot)
                        @php $cardId = $supportDeck[$slot] ?? null; @endphp
                        <div role="listitem">
                            <button type="button"
                                wire:click="setActiveSlot({{ $slot }})"
                                data-testid="deck-slot-{{ $slot }}"
                                aria-label="Deck slot {{ $slot + 1 }}{{ $cardId ? ' (filled)' : ' (empty)' }}"
                                class="w-full rounded-2xl border-2 transition {{ $activeSlot === $slot ? 'border-[#E879A0] shadow-[0_0_12px_rgba(232,121,160,0.3)]' : ($cardId ? 'border-[#7C3AED]' : 'border-dashed border-[#EDE9FE]') }} flex min-h-24 flex-col items-center justify-center gap-1 p-2 text-center">
                                @if ($cardId)
                                    @php
                                        $card = $supportCards->firstWhere('id', $cardId);
                                    @endphp
                                    <span class="text-[10px] font-extrabold uppercase tracking-wide text-[#7C3AED]">{{ $card?->card_type ?? '—' }}</span>
                                    <span class="text-xs font-bold text-[#1E1033] leading-tight">{{ Str::limit($card?->name ?? '?', 14) }}</span>
                                    <button type="button" wire:click.stop="clearSupport({{ $slot }})"
                                        class="mt-1 text-[10px] text-[#EF4444] hover:underline"
                                        aria-label="Remove card from slot {{ $slot + 1 }}">Remove</button>
                                @else
                                    <span class="text-2xl font-black text-[#C4B5FD]">+</span>
                                    <span class="text-[10px] font-bold text-[#C4B5FD]">Slot {{ $slot + 1 }}</span>
                                @endif
                            </button>
                        </div>
                    @endforeach
                </div>

                {{-- Card picker (shown when a slot is active) --}}
                @if ($activeSlot >= 0)
                    <div class="rounded-2xl border border-[#EDE9FE] bg-[#F9F5FF] p-4">
                        <p class="mb-3 text-sm font-extrabold text-[#1E1033]">Select a card for Slot {{ $activeSlot + 1 }}</p>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach ($supportCards as $card)
                                <button type="button"
                                    wire:click="assignSupport({{ $card->id }})"
                                    data-testid="support-card-{{ $card->id }}"
                                    class="flex items-center gap-3 rounded-xl border border-[#EDE9FE] bg-white p-3 text-left transition hover:border-[#E879A0] hover:bg-[#FDF2F8]">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-white text-xs font-extrabold"
                                        style="background: linear-gradient(135deg, #E879A0, #7C3AED)">
                                        {{ strtoupper(substr($card->card_type ?? 'S', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-xs font-extrabold text-[#1E1033]">{{ $card->name }}</p>
                                        <p class="text-[10px] text-[#7C6FAB]">{{ $card->rarity }} · {{ ucfirst($card->card_type ?? '—') }}</p>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </x-card>
    </div>

    {{-- ── STEP 4: Review + Create ──────────────────────────────────── --}}
    <div x-show="step === 4" x-transition>
        <x-card>
            <div class="space-y-6">
                <div>
                    <h2 class="text-lg font-extrabold text-[#1E1033]">Review & Create</h2>
                    <p class="mt-1 text-sm text-[#7C6FAB]">Confirm your selections and start the career run.</p>
                </div>

                @error('creation')
                    <div class="rounded-xl border border-[#FEE2E2] bg-[#FEF2F2] p-3 text-sm font-medium text-[#DC2626]" role="alert">{{ $message }}</div>
                @enderror

                <div class="divide-y divide-[#EDE9FE] rounded-2xl border border-[#EDE9FE] bg-[#F9F5FF]">
                    @php
                        $traineeObj = $trainees->firstWhere('id', $selectedTraineeId);
                        $filledSlots = count(array_filter($supportDeck));
                    @endphp

                    <div class="flex items-center justify-between px-4 py-3">
                        <span class="text-sm font-bold text-[#7C6FAB]">Trainee</span>
                        <span class="text-sm font-extrabold text-[#1E1033]">{{ $traineeObj?->name_en ?? '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-3">
                        <span class="text-sm font-bold text-[#7C6FAB]">Scenario</span>
                        <span class="text-sm font-extrabold text-[#1E1033]">{{ $selectedScenario === 'unity_cup' ? 'Unity Cup (78 turns)' : 'URA Finals (72 turns)' }}</span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-3">
                        <span class="text-sm font-bold text-[#7C6FAB]">Parent A</span>
                        <span class="text-sm font-extrabold text-[#1E1033]">{{ $selectedFather?->name ?? '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-3">
                        <span class="text-sm font-bold text-[#7C6FAB]">Parent B</span>
                        <span class="text-sm font-extrabold text-[#1E1033]">{{ $selectedMother?->name ?? '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-3">
                        <span class="text-sm font-bold text-[#7C6FAB]">Support Deck</span>
                        <span class="text-sm font-extrabold text-[#1E1033]">{{ $filledSlots }}/6 slots filled</span>
                    </div>
                </div>

                <div class="rounded-2xl border border-[#C4B5FD] bg-linear-to-br from-[#F9F5FF] to-[#EDE9FE] p-5 text-center">
                    <div class="text-3xl mb-2">🎉</div>
                    <p class="text-base font-extrabold text-[#1E1033]">Ready to start your career run!</p>
                    <p class="mt-1 text-sm text-[#7C6FAB]">{{ $traineeObj?->name_en ?? 'Your trainee' }} will begin at Turn 1, Junior Year.</p>
                </div>
            </div>
        </x-card>
    </div>

    {{-- Footer navigation --}}
    <div class="flex items-center justify-between gap-3">
        <div>
            @if ($step > 1)
                <button type="button" wire:click="previousStep"
                    data-testid="wizard-back"
                    class="rounded-xl border border-[#EDE9FE] bg-white px-5 py-2.5 text-sm font-bold text-[#7C3AED] transition hover:bg-[#F9F5FF]">
                    ← Back
                </button>
            @else
                <a href="{{ route('characters.index') }}"
                    class="rounded-xl border border-[#EDE9FE] bg-white px-5 py-2.5 text-sm font-bold text-[#7C6FAB] transition hover:bg-[#F9F5FF]">
                    Cancel
                </a>
            @endif
        </div>

        <div>
            @if ($step < 4)
                <button type="button" wire:click="nextStep"
                    data-testid="wizard-next"
                    @if (! $canAdvance) disabled @endif
                    class="rounded-xl bg-linear-to-r from-[#E879A0] to-[#7C3AED] px-6 py-2.5 text-sm font-bold text-white shadow-[0_4px_12px_rgba(232,121,160,0.35)] transition hover:brightness-105 disabled:opacity-50 disabled:cursor-not-allowed">
                    Continue →
                </button>
            @else
                <button type="button"
                    wire:click="createCharacter"
                    data-testid="wizard-create"
                    wire:loading.attr="disabled"
                    class="rounded-xl bg-linear-to-r from-[#F59E0B] to-[#F97316] px-6 py-2.5 text-sm font-bold text-white shadow-[0_4px_12px_rgba(245,158,11,0.35)] transition hover:brightness-105 disabled:opacity-70">
                    <span wire:loading.remove wire:target="createCharacter">🎉 Create Character</span>
                    <span wire:loading wire:target="createCharacter">Creating…</span>
                </button>
            @endif
        </div>
    </div>

</div>
