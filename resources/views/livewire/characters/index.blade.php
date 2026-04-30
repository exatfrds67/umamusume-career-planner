<div class="space-y-8 pb-8">
    <section class="rounded-[20px] bg-linear-to-br from-[#1E1033] to-[#3B1F6E] p-6 text-white shadow-[0_24px_80px_rgba(124,58,237,0.18)] sm:p-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-white/60">Character roster</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight sm:text-4xl">Characters</h1>
                <p class="mt-2 max-w-2xl text-sm text-white/75 sm:text-base">Browse active career runs and jump into the detailed view or the creation wizard.</p>
            </div>

            <a href="{{ route('characters.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-linear-to-r from-[#E879A0] to-[#7C3AED] px-4 py-3 text-sm font-bold text-white shadow-[0_4px_12px_rgba(232,121,160,0.35)] transition hover:brightness-105">
                <span>+</span>
                New Character
            </a>
        </div>
    </section>

    <div class="flex flex-col gap-4 rounded-2xl border border-[#EDE9FE] bg-white p-4 shadow-[0_2px_12px_rgba(124,58,237,0.07)] lg:flex-row lg:items-center lg:justify-between">
        <div class="flex-1">
            <label for="character-search" class="sr-only">Search characters</label>
            <input id="character-search" type="text" wire:model.live.debounce.300ms="search" placeholder="Search characters by name or title" class="w-full rounded-xl border-[#EDE9FE] bg-[#F9F5FF] px-4 py-3 text-sm font-medium text-[#1E1033] placeholder:text-neutral-400 focus:border-[#E879A0] focus:ring-[#E879A0]">
        </div>

        <div class="flex items-center gap-3">
            <label for="character-sort" class="sr-only">Sort characters</label>
            <select id="character-sort" wire:model.live="sortBy" class="min-w-48 rounded-xl border-[#EDE9FE] bg-[#F9F5FF] px-4 py-3 text-sm font-medium text-[#1E1033] focus:border-[#E879A0] focus:ring-[#E879A0]">
                <option value="updated_at">Recently updated</option>
                <option value="created_at">Recently created</option>
                <option value="name">Name</option>
            </select>

            <button type="button" wire:click="clearSearch" class="rounded-xl border border-[#EDE9FE] bg-white px-4 py-3 text-sm font-bold text-[#7C3AED] transition hover:bg-[#F9F5FF]">
                Clear
            </button>
        </div>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        {{-- New Character CTA card --}}
        <a href="{{ route('characters.create') }}"
            data-testid="new-character-cta"
            class="flex min-h-70 flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed border-[#C4B5FD] bg-[#FAFBFF] text-center transition hover:border-[#E879A0] hover:bg-[#FDF2F8]"
            aria-label="Create new character">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl text-2xl"
                style="background: linear-gradient(135deg,#E879A0,#7C3AED)">🐴</div>
            <div>
                <p class="text-[15px] font-extrabold text-[#7C3AED]">New Character</p>
                <p class="mt-1 text-xs text-[#7C6FAB]">Start a fresh career run</p>
            </div>
        </a>

        @forelse ($characters as $character)
            @php
                $stats = $character->current_stats ?? [];
                $turnProgress = $character->current_turn && method_exists($character, 'getMaxTurns') ? (int) round(($character->current_turn / max(1, $character->getMaxTurns())) * 100) : 0;
            @endphp

            <article class="group overflow-hidden rounded-2xl border border-[#EDE9FE] bg-white shadow-[0_2px_12px_rgba(124,58,237,0.07)] transition hover:-translate-y-1 hover:shadow-[0_20px_50px_rgba(124,58,237,0.12)]">
                <div class="relative overflow-hidden bg-linear-to-br from-[#1E1033] to-[#3B1F6E] p-5 text-white">
                    <div class="absolute -right-3 -top-3 h-24 w-24 rounded-full bg-[#E879A0]/15"></div>
                    <div class="relative flex items-start justify-between gap-3">
                        <div class="space-y-2 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-badge variant="rarity-ssr">{{ strtoupper($character->status ?? 'active') }}</x-badge>
                                <x-badge variant="neutral">{{ ucwords(str_replace('_', ' ', (string) $character->scenario_type)) }}</x-badge>
                            </div>
                            <h2 class="text-xl font-black tracking-tight">{{ $character->name }}</h2>
                            <p class="text-sm text-white/65">{{ $character->title ?? 'Career run in progress' }}</p>
                        </div>

                        <div class="flex flex-col items-end gap-3">
                            <!-- Character Avatar (small) -->
                            <div class="w-16 h-20 shrink-0">
                                <x-character-avatar :character="$character" size="sm" />
                            </div>

                            <!-- Progress -->
                            <div class="text-right">
                                <div class="text-3xl font-black text-[#F9A8D4]">{{ $turnProgress }}%</div>
                                <div class="text-[11px] uppercase tracking-[0.12em] text-white/45">complete</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 h-1.5 rounded-full bg-white/10">
                        <div class="h-full rounded-full bg-linear-to-r from-[#E879A0] to-[#7C3AED]" style="width: {{ $turnProgress }}%"></div>
                    </div>
                </div>

                <div class="space-y-4 p-5">
                    <div class="grid grid-cols-5 gap-2">
                        @foreach (['speed' => 'S', 'stamina' => 'T', 'power' => 'P', 'guts' => 'G', 'wit' => 'W'] as $key => $label)
                            <div class="rounded-2xl bg-[#F9F5FF] p-3 text-center">
                                <div class="text-[11px] font-bold uppercase tracking-[0.12em] text-neutral-500">{{ $label }}</div>
                                <div class="mt-1 text-lg font-black text-[#1E1033]">{{ (int) ($stats[$key] ?? 0) }}</div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <x-badge variant="secondary">{{ ucfirst((string) $character->mood_status) }}</x-badge>
                            <x-badge variant="neutral">Turn {{ (int) $character->current_turn }}</x-badge>
                        </div>

                        <a href="{{ route('characters.show', $character) }}" class="inline-flex items-center gap-2 text-sm font-bold text-[#7C3AED] transition hover:text-[#E879A0]">
                            Open detail
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <x-card class="sm:col-span-2 xl:col-span-3">
                <div class="rounded-2xl border border-dashed border-[#C4B5FD] bg-[#F9F5FF] p-8 text-center">
                    <h2 class="text-lg font-extrabold text-[#1E1033]">No characters found</h2>
                    <p class="mt-2 text-sm text-neutral-600">Create your first Uma Musume career run to start tracking progress.</p>
                    <a href="{{ route('characters.create') }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-linear-to-r from-[#E879A0] to-[#7C3AED] px-4 py-3 text-sm font-bold text-white shadow-[0_4px_12px_rgba(232,121,160,0.35)] transition hover:brightness-105">
                        <span>+</span>
                        Create Character
                    </a>
                </div>
            </x-card>
        @endforelse
    </div>
</div>