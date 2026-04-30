<div style="background:#F9F5FF; min-height:100vh; padding:24px; font-family:'Nunito',sans-serif;">

    {{-- Toast --}}
    @if ($toastMessage)
        <div wire:key="toast"
            style="position:fixed; top:20px; right:20px; z-index:9999; padding:14px 20px; border-radius:12px; font-size:13px; font-weight:700; box-shadow:0 8px 32px rgba(124,58,237,0.25); max-width:320px;
                {{ $toastType === 'success' ? 'background:linear-gradient(135deg,#1E1033,#3B1F6E); color:#fff;' : 'background:#FEE2E2; color:#991B1B;' }}"
            x-data x-init="setTimeout(() => $wire.dismissToast(), 3500)">
            {{ $toastMessage }}
        </div>
    @endif

    {{-- Page Header --}}
    <div
        style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 style="font-size:22px; font-weight:900; color:#1E1033; margin:0;">
                🎴 Support Cards &amp; Deck
            </h1>
            @if ($this->character)
                <p style="font-size:13px; color:#7C6FAB; margin:4px 0 0;">
                    {{ $this->character->name }} · Deck Builder
                </p>
            @endif
        </div>

        {{-- Character Selector --}}
        <select wire:model.live="characterId"
            style="font-size:13px; font-weight:600; color:#1E1033; background:#fff; border:1px solid #EDE9FE; border-radius:10px; padding:8px 14px; outline:none; cursor:pointer;"
            aria-label="Select character">
            <option value="">— Select Character —</option>
            @foreach ($this->characters as $char)
                <option value="{{ $char->id }}">{{ $char->name }}</option>
            @endforeach
        </select>
    </div>

    @if (!$this->character)
        {{-- Empty state --}}
        <div
            style="background:#fff; border:1px solid #EDE9FE; border-radius:16px; padding:48px; text-align:center; box-shadow:0 2px 12px rgba(124,58,237,0.07);">
            <div style="font-size:48px; margin-bottom:16px;">🎴</div>
            <h3 style="font-size:18px; font-weight:800; color:#1E1033; margin:0 0 8px;">No Character Selected</h3>
            <p style="font-size:13px; color:#7C6FAB; margin:0 0 20px;">Select a character above to build their support
                card deck.</p>
            <a href="{{ route('characters.index') }}"
                style="display:inline-block; background:linear-gradient(135deg,#E879A0,#7C3AED); color:#fff; font-size:14px; font-weight:700; padding:10px 24px; border-radius:10px; text-decoration:none; box-shadow:0 4px 12px rgba(232,121,160,0.35);">
                Go to Characters
            </a>
        </div>
    @else
        {{-- Overview Stats (4-column) --}}
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:16px;">
            {{-- Deck Synergy --}}
            <div
                style="background:#F5F3FF; border:1px solid #EDE9FE; border-radius:16px; padding:16px 20px; box-shadow:0 2px 12px rgba(124,58,237,0.07);">
                <div
                    style="font-size:11px; font-weight:700; color:#7C6FAB; text-transform:uppercase; letter-spacing:1px; margin-bottom:6px;">
                    Deck Synergy</div>
                <div style="font-size:28px; font-weight:900; color:#7C3AED; line-height:1;">
                    {{ number_format($this->synergyScore, 0) }}%</div>
            </div>
            {{-- Avg Bond --}}
            <div
                style="background:#FDF2F8; border:1px solid #EDE9FE; border-radius:16px; padding:16px 20px; box-shadow:0 2px 12px rgba(124,58,237,0.07);">
                <div
                    style="font-size:11px; font-weight:700; color:#7C6FAB; text-transform:uppercase; letter-spacing:1px; margin-bottom:6px;">
                    Avg Bond</div>
                <div style="font-size:28px; font-weight:900; color:#E879A0; line-height:1;">
                    {{ number_format($this->avgBond, 0) }}%</div>
            </div>
            {{-- Friendship Active --}}
            <div
                style="background:#F0FDF4; border:1px solid #EDE9FE; border-radius:16px; padding:16px 20px; box-shadow:0 2px 12px rgba(124,58,237,0.07);">
                <div
                    style="font-size:11px; font-weight:700; color:#7C6FAB; text-transform:uppercase; letter-spacing:1px; margin-bottom:6px;">
                    Friendship Active</div>
                <div style="font-size:28px; font-weight:900; color:#10B981; line-height:1;">
                    {{ $this->friendshipActiveCount }} <span style="font-size:14px; font-weight:700;">cards</span></div>
            </div>
            {{-- SSR Cards --}}
            <div
                style="background:#FFFBEB; border:1px solid #EDE9FE; border-radius:16px; padding:16px 20px; box-shadow:0 2px 12px rgba(124,58,237,0.07);">
                <div
                    style="font-size:11px; font-weight:700; color:#7C6FAB; text-transform:uppercase; letter-spacing:1px; margin-bottom:6px;">
                    SSR Cards</div>
                <div style="font-size:28px; font-weight:900; color:#F59E0B; line-height:1;">{{ $this->ssrCount }}</div>
            </div>
        </div>

        {{-- Friendship Training Banner (shown when ≥1 card at 80%+ bond) --}}
        @if ($this->friendshipActiveCount > 0)
            <div
                style="background:linear-gradient(135deg,#ECFDF5,#D1FAE5); border:1px solid #6EE7B7; border-radius:16px; padding:14px 20px; margin-bottom:16px; display:flex; align-items:center; gap:10px;">
                <span style="font-size:20px;">🤝</span>
                <div>
                    <span style="font-size:13px; font-weight:800; color:#065F46;">Friendship Training Active</span>
                    <span style="font-size:12px; color:#065F46; margin-left:8px;">
                        {{ $this->friendshipActiveCount >= 3 ? '1.35×' : '1.10×' }} training multiplier
                    </span>
                </div>
            </div>
        @endif

        {{-- Deck Validation Errors --}}
        @if (!$this->validation['is_valid'])
            <div
                style="background:#FEF2F2; border:1px solid #FECACA; border-radius:12px; padding:12px 16px; margin-bottom:16px;">
                <div style="font-size:12px; font-weight:800; color:#991B1B; margin-bottom:6px;">⚠️ Deck Issues</div>
                @foreach ($this->validation['errors'] as $error)
                    <div style="font-size:12px; color:#991B1B;">• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        @php
            $typeColors = [
                'speed' => '#E879A0',
                'stamina' => '#10B981',
                'power' => '#F59E0B',
                'guts' => '#EF4444',
                'wit' => '#3B82F6',
                'friend' => '#7C3AED',
            ];
            $typeEmojis = [
                'speed' => '⚡',
                'stamina' => '🌿',
                'power' => '🔥',
                'guts' => '❤️',
                'wit' => '💙',
                'friend' => '🤝',
            ];
        @endphp

        {{-- Deck Grid (3×2 = 6 slots) --}}
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:20px;">
            @foreach ($this->deckSlots as $slot => $card)
                @php
                    $isFriendSlot = $slot === 6;
                    $cardType = $card?->supportCard?->card_type ?? null;
                    $typeColor = $typeColors[$cardType] ?? '#7C3AED';
                    $typeEmoji = $typeEmojis[$cardType] ?? '🎴';
                    $rarity = $card?->supportCard?->rarity ?? null;
                    $limitBreak = $card?->limit_break_level ?? 0;
                    $bondLevel = $card?->friendship_level ?? 0;
                    $friendshipActive = $bondLevel >= 80;
                    $bondColor = $friendshipActive ? '#10B981' : $typeColor;
                    $metaTier = $card?->supportCard?->meta_tier ?? null;
                @endphp

                <div wire:key="slot-{{ $slot }}"
                    style="background:#fff; border-radius:16px; border:2px solid {{ $isFriendSlot ? '#E879A0' : '#EDE9FE' }}; box-shadow:0 2px 12px rgba(124,58,237,0.07); overflow:hidden; transition:box-shadow 0.15s;">

                    {{-- Card Header --}}
                    <div
                        style="background:linear-gradient(135deg,{{ $card ? $typeColor . '22' : '#F9F5FF' }},{{ $card ? $typeColor . '44' : '#EDE9FE' }}); padding:12px 14px; display:flex; align-items:center; justify-content:space-between;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span
                                style="font-size:16px;">{{ $card ? $typeEmoji : ($isFriendSlot ? '🤝' : '🎴') }}</span>
                            <div>
                                <div
                                    style="font-size:10px; font-weight:700; color:{{ $card ? $typeColor : '#7C6FAB' }}; text-transform:uppercase; letter-spacing:0.5px;">
                                    {{ $card ? ucfirst($cardType ?? 'card') : ($isFriendSlot ? 'Friends Slot' : 'Slot ' . $slot) }}
                                </div>
                                @if ($card)
                                    <div style="font-size:12px; font-weight:800; color:#1E1033; margin-top:1px;">
                                        {{ Str::limit($card->supportCard?->name ?? 'Unknown', 20) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div style="display:flex; gap:4px; align-items:center;">
                            @if ($rarity)
                                <span
                                    style="font-size:9px; font-weight:800; padding:2px 7px; border-radius:6px; color:#fff;
                                    {{ $rarity === 'SSR' ? 'background:linear-gradient(135deg,#F59E0B,#F97316);' : ($rarity === 'SR' ? 'background:linear-gradient(135deg,#7C3AED,#A855F7);' : 'background:#E5E7EB; color:#374151;') }}">
                                    {{ $rarity }}
                                </span>
                            @endif
                            @if ($limitBreak > 0)
                                <span
                                    style="font-size:9px; font-weight:800; background:#FFFBEB; color:#92400E; padding:2px 6px; border-radius:6px;">
                                    ★{{ $limitBreak }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div style="padding:12px 14px;">
                        @if ($card)
                            {{-- Bond Bar --}}
                            <div style="margin-bottom:10px;">
                                <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                                    <span style="font-size:10px; font-weight:700; color:#7C6FAB;">Bond</span>
                                    <span
                                        style="font-size:10px; font-weight:700; color:{{ $bondColor }};">{{ $bondLevel }}%{{ $friendshipActive ? ' 🤝' : '' }}</span>
                                </div>
                                <div style="background:#EDE9FE; border-radius:99px; height:6px; overflow:hidden;">
                                    <div
                                        style="height:6px; border-radius:99px; background:{{ $bondColor }}; width:{{ $bondLevel }}%; transition:width 0.4s ease;">
                                    </div>
                                </div>
                            </div>

                            {{-- Limit Break Segments --}}
                            <div style="display:flex; gap:3px; margin-bottom:12px;">
                                @for ($lb = 0; $lb < 4; $lb++)
                                    <div
                                        style="flex:1; height:4px; border-radius:2px; background:{{ $lb < $limitBreak ? $typeColor : '#EDE9FE' }};">
                                    </div>
                                @endfor
                            </div>

                            {{-- Actions --}}
                            <div style="display:flex; gap:6px;">
                                <button wire:click="openLimitBreakModal({{ $slot }})"
                                    style="flex:1; padding:6px 8px; border-radius:8px; border:1px solid {{ $typeColor }}44; background:{{ $typeColor }}11; color:{{ $typeColor }}; font-size:11px; font-weight:700; cursor:pointer;"
                                    data-testid="deck-limit-break-slot-{{ $slot }}">
                                    {{ $limitBreak >= 4 ? '⭐ Max LB' : '⬆️ Limit Break' }}
                                </button>
                                <button wire:click="removeCard({{ $slot }})"
                                    style="padding:6px 10px; border-radius:8px; border:1px solid #FECACA; background:#FEF2F2; color:#EF4444; font-size:11px; font-weight:700; cursor:pointer;"
                                    data-testid="deck-remove-slot-{{ $slot }}"
                                    aria-label="Remove card from slot {{ $slot }}">
                                    ✕
                                </button>
                            </div>
                        @else
                            {{-- Empty slot --}}
                            <div style="text-align:center; padding:12px 0;">
                                <div style="font-size:28px; margin-bottom:8px; opacity:0.4;">
                                    {{ $isFriendSlot ? '🤝' : '+' }}</div>
                                <p style="font-size:11px; color:#7C6FAB; margin:0 0 10px;">
                                    {{ $isFriendSlot ? 'Borrow a friend\'s card' : 'Add a support card' }}
                                </p>
                                <button wire:click="openCardPicker({{ $slot }})"
                                    style="padding:7px 16px; border-radius:10px; border:none; background:linear-gradient(135deg,#E879A0,#7C3AED); color:#fff; font-size:12px; font-weight:700; cursor:pointer; box-shadow:0 4px 12px rgba(232,121,160,0.25);"
                                    data-testid="deck-add-slot-{{ $slot }}">
                                    {{ $isFriendSlot ? '+ Borrow' : '+ Add Card' }}
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Card Picker Modal --}}
        @if ($showCardPicker)
            <div style="position:fixed; inset:0; background:rgba(15,10,40,0.8); backdrop-filter:blur(8px); z-index:100; display:flex; align-items:center; justify-content:center; padding:16px;"
                wire:click.self="closeCardPicker">
                <div style="background:#fff; border-radius:24px; max-width:600px; width:100%; max-height:85vh; display:flex; flex-direction:column; box-shadow:0 24px 80px rgba(0,0,0,0.4);"
                    role="dialog" aria-modal="true" aria-label="Select a support card">

                    {{-- Modal Header --}}
                    <div
                        style="background:linear-gradient(135deg,#1E1033,#3B1F6E); padding:20px 24px; border-radius:24px 24px 0 0; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
                        <div>
                            <div style="font-size:12px; color:rgba(255,255,255,0.5); margin-bottom:4px;">🎴 SELECT CARD
                            </div>
                            <div style="font-size:18px; font-weight:900; color:#fff;">
                                {{ $pickerSlot === 6 ? 'Borrow Friend\'s Card' : 'Add to Slot ' . $pickerSlot }}
                            </div>
                        </div>
                        <button wire:click="closeCardPicker"
                            style="background:rgba(255,255,255,0.15); border:none; color:#fff; width:32px; height:32px; border-radius:50%; cursor:pointer; font-size:18px; display:flex; align-items:center; justify-content:center;"
                            aria-label="Close card picker">✕</button>
                    </div>

                    {{-- Search & Filter --}}
                    <div style="padding:16px 20px; border-bottom:1px solid #EDE9FE; flex-shrink:0;">
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <div style="position:relative; flex:1; min-width:160px;">
                                <span
                                    style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#C4B5FD;">🔍</span>
                                <input wire:model.live.debounce.200ms="pickerSearch" type="text"
                                    placeholder="Search cards..."
                                    style="width:100%; padding:8px 10px 8px 32px; background:#F9F5FF; border:1px solid #EDE9FE; border-radius:10px; font-size:13px; color:#1E1033; outline:none; box-sizing:border-box;"
                                    aria-label="Search support cards" />
                            </div>
                            <select wire:model.live="pickerTypeFilter"
                                style="padding:8px 12px; background:#F9F5FF; border:1px solid #EDE9FE; border-radius:10px; font-size:13px; color:#1E1033; outline:none; cursor:pointer;">
                                <option value="">All Types</option>
                                @foreach (['speed' => '⚡ Speed', 'stamina' => '🌿 Stamina', 'power' => '🔥 Power', 'guts' => '❤️ Guts', 'wit' => '💙 Wit', 'friend' => '🤝 Friend'] as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Card List --}}
                    <div style="flex:1; overflow-y:auto; padding:12px 16px;">
                        @forelse ($this->availableCards as $availCard)
                            @php
                                $aType = $availCard->card_type ?? 'friend';
                                $aColor = $typeColors[$aType] ?? '#7C3AED';
                                $aEmoji = $typeEmojis[$aType] ?? '🎴';
                                $aRarity = $availCard->rarity ?? 'R';
                            @endphp
                            <button wire:click="selectCard({{ $availCard->id }})"
                                style="width:100%; display:flex; align-items:center; gap:12px; padding:10px 12px; border-radius:12px; border:1px solid #EDE9FE; background:#F9F5FF; margin-bottom:6px; cursor:pointer; text-align:left; transition:background 0.1s;"
                                onmouseover="this.style.background='#EDE9FE'"
                                onmouseout="this.style.background='#F9F5FF'"
                                data-testid="picker-card-{{ $availCard->id }}">
                                {{-- Type icon --}}
                                <div
                                    style="width:36px; height:36px; border-radius:10px; background:{{ $aColor }}18; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0;">
                                    {{ $aEmoji }}
                                </div>
                                {{-- Card info --}}
                                <div style="flex:1; min-width:0;">
                                    <div
                                        style="font-size:13px; font-weight:800; color:#1E1033; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                        {{ $availCard->name }}
                                    </div>
                                    <div style="display:flex; gap:4px; margin-top:2px; flex-wrap:wrap;">
                                        <span
                                            style="font-size:10px; font-weight:700; color:{{ $aColor }}; background:{{ $aColor }}18; padding:1px 6px; border-radius:4px;">
                                            {{ ucfirst($aType) }}
                                        </span>
                                        @if ($availCard->meta_tier)
                                            <span
                                                style="font-size:10px; font-weight:700; color:#92400E; background:#FFFBEB; padding:1px 6px; border-radius:4px;">
                                                {{ $availCard->meta_tier }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                {{-- Rarity badge --}}
                                <span
                                    style="font-size:9px; font-weight:800; padding:3px 8px; border-radius:6px; flex-shrink:0; color:#fff;
                                    {{ $aRarity === 'SSR' ? 'background:linear-gradient(135deg,#F59E0B,#F97316);' : ($aRarity === 'SR' ? 'background:linear-gradient(135deg,#7C3AED,#A855F7);' : 'background:#E5E7EB; color:#374151;') }}">
                                    {{ $aRarity }}
                                </span>
                            </button>
                        @empty
                            <div style="text-align:center; padding:32px; color:#7C6FAB; font-size:13px;">
                                No cards found matching your search.
                            </div>
                        @endforelse
                    </div>

                    {{-- Footer --}}
                    <div style="padding:14px 20px; border-top:1px solid #EDE9FE; flex-shrink:0; text-align:right;">
                        <button wire:click="closeCardPicker"
                            style="padding:9px 20px; border-radius:10px; border:1px solid #EDE9FE; background:transparent; color:#7C6FAB; font-size:13px; font-weight:700; cursor:pointer;">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Limit Break Modal --}}
        @if ($showLimitBreakModal && $this->limitBreakSlot)
            @php
                $lbCard = $this->deckSlots[$this->limitBreakSlot] ?? null;
                $lbType = $lbCard?->supportCard?->card_type ?? 'speed';
                $lbColor = $typeColors[$lbType] ?? '#7C3AED';
            @endphp
            <div style="position:fixed; inset:0; background:rgba(15,10,40,0.8); backdrop-filter:blur(8px); z-index:100; display:flex; align-items:center; justify-content:center; padding:16px;"
                wire:click.self="closeLimitBreakModal">
                <div style="background:#fff; border-radius:24px; max-width:400px; width:100%; box-shadow:0 24px 80px rgba(0,0,0,0.4);"
                    role="dialog" aria-modal="true" aria-label="Limit Break">

                    {{-- Header --}}
                    <div
                        style="background:linear-gradient(135deg,#1E1033,#3B1F6E); padding:20px 24px; border-radius:24px 24px 0 0; display:flex; align-items:center; justify-content:space-between;">
                        <div>
                            <div style="font-size:12px; color:rgba(255,255,255,0.5); margin-bottom:4px;">⬆️ LIMIT BREAK
                            </div>
                            <div style="font-size:18px; font-weight:900; color:#fff;">
                                {{ Str::limit($lbCard?->supportCard?->name ?? 'Card', 24) }}
                            </div>
                        </div>
                        <button wire:click="closeLimitBreakModal"
                            style="background:rgba(255,255,255,0.15); border:none; color:#fff; width:32px; height:32px; border-radius:50%; cursor:pointer; font-size:18px; display:flex; align-items:center; justify-content:center;"
                            aria-label="Close limit break modal">✕</button>
                    </div>

                    <div style="padding:24px;">
                        {{-- Level selector --}}
                        <div style="margin-bottom:20px;">
                            <div style="font-size:13px; font-weight:700; color:#7C6FAB; margin-bottom:12px;">Select
                                Limit Break Level</div>
                            <div style="display:flex; gap:8px; justify-content:center;">
                                @for ($lb = 0; $lb <= 4; $lb++)
                                    <button wire:click="$set('newLimitBreakLevel', {{ $lb }})"
                                        style="width:48px; height:48px; border-radius:12px; border:2px solid {{ $newLimitBreakLevel === $lb ? $lbColor : '#EDE9FE' }}; background:{{ $newLimitBreakLevel === $lb ? $lbColor . '18' : '#F9F5FF' }}; color:{{ $newLimitBreakLevel === $lb ? $lbColor : '#7C6FAB' }}; font-size:16px; font-weight:900; cursor:pointer; transition:all 0.15s;"
                                        data-testid="lb-level-{{ $lb }}">
                                        {{ $lb === 0 ? '0' : str_repeat('★', $lb) }}
                                    </button>
                                @endfor
                            </div>
                        </div>

                        {{-- LB progress bar --}}
                        <div style="display:flex; gap:4px; margin-bottom:20px;">
                            @for ($lb = 0; $lb < 4; $lb++)
                                <div
                                    style="flex:1; height:6px; border-radius:3px; background:{{ $lb < $newLimitBreakLevel ? $lbColor : '#EDE9FE' }}; transition:background 0.2s;">
                                </div>
                            @endfor
                        </div>

                        {{-- Buttons --}}
                        <div style="display:flex; gap:10px;">
                            <button wire:click="closeLimitBreakModal"
                                style="flex:1; padding:11px; border-radius:10px; border:1px solid #EDE9FE; background:transparent; color:#7C6FAB; font-size:13px; font-weight:700; cursor:pointer;">
                                Cancel
                            </button>
                            <button wire:click="confirmLimitBreak"
                                style="flex:2; padding:11px; border-radius:10px; border:none; background:linear-gradient(135deg,#E879A0,#7C3AED); color:#fff; font-size:13px; font-weight:700; cursor:pointer; box-shadow:0 4px 12px rgba(232,121,160,0.35);"
                                data-testid="deck-confirm-limit-break">
                                ⬆️ Confirm Limit Break
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    @endif {{-- end character check --}}
</div>
