<div style="background:#F9F5FF; min-height:100vh; padding:24px;">

    {{-- Toast --}}
    @if ($toastMessage)
        <div wire:key="toast"
            style="position:fixed; top:20px; right:20px; z-index:9999; padding:14px 20px; border-radius:12px; font-family:'Nunito',sans-serif; font-size:13px; font-weight:700; box-shadow:0 8px 32px rgba(124,58,237,0.25); max-width:320px;
                {{ $toastType === 'success' ? 'background:linear-gradient(135deg,#1E1033,#3B1F6E); color:#fff;' : 'background:#FEE2E2; color:#991B1B;' }}"
            x-data x-init="setTimeout(() => $wire.dismissToast(), 3500)">
            {{ $toastMessage }}
        </div>
    @endif

    {{-- Page Header --}}
    <div
        style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 style="font-family:'Nunito',sans-serif; font-size:22px; font-weight:900; color:#1E1033; margin:0;">
                ✨ Skill Management
            </h1>
            @if ($this->character)
                <p style="font-family:'Nunito',sans-serif; font-size:13px; color:#7C6FAB; margin:4px 0 0;">
                    {{ $this->character->name }} · {{ $this->ownedCount }} skills owned
                </p>
            @endif
        </div>

        {{-- Character Selector --}}
        <div>
            <select wire:model.live="characterId"
                style="font-family:'Nunito',sans-serif; font-size:13px; font-weight:600; color:#1E1033; background:#fff; border:1px solid #EDE9FE; border-radius:10px; padding:8px 14px; outline:none; cursor:pointer;"
                aria-label="Select character">
                <option value="">— Select Character —</option>
                @foreach ($this->characters as $char)
                    <option value="{{ $char->id }}">{{ $char->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if (!$this->character)
        {{-- Empty state --}}
        <div
            style="background:#fff; border:1px solid #EDE9FE; border-radius:16px; padding:48px; text-align:center; box-shadow:0 2px 12px rgba(124,58,237,0.07);">
            <div style="font-size:48px; margin-bottom:16px;">✨</div>
            <h3
                style="font-family:'Nunito',sans-serif; font-size:18px; font-weight:800; color:#1E1033; margin:0 0 8px;">
                No Character Selected</h3>
            <p style="font-family:'Nunito',sans-serif; font-size:13px; color:#7C6FAB; margin:0 0 20px;">Select a
                character above to manage their skills and SP budget.</p>
            <a href="{{ route('characters.index') }}"
                style="display:inline-block; background:linear-gradient(135deg,#E879A0,#7C3AED); color:#fff; font-family:'Nunito',sans-serif; font-size:14px; font-weight:700; padding:10px 24px; border-radius:10px; text-decoration:none; box-shadow:0 4px 12px rgba(232,121,160,0.35);">
                Go to Characters
            </a>
        </div>
    @else
        {{-- SP Budget Card --}}
        <div
            style="background:linear-gradient(135deg,#FFFBEB,#FEF3C7); border:1px solid #FCD34D; border-radius:16px; padding:20px 24px; margin-bottom:16px; box-shadow:0 2px 12px rgba(124,58,237,0.07);">
            <div style="display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                <div>
                    <div
                        style="font-family:'Nunito',sans-serif; font-size:15px; font-weight:800; color:#92400E; margin-bottom:4px;">
                        ✨ SP Budget
                    </div>
                    <div style="font-family:'Nunito',sans-serif; font-size:12px; color:#92400E; opacity:0.75;">
                        Hint discounts reduce purchase costs up to 40%
                    </div>
                </div>
                <div style="text-align:right;">
                    <div
                        style="font-family:'Nunito',sans-serif; font-size:32px; font-weight:900; color:#F59E0B; line-height:1;">
                        {{ number_format($this->spBudget['available']) }}
                    </div>
                    <div style="font-family:'Nunito',sans-serif; font-size:11px; color:#92400E;">
                        of {{ number_format($this->spBudget['total_earned']) }} total earned
                    </div>
                </div>
            </div>
            {{-- Progress bar --}}
            @php
                $pct =
                    $this->spBudget['total_earned'] > 0
                        ? min(100, ($this->spBudget['used'] / $this->spBudget['total_earned']) * 100)
                        : 0;
            @endphp
            <div
                style="margin-top:12px; background:rgba(245,158,11,0.2); border-radius:99px; height:10px; overflow:hidden;">
                <div
                    style="height:10px; border-radius:99px; background:linear-gradient(90deg,#F59E0B,#F97316); width:{{ $pct }}%; transition:width 0.6s cubic-bezier(.4,0,.2,1);">
                </div>
            </div>
            <div style="display:flex; justify-content:space-between; margin-top:6px;">
                <span style="font-family:'Nunito',sans-serif; font-size:11px; color:#92400E;">Used:
                    {{ number_format($this->spBudget['used']) }}</span>
                <span style="font-family:'Nunito',sans-serif; font-size:11px; color:#92400E;">Remaining:
                    {{ number_format($this->spBudget['available']) }}</span>
            </div>
        </div>

        {{-- Hint Discount Legend --}}
        <div
            style="background:#F5F3FF; border:1px solid #C4B5FD; border-radius:16px; padding:16px 20px; margin-bottom:16px;">
            <div
                style="font-family:'Nunito',sans-serif; font-size:12px; font-weight:800; color:#7C3AED; margin-bottom:10px; letter-spacing:0.5px;">
                💡 HINT LEVEL DISCOUNTS
            </div>
            <div style="display:grid; grid-template-columns:repeat(6,1fr); gap:8px;">
                @foreach ([0 => ['label' => 'Base', 'pct' => '0%', 'color' => '#9CA3AF'], 1 => ['label' => 'Lv.1', 'pct' => '−10%', 'color' => '#10B981'], 2 => ['label' => 'Lv.2', 'pct' => '−20%', 'color' => '#10B981'], 3 => ['label' => 'Lv.3', 'pct' => '−30%', 'color' => '#10B981'], 4 => ['label' => 'Lv.4', 'pct' => '−35%', 'color' => '#10B981'], 5 => ['label' => 'Lv.5', 'pct' => '−40%', 'color' => '#10B981']] as $lvl => $info)
                    <div
                        style="background:#fff; border:1px solid #EDE9FE; border-radius:8px; padding:8px 4px; text-align:center;">
                        <div style="font-family:'Nunito',sans-serif; font-size:10px; font-weight:700; color:#7C6FAB;">
                            {{ $info['label'] }}</div>
                        <div
                            style="font-family:'Nunito',sans-serif; font-size:12px; font-weight:800; color:{{ $info['color'] }};">
                            {{ $info['pct'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Filter Bar --}}
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:16px;">
            {{-- Search --}}
            <div style="position:relative; flex:1; min-width:200px;">
                <span
                    style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#C4B5FD; font-size:16px;">🔍</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search skills..."
                    style="width:100%; padding:9px 12px 9px 36px; background:#F9F5FF; border:1px solid #EDE9FE; border-radius:10px; font-family:'Nunito',sans-serif; font-size:13px; color:#1E1033; outline:none; box-sizing:border-box;"
                    aria-label="Search skills" />
            </div>

            {{-- Filter Tabs --}}
            @foreach ([
        'all' => 'All Skills',
        'owned' => 'Owned (' . $this->ownedCount . ')',
        'available' => 'Available',
        'hints' => '💡 Hints (' . $this->hintCount . ')',
        'evolve' => '⬆️ Evolve (' . $this->evolveCount . ')',
    ] as $key => $label)
                <button wire:click="$set('filter', '{{ $key }}')"
                    style="font-family:'Nunito',sans-serif; font-size:12px; font-weight:700; padding:8px 14px; border-radius:10px; border:none; cursor:pointer; transition:all 0.15s;
                       {{ $filter === $key ? 'background:linear-gradient(135deg,#E879A0,#7C3AED); color:#fff; box-shadow:0 4px 12px rgba(232,121,160,0.35);' : 'background:#EDE9FE; color:#7C6FAB;' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Skills Grid --}}
        @php
            $typeColors = [
                'unique' => '#F59E0B',
                'speed' => '#E879A0',
                'stamina' => '#10B981',
                'power' => '#F97316',
                'guts' => '#EF4444',
                'wit' => '#3B82F6',
                'style' => '#7C3AED',
                'distance' => '#06B6D4',
                'recovery' => '#8B5CF6',
            ];
            $typeEmojis = [
                'unique' => '⭐',
                'speed' => '⚡',
                'stamina' => '🌿',
                'power' => '🔥',
                'guts' => '❤️',
                'wit' => '💙',
                'style' => '🎯',
                'distance' => '🏁',
                'recovery' => '💜',
            ];
        @endphp

        @if ($this->skills->isEmpty())
            <div
                style="background:#fff; border:1px solid #EDE9FE; border-radius:16px; padding:48px; text-align:center;">
                <div style="font-size:40px; margin-bottom:12px;">🔍</div>
                <p style="font-family:'Nunito',sans-serif; font-size:14px; color:#7C6FAB;">No skills found matching your
                    filters.</p>
            </div>
        @else
            <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:14px;">
                @foreach ($this->skills as $skill)
                    @php
                        $typeColor = $typeColors[$skill->skill_type] ?? '#7C3AED';
                        $typeEmoji = $typeEmojis[$skill->skill_type] ?? '✨';
                        $isOwned = in_array($skill->id, $this->ownedSkillIds);
                        $hintLevel = $this->hintCounts[$skill->id] ?? 0;
                        $hasHint = $hintLevel > 0;
                        $isEvolvable = $isOwned && $skill->can_evolve;
                        $discountedCost = $this->getDiscountedCost($skill->id, $skill->base_sp_cost);
                        $hasDiscount = $discountedCost < $skill->base_sp_cost;
                        $canAfford = $this->spBudget['available'] >= $discountedCost;
                    @endphp
                    <div style="background:#fff; border-radius:16px; border:1px solid {{ $isOwned ? $typeColor . '44' : '#EDE9FE' }}; box-shadow:0 2px 12px rgba(124,58,237,0.07); overflow:hidden; position:relative; transition:box-shadow 0.15s;"
                        wire:key="skill-{{ $skill->id }}">

                        {{-- Owned stripe --}}
                        @if ($isOwned)
                            <div style="height:3px; background:{{ $typeColor }};"></div>
                        @endif

                        {{-- Badges top-right --}}
                        <div style="position:absolute; top:10px; right:10px; display:flex; gap:4px; z-index:1;">
                            @if ($hasHint)
                                <span
                                    style="background:#D1FAE5; color:#065F46; font-family:'Nunito',sans-serif; font-size:9px; font-weight:700; padding:2px 6px; border-radius:6px;">💡
                                    HINT</span>
                            @endif
                            @if ($isEvolvable)
                                <span
                                    style="background:linear-gradient(135deg,#F59E0B,#F97316); color:#fff; font-family:'Nunito',sans-serif; font-size:9px; font-weight:700; padding:2px 6px; border-radius:6px;">⭐
                                    GOLD</span>
                            @endif
                        </div>

                        <div style="padding:14px 16px;">
                            {{-- Icon + Name row --}}
                            <div style="display:flex; align-items:flex-start; gap:12px; margin-bottom:10px;">
                                <div
                                    style="width:42px; height:42px; border-radius:12px; background:{{ $typeColor }}18; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0;">
                                    {{ $typeEmoji }}
                                </div>
                                <div style="flex:1; min-width:0;">
                                    <div
                                        style="font-family:'Nunito',sans-serif; font-size:14px; font-weight:800; color:#1E1033; margin-bottom:4px; padding-right:60px;">
                                        {{ $skill->name }}
                                    </div>
                                    <div style="display:flex; gap:4px; flex-wrap:wrap;">
                                        <span
                                            style="background:{{ $typeColor }}18; color:{{ $typeColor }}; font-family:'Nunito',sans-serif; font-size:10px; font-weight:700; padding:2px 8px; border-radius:6px;">
                                            {{ ucfirst($skill->skill_type ?? 'general') }}
                                        </span>
                                        @if ($skill->rarity)
                                            <span
                                                style="background:{{ $skill->rarity === 'rare' ? '#EDE9FE' : ($skill->rarity === 'unique' ? '#FFFBEB' : '#F3F4F6') }}; color:{{ $skill->rarity === 'rare' ? '#7C3AED' : ($skill->rarity === 'unique' ? '#92400E' : '#6B7280') }}; font-family:'Nunito',sans-serif; font-size:10px; font-weight:700; padding:2px 8px; border-radius:6px;">
                                                {{ ucfirst($skill->rarity) }}
                                            </span>
                                        @endif
                                        @if ($skill->meta_tier)
                                            <span
                                                style="background:{{ in_array($skill->meta_tier, ['S', 'S+']) ? '#FFFBEB' : '#F3F4F6' }}; color:{{ in_array($skill->meta_tier, ['S', 'S+']) ? '#92400E' : '#6B7280' }}; font-family:'Nunito',sans-serif; font-size:10px; font-weight:700; padding:2px 8px; border-radius:6px;">
                                                {{ $skill->meta_tier }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Description --}}
                            @if ($skill->description)
                                <p
                                    style="font-family:'Nunito',sans-serif; font-size:12px; color:#7C6FAB; line-height:1.5; margin:0 0 10px;">
                                    {{ Str::limit($skill->description, 80) }}
                                </p>
                            @endif

                            {{-- Hint level squares --}}
                            <div style="display:flex; align-items:center; gap:6px; margin-bottom:10px;">
                                <span
                                    style="font-family:'Nunito',sans-serif; font-size:11px; color:#7C6FAB; font-weight:600;">Hint:</span>
                                @for ($i = 1; $i <= 5; $i++)
                                    <div
                                        style="width:14px; height:14px; border-radius:3px; background:{{ $i <= $hintLevel ? '#10B981' : '#EDE9FE' }};">
                                    </div>
                                @endfor
                                @if ($hintLevel > 0)
                                    <span
                                        style="font-family:'Nunito',sans-serif; font-size:10px; color:#10B981; font-weight:700;">Lv.{{ $hintLevel }}</span>
                                @endif
                            </div>

                            {{-- Cost row --}}
                            <div
                                style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
                                <div style="display:flex; align-items:center; gap:6px;">
                                    @if ($hasDiscount)
                                        <span
                                            style="font-family:'Nunito',sans-serif; font-size:12px; color:#9CA3AF; text-decoration:line-through;">{{ $skill->base_sp_cost }}</span>
                                        <span
                                            style="font-family:'Nunito',sans-serif; font-size:14px; font-weight:800; color:#10B981;">{{ $discountedCost }}</span>
                                    @else
                                        <span
                                            style="font-family:'Nunito',sans-serif; font-size:14px; font-weight:800; color:#1E1033;">{{ $skill->base_sp_cost }}</span>
                                    @endif
                                    <span
                                        style="font-family:'Nunito',sans-serif; font-size:11px; font-weight:700; color:#F59E0B;">SP</span>
                                </div>
                            </div>

                            {{-- Action button --}}
                            @if ($isOwned)
                                <button disabled
                                    style="width:100%; padding:8px; border-radius:10px; border:none; background:#D1FAE5; color:#065F46; font-family:'Nunito',sans-serif; font-size:12px; font-weight:700; cursor:default;">
                                    ✓ Owned
                                </button>
                            @elseif($isEvolvable)
                                <button wire:click="openEvolutionModal({{ $skill->id }})"
                                    style="width:100%; padding:8px; border-radius:10px; border:none; background:linear-gradient(135deg,#F59E0B,#F97316); color:#fff; font-family:'Nunito',sans-serif; font-size:12px; font-weight:700; cursor:pointer; box-shadow:0 4px 12px rgba(245,158,11,0.35);">
                                    ⬆️ Evolve
                                </button>
                            @elseif($canAfford)
                                <button wire:click="purchaseSkill({{ $skill->id }})"
                                    style="width:100%; padding:8px; border-radius:10px; border:none; background:linear-gradient(135deg,#E879A0,#7C3AED); color:#fff; font-family:'Nunito',sans-serif; font-size:12px; font-weight:700; cursor:pointer; box-shadow:0 4px 12px rgba(232,121,160,0.35);">
                                    Purchase
                                </button>
                            @else
                                <button disabled
                                    style="width:100%; padding:8px; border-radius:10px; border:1px solid #EDE9FE; background:transparent; color:#9CA3AF; font-family:'Nunito',sans-serif; font-size:12px; font-weight:700; cursor:not-allowed; opacity:0.6;">
                                    Need {{ $discountedCost - $this->spBudget['available'] }} more SP
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Evolution Modal --}}
        @if ($showEvolutionModal && $this->evolutionSkill)
            @php
                $evoSkill = $this->evolutionSkill;
                $evoTarget = $evoSkill->evolutionTarget;
                $evoTypeColor = $typeColors[$evoSkill->skill_type] ?? '#7C3AED';
                $evoHintLevel = $this->hintCounts[$evoSkill->id] ?? 0;
                $evoCost = $this->getDiscountedCost($evoSkill->id, $evoSkill->base_sp_cost);
            @endphp
            <div style="position:fixed; inset:0; background:rgba(15,10,40,0.8); backdrop-filter:blur(8px); z-index:100; display:flex; align-items:center; justify-content:center; padding:16px;"
                wire:click.self="closeEvolutionModal">
                <div
                    style="background:#fff; border-radius:24px; max-width:520px; width:100%; max-height:90vh; overflow-y:auto; box-shadow:0 24px 80px rgba(0,0,0,0.4);">

                    {{-- Modal Header --}}
                    <div
                        style="background:linear-gradient(135deg,#78350F,#92400E); padding:20px 24px; border-radius:24px 24px 0 0; display:flex; align-items:center; justify-content:space-between;">
                        <div>
                            <div
                                style="font-family:'Nunito',sans-serif; font-size:12px; color:rgba(255,255,255,0.6); margin-bottom:4px;">
                                ⬆️ SKILL EVOLUTION</div>
                            <div style="font-family:'Nunito',sans-serif; font-size:18px; font-weight:900; color:#fff;">
                                {{ $evoSkill->name }}</div>
                        </div>
                        <button wire:click="closeEvolutionModal"
                            style="background:rgba(255,255,255,0.15); border:none; color:#fff; width:32px; height:32px; border-radius:50%; cursor:pointer; font-size:18px; display:flex; align-items:center; justify-content:center;">
                            ✕
                        </button>
                    </div>

                    <div style="padding:24px;">
                        {{-- Before → After --}}
                        <div
                            style="display:grid; grid-template-columns:1fr auto 1fr; gap:12px; align-items:center; margin-bottom:20px;">
                            {{-- Before --}}
                            <div
                                style="background:#F9F5FF; border:1px solid #EDE9FE; border-radius:12px; padding:14px; text-align:center;">
                                <div
                                    style="font-family:'Nunito',sans-serif; font-size:10px; font-weight:700; color:#7C6FAB; margin-bottom:6px;">
                                    BEFORE</div>
                                <div style="font-size:24px; margin-bottom:6px;">
                                    {{ $typeEmojis[$evoSkill->skill_type] ?? '✨' }}</div>
                                <div
                                    style="font-family:'Nunito',sans-serif; font-size:13px; font-weight:800; color:#1E1033;">
                                    {{ $evoSkill->name }}</div>
                                <div
                                    style="font-family:'Nunito',sans-serif; font-size:11px; color:#7C6FAB; margin-top:4px;">
                                    {{ ucfirst($evoSkill->rarity) }}</div>
                                <div
                                    style="font-family:'Nunito',sans-serif; font-size:14px; font-weight:800; color:#F59E0B; margin-top:6px;">
                                    {{ $evoSkill->base_sp_cost }} SP</div>
                            </div>

                            <div style="font-size:24px; color:#F59E0B; font-weight:900;">→</div>

                            {{-- After --}}
                            @if ($evoTarget)
                                <div
                                    style="background:linear-gradient(135deg,#FFFBEB,#FEF3C7); border:1px solid #FCD34D; border-radius:12px; padding:14px; text-align:center;">
                                    <div
                                        style="font-family:'Nunito',sans-serif; font-size:10px; font-weight:700; color:#92400E; margin-bottom:6px;">
                                        AFTER</div>
                                    <div style="font-size:24px; margin-bottom:6px;">⭐</div>
                                    <div
                                        style="font-family:'Nunito',sans-serif; font-size:13px; font-weight:800; color:#1E1033;">
                                        {{ $evoTarget->name }}</div>
                                    <div
                                        style="font-family:'Nunito',sans-serif; font-size:11px; color:#92400E; margin-top:4px;">
                                        {{ ucfirst($evoTarget->rarity) }}</div>
                                    <div
                                        style="font-family:'Nunito',sans-serif; font-size:14px; font-weight:800; color:#10B981; margin-top:6px;">
                                        {{ $evoTarget->base_sp_cost }} SP</div>
                                </div>
                            @endif
                        </div>

                        {{-- Cost info --}}
                        <div style="background:#F9F5FF; border-radius:12px; padding:14px; margin-bottom:20px;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                                <span style="font-family:'Nunito',sans-serif; font-size:12px; color:#7C6FAB;">Hint
                                    Level</span>
                                <span
                                    style="font-family:'Nunito',sans-serif; font-size:12px; font-weight:700; color:#10B981;">Lv.{{ $evoHintLevel }}</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                                <span style="font-family:'Nunito',sans-serif; font-size:12px; color:#7C6FAB;">SP
                                    Available</span>
                                <span
                                    style="font-family:'Nunito',sans-serif; font-size:12px; font-weight:700; color:#F59E0B;">{{ number_format($this->spBudget['available']) }}</span>
                            </div>
                            <div style="display:flex; justify-content:space-between;">
                                <span style="font-family:'Nunito',sans-serif; font-size:12px; color:#7C6FAB;">Evolution
                                    Cost</span>
                                <span
                                    style="font-family:'Nunito',sans-serif; font-size:12px; font-weight:700; color:#1E1033;">{{ $evoCost }}
                                    SP</span>
                            </div>
                        </div>

                        {{-- Buttons --}}
                        <div style="display:flex; gap:10px;">
                            <button wire:click="closeEvolutionModal"
                                style="flex:1; padding:12px; border-radius:10px; border:1px solid #EDE9FE; background:transparent; color:#7C6FAB; font-family:'Nunito',sans-serif; font-size:14px; font-weight:700; cursor:pointer;">
                                Cancel
                            </button>
                            <button wire:click="confirmEvolution"
                                style="flex:2; padding:12px; border-radius:10px; border:none; background:linear-gradient(135deg,#F59E0B,#F97316); color:#fff; font-family:'Nunito',sans-serif; font-size:14px; font-weight:700; cursor:pointer; box-shadow:0 4px 12px rgba(245,158,11,0.35);">
                                ⬆️ Evolve Skill
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    @endif {{-- end character check --}}
</div>
