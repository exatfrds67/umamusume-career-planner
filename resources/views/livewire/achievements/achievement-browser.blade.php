<div>
    {{-- Page Header --}}
    <div
        style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 style="font-size:22px; font-weight:900; color:#1E1033; margin:0 0 4px;">🏅 Achievements</h1>
            <p style="font-size:13px; color:#7C6FAB; margin:0;">Track your milestones and accomplishments.</p>
        </div>
    </div>

    {{-- 4-column summary cards --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:14px; margin-bottom:24px;">
        <div style="background:#F5F3FF; border:1px solid #C4B5FD; border-radius:16px; padding:16px 18px;">
            <div style="font-size:28px; font-weight:900; color:#7C3AED;">{{ $summary['unlocked'] }}<span
                    style="font-size:14px; color:#7C6FAB;">/{{ $summary['total'] }}</span></div>
            <div
                style="font-size:11px; font-weight:800; color:#7C6FAB; text-transform:uppercase; letter-spacing:1px; margin-top:4px;">
                Unlocked</div>
        </div>
        <div
            style="background:linear-gradient(135deg,#FFFBEB,#FEF3C7); border:1px solid #FCD34D; border-radius:16px; padding:16px 18px;">
            <div style="font-size:28px; font-weight:900; color:#F59E0B;">{{ $summary['percent'] }}%</div>
            <div
                style="font-size:11px; font-weight:800; color:#92400E; text-transform:uppercase; letter-spacing:1px; margin-top:4px;">
                Complete</div>
        </div>
        <div style="background:#F0FDF4; border:1px solid #6EE7B7; border-radius:16px; padding:16px 18px;">
            <div style="font-size:28px; font-weight:900; color:#10B981;">{{ $summary['by_rarity']['legendary'] ?? 0 }}
            </div>
            <div
                style="font-size:11px; font-weight:800; color:#065F46; text-transform:uppercase; letter-spacing:1px; margin-top:4px;">
                Legendary</div>
        </div>
        <div style="background:#FDF2F8; border:1px solid #F9A8D4; border-radius:16px; padding:16px 18px;">
            <div style="font-size:28px; font-weight:900; color:#E879A0;">{{ $summary['total'] }}</div>
            <div
                style="font-size:11px; font-weight:800; color:#9D174D; text-transform:uppercase; letter-spacing:1px; margin-top:4px;">
                Total</div>
        </div>
    </div>

    {{-- Overall progress bar --}}
    <div style="background:#fff; border:1px solid #EDE9FE; border-radius:16px; padding:16px 20px; margin-bottom:20px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <span style="font-size:13px; font-weight:700; color:#1E1033;">Overall Progress</span>
            <span style="font-size:13px; font-weight:800; color:#7C3AED;">{{ $summary['unlocked'] }} /
                {{ $summary['total'] }}</span>
        </div>
        <div style="height:10px; background:#EDE9FE; border-radius:99px; overflow:hidden;">
            <div
                style="height:100%; width:{{ $summary['percent'] }}%; background:linear-gradient(90deg,#E879A0,#7C3AED); border-radius:99px; transition:width 0.6s cubic-bezier(.4,0,.2,1);">
            </div>
        </div>
        @if (!empty($summary['by_rarity']))
            <div style="display:flex; gap:12px; margin-top:10px; flex-wrap:wrap;">
                @foreach (['legendary' => ['#F59E0B', '⭐'], 'epic' => ['#7C3AED', '💎'], 'rare' => ['#3B82F6', '🔷'], 'common' => ['#10B981', '✅']] as $rarity => [$color, $icon])
                    @if (isset($summary['by_rarity'][$rarity]) && $summary['by_rarity'][$rarity] > 0)
                        <span style="font-size:11px; font-weight:700; color:{{ $color }};">
                            {{ $icon }} {{ ucfirst($rarity) }}: {{ $summary['by_rarity'][$rarity] }}
                        </span>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    {{-- Fan Class Progression Strip --}}
    @php
        $fanTiers = [
            ['name' => 'Debut', 'fans' => 0, 'icon' => '🌱'],
            ['name' => 'Bronze', 'fans' => 5000, 'icon' => '🥉'],
            ['name' => 'Silver', 'fans' => 20000, 'icon' => '🥈'],
            ['name' => 'Gold', 'fans' => 50000, 'icon' => '🥇'],
            ['name' => 'Platinum', 'fans' => 100000, 'icon' => '💎'],
            ['name' => 'Star', 'fans' => 160000, 'icon' => '⭐'],
            ['name' => 'Top Star', 'fans' => 240000, 'icon' => '🌟'],
            ['name' => 'Legend', 'fans' => 320000, 'icon' => '👑'],
        ];
        $currentFans = $summary['total_fans'] ?? 0;
        $currentTierIdx = 0;
        foreach ($fanTiers as $idx => $tier) {
            if ($currentFans >= $tier['fans']) {
                $currentTierIdx = $idx;
            }
        }
        $nextTier = $fanTiers[$currentTierIdx + 1] ?? null;
        $fansRemaining = $nextTier ? max(0, $nextTier['fans'] - $currentFans) : 0;
    @endphp
    <div
        style="background:linear-gradient(135deg,#ECFDF5,#D1FAE5); border:1px solid #6EE7B7; border-radius:16px; padding:18px 20px; margin-bottom:24px;">
        <div
            style="font-size:12px; font-weight:800; color:#065F46; text-transform:uppercase; letter-spacing:1px; margin-bottom:14px;">
            🏆 Fan Class Progression</div>
        <div style="display:flex; align-items:center; gap:0; overflow-x:auto; padding-bottom:4px;">
            @foreach ($fanTiers as $idx => $tier)
                @php
                    $reached = $currentFans >= $tier['fans'];
                    $isCurrent = $idx === $currentTierIdx;
                    $isNext = $idx === $currentTierIdx + 1;
                @endphp
                {{-- Tier circle --}}
                <div style="display:flex; flex-direction:column; align-items:center; flex-shrink:0;">
                    <div style="
                        width:36px; height:36px; border-radius:50%;
                        display:flex; align-items:center; justify-content:center;
                        font-size:14px;
                        {{ $reached ? 'background:linear-gradient(135deg,#F59E0B,#F97316); color:#fff; box-shadow:0 2px 8px rgba(245,158,11,0.4);' : ($isNext ? 'background:#fff; border:2px dashed #7C3AED; color:#7C3AED;' : 'background:#E5E7EB; color:#9CA3AF;') }}
                    "
                        title="{{ $tier['name'] }} — {{ number_format($tier['fans']) }} fans">
                        {{ $reached ? '✓' : $tier['icon'] }}
                    </div>
                    <div
                        style="font-size:9px; font-weight:700; margin-top:4px; white-space:nowrap; color:{{ $reached ? '#065F46' : ($isNext ? '#7C3AED' : '#9CA3AF') }};">
                        {{ $tier['name'] }}
                    </div>
                </div>
                {{-- Connector line (not after last) --}}
                @if (!$loop->last)
                    <div
                        style="
                        width:28px; height:3px; flex-shrink:0; margin-bottom:18px;
                        background:{{ $reached && isset($fanTiers[$idx + 1]) && $currentFans >= $fanTiers[$idx + 1]['fans'] ? '#F59E0B' : '#E5E7EB' }};
                        border-radius:99px;
                    ">
                    </div>
                @endif
            @endforeach
        </div>
        @if ($nextTier)
            <div style="font-size:12px; color:#065F46; margin-top:10px; font-weight:600;">
                Next: <strong>{{ $nextTier['name'] }}</strong> at {{ number_format($nextTier['fans']) }} fans
                · <span style="color:#10B981;">{{ number_format($fansRemaining) }} more needed</span>
            </div>
        @else
            <div style="font-size:12px; color:#065F46; margin-top:10px; font-weight:700;">🎉 Maximum fan class reached —
                Legend!</div>
        @endif
    </div>

    {{-- Search + Category filters --}}
    <div style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap; align-items:center;">
        {{-- Search --}}
        <div style="position:relative; flex:1; min-width:200px;">
            <span
                style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#7C6FAB; font-size:14px;">🔍</span>
            <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Search achievements..."
                data-testid="achievement-search"
                style="width:100%; padding:9px 14px 9px 36px; border:1px solid #EDE9FE; border-radius:10px; font-size:13px; color:#1E1033; background:#F9F5FF; box-sizing:border-box; outline:none;">
        </div>

        {{-- Category buttons --}}
        <div style="display:flex; gap:6px; flex-wrap:wrap;">
            @foreach ($categoryLabels as $cat => $label)
                @php
                    $catColors = [
                        'all' => 'linear-gradient(135deg,#E879A0,#7C3AED)',
                        'racing' => 'linear-gradient(135deg,#F59E0B,#F97316)',
                        'training' => 'linear-gradient(135deg,#E879A0,#EC4899)',
                        'skills' => 'linear-gradient(135deg,#7C3AED,#A855F7)',
                        'career' => 'linear-gradient(135deg,#3B82F6,#6366F1)',
                        'collection' => 'linear-gradient(135deg,#10B981,#059669)',
                    ];
                    $activeBg = $catColors[$cat] ?? 'linear-gradient(135deg,#E879A0,#7C3AED)';
                @endphp
                <button wire:click="setCategory('{{ $cat }}')"
                    data-testid="achievement-filter-{{ $cat }}"
                    style="
                        padding:7px 14px; border-radius:10px; font-size:12px; font-weight:700;
                        border:none; cursor:pointer; transition:all 0.15s;
                        {{ $activeCategory === $cat ? 'background:' . $activeBg . '; color:#fff;' : 'background:#EDE9FE; color:#7C6FAB;' }}
                    ">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Achievement grid --}}
    @if ($achievements->isEmpty())
        <div style="text-align:center; padding:48px 20px; color:#7C6FAB;">
            <p style="font-size:32px; margin-bottom:8px;">🔍</p>
            <p style="font-size:14px; font-weight:700; color:#1E1033; margin-bottom:4px;">No achievements found</p>
            <p style="font-size:12px;">Try a different category or search term.</p>
        </div>
    @else
        <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:14px;">
            @foreach ($achievements as $achievement)
                @php
                    $rarityStyles = $achievement->rarity_style;
                    $progressPct = $achievement->progress_percent;
                    $isUnlocked = $achievement->is_unlocked;
                    $catColor =
                        [
                            'racing' => '#F59E0B',
                            'training' => '#E879A0',
                            'skills' => '#7C3AED',
                            'career' => '#3B82F6',
                            'collection' => '#10B981',
                        ][$achievement->category] ?? '#7C3AED';
                @endphp
                <div data-testid="achievement-card-{{ $achievement->key }}"
                    style="
                        background:{{ $isUnlocked ? $catColor . '0a' : '#F9F5FF' }};
                        border:1px solid {{ $isUnlocked ? $rarityStyles['border'] : '#EDE9FE' }};
                        border-radius:16px; overflow:hidden;
                        {{ $isUnlocked ? '' : 'opacity:0.75;' }}
                        transition:all 0.15s; position:relative;
                    ">
                    {{-- Colored top stripe for unlocked --}}
                    @if ($isUnlocked)
                        <div style="height:3px; background:{{ $rarityStyles['bg'] }};"></div>
                    @endif

                    {{-- Unlocked checkmark --}}
                    @if ($isUnlocked)
                        <div
                            style="position:absolute; top:12px; right:12px; width:20px; height:20px; border-radius:50%; background:#10B981; display:flex; align-items:center; justify-content:center; font-size:10px; color:#fff; font-weight:900;">
                            ✓</div>
                    @endif

                    <div style="padding:16px;">
                        <div style="display:flex; align-items:flex-start; gap:12px;">
                            {{-- Icon --}}
                            <div
                                style="
                                width:46px; height:46px; border-radius:12px; flex-shrink:0;
                                background:{{ $isUnlocked ? $rarityStyles['bg'] : '#EDE9FE' }};
                                display:flex; align-items:center; justify-content:center; font-size:22px;
                                {{ $isUnlocked ? '' : 'filter:grayscale(1);' }}
                            ">
                                {{ $achievement->icon }}</div>

                            {{-- Info --}}
                            <div style="flex:1; min-width:0;">
                                <div
                                    style="display:flex; align-items:center; gap:6px; margin-bottom:3px; flex-wrap:wrap;">
                                    <span
                                        style="font-size:14px; font-weight:800; color:{{ $isUnlocked ? '#1E1033' : '#7C6FAB' }};">{{ $achievement->title }}</span>
                                    <span
                                        style="font-size:9px; font-weight:800; padding:2px 7px; border-radius:20px; background:{{ $rarityStyles['bg'] }}; color:{{ $rarityStyles['color'] }}; text-transform:uppercase; letter-spacing:0.5px;">{{ $achievement->rarity }}</span>
                                </div>
                                <p style="font-size:12px; color:#7C6FAB; margin:0 0 8px; line-height:1.4;">
                                    {{ $achievement->description }}</p>

                                {{-- Bottom row: category + reward --}}
                                <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                    <span
                                        style="font-size:10px; font-weight:700; padding:2px 8px; border-radius:20px; background:{{ $catColor }}18; color:{{ $catColor }};">{{ ucfirst($achievement->category) }}</span>

                                    @if ($isUnlocked)
                                        <span
                                            style="font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; background:#D1FAE5; color:#065F46;">✅
                                            Unlocked</span>
                                        @if ($achievement->unlocked_at)
                                            <span
                                                style="font-size:10px; color:#7C6FAB;">{{ $achievement->unlocked_at->diffForHumans() }}</span>
                                        @endif
                                    @else
                                        @if ($achievement->target > 1)
                                            <div style="flex:1; min-width:120px;">
                                                <div
                                                    style="display:flex; justify-content:space-between; margin-bottom:3px;">
                                                    <span
                                                        style="font-size:10px; color:#7C6FAB;">{{ number_format($achievement->progress) }}/{{ number_format($achievement->target) }}</span>
                                                    <span
                                                        style="font-size:10px; font-weight:700; color:#7C3AED;">{{ $progressPct }}%</span>
                                                </div>
                                                <div
                                                    style="height:6px; background:#EDE9FE; border-radius:99px; overflow:hidden;">
                                                    <div
                                                        style="height:100%; width:{{ $progressPct }}%; background:linear-gradient(90deg,#E879A0,#7C3AED); border-radius:99px; transition:width 0.6s cubic-bezier(.4,0,.2,1);">
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span
                                                style="font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; background:#F3F4F6; color:#6B7280;">🔒
                                                Locked</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
