<div>
    {{-- Status message --}}
    @if ($statusMessage)
        <div style="padding:10px 16px; border-radius:10px; margin-bottom:16px; font-size:13px; font-weight:700;
                background:{{ $statusType === 'success' ? '#D1FAE5' : '#FEE2E2' }};
                color:{{ $statusType === 'success' ? '#065F46' : '#991B1B' }};
                border:1px solid {{ $statusType === 'success' ? '#6EE7B7' : '#FCA5A5' }};"
            role="alert" aria-live="polite">
            {{ $statusType === 'success' ? '✅' : '❌' }} {{ $statusMessage }}
        </div>
    @endif

    {{-- Header row --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
        <div>
            <h2 style="font-size:15px; font-weight:800; color:#1E1033; margin:0 0 4px;">📸 Run Snapshots</h2>
            <p style="font-size:12px; color:#7C6FAB; margin:0;">Save and restore career states at any point in your run.
            </p>
        </div>
        <div style="display:flex; gap:8px;">
            @if ($snapshots->count() >= 2)
                <button wire:click="openCompareModal" data-testid="snapshot-compare-btn"
                    style="padding:8px 14px; border-radius:10px; font-size:12px; font-weight:700; background:#EDE9FE; color:#7C3AED; border:none; cursor:pointer;">
                    ⚖️ Compare
                </button>
            @endif
            @if ($snapshots->count() > 20)
                <button wire:click="cleanupOldSnapshots" data-testid="snapshot-cleanup-btn"
                    style="padding:8px 14px; border-radius:10px; font-size:12px; font-weight:700; background:#EDE9FE; color:#7C3AED; border:none; cursor:pointer;">
                    🧹 Clean Up
                </button>
            @endif
            @if ($careerId)
                <button wire:click="openCreateModal" data-testid="snapshot-create-btn"
                    style="padding:8px 16px; border-radius:10px; font-size:13px; font-weight:700; background:linear-gradient(135deg,#E879A0,#7C3AED); color:#fff; border:none; cursor:pointer; box-shadow:0 4px 12px rgba(232,121,160,.35);">
                    + New Snapshot
                </button>
            @endif
        </div>
    </div>

    {{-- No career state --}}
    @if (!$careerId)
        <div style="text-align:center; padding:40px 20px; color:#7C6FAB;">
            <p style="font-size:32px; margin-bottom:8px;">📸</p>
            <p style="font-size:14px; font-weight:700; color:#1E1033; margin-bottom:4px;">No Active Career</p>
            <p style="font-size:12px;">Start a career run to use snapshots.</p>
        </div>

        {{-- Empty snapshots --}}
    @elseif($snapshots->isEmpty())
        <div style="text-align:center; padding:40px 20px; color:#7C6FAB;">
            <p style="font-size:32px; margin-bottom:8px;">📸</p>
            <p style="font-size:14px; font-weight:700; color:#1E1033; margin-bottom:4px;">No Snapshots Yet</p>
            <p style="font-size:12px; margin-bottom:16px;">Create a snapshot to save your current career state.</p>
            <button wire:click="openCreateModal" data-testid="snapshot-create-empty-btn"
                style="padding:10px 20px; border-radius:10px; font-size:13px; font-weight:700; background:linear-gradient(135deg,#E879A0,#7C3AED); color:#fff; border:none; cursor:pointer;">
                + Create First Snapshot
            </button>
        </div>

        {{-- Snapshot list --}}
    @else
        <div style="display:flex; flex-direction:column; gap:10px;">
            @foreach ($snapshots as $snapshot)
                @php
                    $triggerColors = [
                        'manual' => ['bg' => '#EDE9FE', 'color' => '#7C3AED', 'label' => 'Manual'],
                        'auto_pre_restore' => [
                            'bg' => '#FEF3C7',
                            'color' => '#92400E',
                            'label' => 'Auto (Pre-Restore)',
                        ],
                        'auto' => ['bg' => '#D1FAE5', 'color' => '#065F46', 'label' => 'Auto'],
                        'race' => ['bg' => '#FDF2F8', 'color' => '#9D174D', 'label' => 'Race'],
                        'phase' => ['bg' => '#EFF6FF', 'color' => '#1E40AF', 'label' => 'Phase'],
                    ];
                    $tc = $triggerColors[$snapshot->trigger_type] ?? [
                        'bg' => '#F3F4F6',
                        'color' => '#374151',
                        'label' => ucfirst($snapshot->trigger_type ?? 'Unknown'),
                    ];
                    $stats = $snapshot->snapshot_data['stats'] ?? [];
                    $sp = $snapshot->snapshot_data['sp'] ?? [];
                    $spAvail = ($sp['total_earned'] ?? 0) - ($sp['total_spent'] ?? 0);
                    $statColors = [
                        'speed' => '#E879A0',
                        'stamina' => '#10B981',
                        'power' => '#F59E0B',
                        'guts' => '#EF4444',
                        'wit' => '#3B82F6',
                    ];
                    $statIcons = ['speed' => '⚡', 'stamina' => '🌿', 'power' => '🔥', 'guts' => '❤️', 'wit' => '💙'];
                @endphp
                <div style="background:#fff; border:1px solid #EDE9FE; border-radius:14px; padding:14px 18px;"
                    data-testid="snapshot-row-{{ $snapshot->id }}">
                    <div style="display:flex; align-items:center; gap:14px;">
                        {{-- Turn badge --}}
                        <div
                            style="width:48px; height:48px; border-radius:12px; background:linear-gradient(135deg,#E879A0,#7C3AED); display:flex; flex-direction:column; align-items:center; justify-content:center; flex-shrink:0; color:#fff;">
                            <span style="font-size:9px; font-weight:800; opacity:.8;">TURN</span>
                            <span
                                style="font-size:16px; font-weight:900; line-height:1;">{{ $snapshot->turn_number ?? '?' }}</span>
                        </div>

                        {{-- Info --}}
                        <div style="flex:1; min-width:0;">
                            <div style="display:flex; align-items:center; gap:8px; margin-bottom:3px; flex-wrap:wrap;">
                                <span style="font-size:13px; font-weight:800; color:#1E1033;">
                                    {{ $snapshot->description ?: 'Snapshot — Turn ' . ($snapshot->turn_number ?? '?') }}
                                </span>
                                <span
                                    style="font-size:10px; font-weight:700; padding:2px 8px; border-radius:20px; background:{{ $tc['bg'] }}; color:{{ $tc['color'] }};">{{ $tc['label'] }}</span>
                            </div>
                            <div style="font-size:11px; color:#7C6FAB; margin-bottom:6px;">
                                {{ $snapshot->created_at?->diffForHumans() ?? 'Unknown time' }}
                                @if ($snapshot->checksum)
                                    · <span
                                        style="font-family:monospace; font-size:10px; color:#C4B5FD;">{{ substr($snapshot->checksum, 0, 8) }}…</span>
                                @endif
                            </div>
                            {{-- Mini stats row --}}
                            @if (!empty($stats))
                                <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:4px;">
                                    @foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat)
                                        @if (isset($stats[$stat]))
                                            <span
                                                style="font-size:10px; font-weight:700; padding:2px 7px; border-radius:20px; background:{{ $statColors[$stat] }}18; color:{{ $statColors[$stat] }};">
                                                {{ $statIcons[$stat] }} {{ $stats[$stat] }}
                                            </span>
                                        @endif
                                    @endforeach
                                    @if ($spAvail > 0)
                                        <span
                                            style="font-size:10px; font-weight:700; padding:2px 7px; border-radius:20px; background:#FFFBEB; color:#F59E0B;">✨
                                            {{ $spAvail }} SP</span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div style="display:flex; gap:6px; flex-shrink:0;">
                            <button wire:click="confirmRestore({{ $snapshot->id }})"
                                data-testid="snapshot-restore-{{ $snapshot->id }}" title="Restore to this snapshot"
                                style="padding:6px 12px; border-radius:8px; font-size:11px; font-weight:700; background:#D1FAE5; color:#065F46; border:none; cursor:pointer;">
                                ↩ Restore
                            </button>
                            <button wire:click="confirmDelete({{ $snapshot->id }})"
                                data-testid="snapshot-delete-{{ $snapshot->id }}" title="Delete this snapshot"
                                style="padding:6px 10px; border-radius:8px; font-size:11px; font-weight:700; background:#FEE2E2; color:#991B1B; border:none; cursor:pointer;">
                                🗑
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <p style="font-size:11px; color:#7C6FAB; margin-top:12px; text-align:right;">
            {{ $snapshots->count() }} snapshot{{ $snapshots->count() !== 1 ? 's' : '' }} saved
            @if ($snapshots->count() >= 20)
                · <span style="color:#F59E0B;">⚠️ Near limit (20 max)</span>
            @endif
        </p>
    @endif

    {{-- Create Snapshot Modal --}}
    @if ($showCreateModal)
        <div style="position:fixed; inset:0; z-index:100; background:rgba(15,10,40,0.8); backdrop-filter:blur(8px); display:flex; align-items:center; justify-content:center;"
            role="dialog" aria-modal="true" aria-label="Create Snapshot">
            <div
                style="background:#fff; border-radius:20px; width:100%; max-width:440px; overflow:hidden; box-shadow:0 20px 60px rgba(124,58,237,0.3);">
                <div style="background:linear-gradient(135deg,#1E1033,#3B1F6E); padding:20px 24px;">
                    <h3 style="font-size:18px; font-weight:900; color:#fff; margin:0 0 4px;">📸 Create Snapshot</h3>
                    <p style="font-size:12px; color:rgba(255,255,255,0.5); margin:0;">Save your current career state</p>
                </div>
                <div style="padding:24px;">
                    <label style="display:block; font-size:13px; font-weight:700; color:#1E1033; margin-bottom:8px;">
                        Description <span style="color:#7C6FAB; font-weight:400;">(optional)</span>
                    </label>
                    <input type="text" wire:model="newDescription"
                        placeholder="e.g. Before G1 race, Turn 45 checkpoint…" maxlength="120"
                        data-testid="snapshot-description-input"
                        style="width:100%; padding:10px 14px; border:1px solid #EDE9FE; border-radius:10px; font-size:13px; color:#1E1033; background:#F9F5FF; box-sizing:border-box; outline:none;">
                    <p style="font-size:11px; color:#7C6FAB; margin-top:6px;">A snapshot captures your current stats,
                        SP, skills, and race history.</p>
                </div>
                <div
                    style="padding:16px 24px; border-top:1px solid #EDE9FE; display:flex; gap:10px; justify-content:flex-end;">
                    <button wire:click="closeCreateModal" data-testid="snapshot-create-cancel"
                        style="padding:10px 20px; border-radius:10px; font-size:13px; font-weight:700; background:transparent; color:#7C6FAB; border:none; cursor:pointer;">
                        Cancel
                    </button>
                    <button wire:click="createSnapshot" data-testid="snapshot-create-confirm"
                        style="padding:10px 20px; border-radius:10px; font-size:13px; font-weight:700; background:linear-gradient(135deg,#E879A0,#7C3AED); color:#fff; border:none; cursor:pointer; box-shadow:0 4px 12px rgba(232,121,160,.35);">
                        📸 Save Snapshot
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Compare Modal --}}
    @if ($showCompareModal)
        <div style="position:fixed; inset:0; z-index:100; background:rgba(15,10,40,0.8); backdrop-filter:blur(8px); display:flex; align-items:center; justify-content:center;"
            role="dialog" aria-modal="true" aria-label="Compare Snapshots">
            <div
                style="background:#fff; border-radius:20px; width:100%; max-width:560px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(124,58,237,0.3);">
                <div style="background:linear-gradient(135deg,#1E1033,#3B1F6E); padding:20px 24px;">
                    <h3 style="font-size:18px; font-weight:900; color:#fff; margin:0 0 4px;">⚖️ Compare Snapshots</h3>
                    <p style="font-size:12px; color:rgba(255,255,255,0.5); margin:0;">Select two snapshots to compare
                        stat differences</p>
                </div>
                <div style="padding:24px;">
                    @if ($compareResult === null)
                        {{-- Selector --}}
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:20px;">
                            <div>
                                <label
                                    style="display:block; font-size:12px; font-weight:700; color:#7C6FAB; margin-bottom:6px; text-transform:uppercase; letter-spacing:1px;">Snapshot
                                    A (From)</label>
                                <select wire:model="compareSnapshotAId" data-testid="compare-snapshot-a"
                                    style="width:100%; padding:9px 12px; border:1px solid #EDE9FE; border-radius:10px; font-size:13px; color:#1E1033; background:#F9F5FF; outline:none;">
                                    <option value="">Select…</option>
                                    @foreach ($snapshots as $snap)
                                        <option value="{{ $snap->id }}">Turn {{ $snap->turn_number }} —
                                            {{ $snap->description ?: $snap->created_at?->format('M d') }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label
                                    style="display:block; font-size:12px; font-weight:700; color:#7C6FAB; margin-bottom:6px; text-transform:uppercase; letter-spacing:1px;">Snapshot
                                    B (To)</label>
                                <select wire:model="compareSnapshotBId" data-testid="compare-snapshot-b"
                                    style="width:100%; padding:9px 12px; border:1px solid #EDE9FE; border-radius:10px; font-size:13px; color:#1E1033; background:#F9F5FF; outline:none;">
                                    <option value="">Select…</option>
                                    @foreach ($snapshots as $snap)
                                        <option value="{{ $snap->id }}">Turn {{ $snap->turn_number }} —
                                            {{ $snap->description ?: $snap->created_at?->format('M d') }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <button wire:click="runCompare" data-testid="compare-run-btn"
                            style="width:100%; padding:11px; border-radius:10px; font-size:13px; font-weight:700; background:linear-gradient(135deg,#E879A0,#7C3AED); color:#fff; border:none; cursor:pointer; box-shadow:0 4px 12px rgba(232,121,160,.35);">
                            ⚖️ Compare
                        </button>
                    @else
                        {{-- Results --}}
                        @php
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
                            $statLabels = [
                                'speed' => 'Speed',
                                'stamina' => 'Stamina',
                                'power' => 'Power',
                                'guts' => 'Guts',
                                'wit' => 'Wit',
                            ];
                        @endphp
                        <div
                            style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:16px; text-align:center;">
                            <div
                                style="background:#F5F3FF; border:1px solid #C4B5FD; border-radius:12px; padding:10px;">
                                <div
                                    style="font-size:10px; font-weight:800; color:#7C6FAB; text-transform:uppercase; letter-spacing:1px;">
                                    Snapshot A</div>
                                <div style="font-size:13px; font-weight:800; color:#1E1033;">Turn
                                    {{ $compareResult['snapshot_a']['turn'] }}</div>
                            </div>
                            <div
                                style="background:#F0FDF4; border:1px solid #6EE7B7; border-radius:12px; padding:10px;">
                                <div
                                    style="font-size:10px; font-weight:800; color:#065F46; text-transform:uppercase; letter-spacing:1px;">
                                    Snapshot B</div>
                                <div style="font-size:13px; font-weight:800; color:#1E1033;">Turn
                                    {{ $compareResult['snapshot_b']['turn'] }}</div>
                            </div>
                        </div>

                        <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:16px;">
                            @foreach ($compareResult['stat_diffs'] as $stat => $diff)
                                @php
                                    $color = $statColors[$stat] ?? '#7C3AED';
                                    $icon = $statIcons[$stat] ?? '📊';
                                    $label = $statLabels[$stat] ?? ucfirst($stat);
                                    $diffVal = $diff['diff'];
                                    $maxVal = max($diff['from'], $diff['to'], 1);
                                    $fromPct = round(($diff['from'] / 1200) * 100);
                                    $toPct = round(($diff['to'] / 1200) * 100);
                                @endphp
                                <div style="background:#F9F5FF; border-radius:10px; padding:10px 14px;">
                                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                                        <span style="font-size:13px;">{{ $icon }}</span>
                                        <span
                                            style="font-size:12px; font-weight:700; color:#1E1033; flex:1;">{{ $label }}</span>
                                        <span
                                            style="font-size:12px; font-weight:700; color:#7C6FAB;">{{ $diff['from'] }}</span>
                                        <span style="font-size:11px; color:#C4B5FD;">→</span>
                                        <span
                                            style="font-size:12px; font-weight:700; color:#1E1033;">{{ $diff['to'] }}</span>
                                        <span
                                            style="font-size:11px; font-weight:800; padding:2px 7px; border-radius:20px;
                                            background:{{ $diffVal > 0 ? '#D1FAE5' : ($diffVal < 0 ? '#FEE2E2' : '#F3F4F6') }};
                                            color:{{ $diffVal > 0 ? '#065F46' : ($diffVal < 0 ? '#991B1B' : '#6B7280') }};">
                                            {{ $diffVal > 0 ? '+' : '' }}{{ $diffVal }}
                                        </span>
                                    </div>
                                    <div
                                        style="height:6px; background:#EDE9FE; border-radius:99px; overflow:hidden; position:relative;">
                                        <div
                                            style="height:100%; width:{{ $fromPct }}%; background:{{ $color }}66; border-radius:99px;">
                                        </div>
                                    </div>
                                    <div
                                        style="height:6px; background:#EDE9FE; border-radius:99px; overflow:hidden; margin-top:3px;">
                                        <div
                                            style="height:100%; width:{{ $toPct }}%; background:{{ $color }}; border-radius:99px;">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Turn + SP delta --}}
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:16px;">
                            <div
                                style="background:#EFF6FF; border:1px solid #BFDBFE; border-radius:10px; padding:10px; text-align:center;">
                                <div
                                    style="font-size:10px; font-weight:800; color:#1E40AF; text-transform:uppercase; letter-spacing:1px;">
                                    Turn Delta</div>
                                <div style="font-size:20px; font-weight:900; color:#3B82F6;">
                                    +{{ $compareResult['turn_diff'] }}</div>
                            </div>
                            <div
                                style="background:#FFFBEB; border:1px solid #FCD34D; border-radius:10px; padding:10px; text-align:center;">
                                <div
                                    style="font-size:10px; font-weight:800; color:#92400E; text-transform:uppercase; letter-spacing:1px;">
                                    Skills Gained</div>
                                <div style="font-size:20px; font-weight:900; color:#F59E0B;">
                                    +{{ $compareResult['skills_diff'] }}</div>
                            </div>
                        </div>

                        <button wire:click="$set('compareResult', null)" data-testid="compare-reset-btn"
                            style="width:100%; padding:9px; border-radius:10px; font-size:12px; font-weight:700; background:#EDE9FE; color:#7C3AED; border:none; cursor:pointer; margin-bottom:8px;">
                            ← Compare Different Snapshots
                        </button>
                    @endif
                </div>
                <div style="padding:12px 24px; border-top:1px solid #EDE9FE; display:flex; justify-content:flex-end;">
                    <button wire:click="closeCompareModal" data-testid="compare-close-btn"
                        style="padding:9px 20px; border-radius:10px; font-size:13px; font-weight:700; background:transparent; color:#7C6FAB; border:none; cursor:pointer;">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Restore Confirm Modal --}}
    @if ($showRestoreConfirm)
        <div style="position:fixed; inset:0; z-index:100; background:rgba(15,10,40,0.8); backdrop-filter:blur(8px); display:flex; align-items:center; justify-content:center;"
            role="dialog" aria-modal="true" aria-label="Confirm Restore">
            <div
                style="background:#fff; border-radius:20px; width:100%; max-width:400px; overflow:hidden; box-shadow:0 20px 60px rgba(124,58,237,0.3);">
                <div style="background:linear-gradient(135deg,#78350F,#92400E); padding:20px 24px;">
                    <h3 style="font-size:18px; font-weight:900; color:#fff; margin:0 0 4px;">⚠️ Restore Snapshot</h3>
                    <p style="font-size:12px; color:rgba(255,255,255,0.6); margin:0;">This will overwrite your current
                        career state</p>
                </div>
                <div style="padding:24px;">
                    <p style="font-size:13px; color:#1E1033; line-height:1.6;">Your current career state will be
                        replaced with the snapshot data. A backup snapshot will be created automatically before
                        restoring.</p>
                    <div
                        style="margin-top:12px; padding:10px 14px; background:#FFFBEB; border:1px solid #FCD34D; border-radius:10px; font-size:12px; color:#92400E;">
                        💡 A pre-restore backup will be saved automatically so you can undo this action.
                    </div>
                </div>
                <div
                    style="padding:16px 24px; border-top:1px solid #EDE9FE; display:flex; gap:10px; justify-content:flex-end;">
                    <button wire:click="cancelRestore" data-testid="snapshot-restore-cancel"
                        style="padding:10px 20px; border-radius:10px; font-size:13px; font-weight:700; background:transparent; color:#7C6FAB; border:none; cursor:pointer;">
                        Cancel
                    </button>
                    <button wire:click="restoreSnapshot" data-testid="snapshot-restore-confirm"
                        style="padding:10px 20px; border-radius:10px; font-size:13px; font-weight:700; background:linear-gradient(135deg,#F59E0B,#F97316); color:#fff; border:none; cursor:pointer; box-shadow:0 4px 12px rgba(245,158,11,.35);">
                        ↩ Restore Now
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirm Modal --}}
    @if ($showDeleteConfirm)
        <div style="position:fixed; inset:0; z-index:100; background:rgba(15,10,40,0.8); backdrop-filter:blur(8px); display:flex; align-items:center; justify-content:center;"
            role="dialog" aria-modal="true" aria-label="Confirm Delete">
            <div
                style="background:#fff; border-radius:20px; width:100%; max-width:380px; overflow:hidden; box-shadow:0 20px 60px rgba(239,68,68,0.3);">
                <div style="background:linear-gradient(135deg,#7F1D1D,#991B1B); padding:20px 24px;">
                    <h3 style="font-size:18px; font-weight:900; color:#fff; margin:0 0 4px;">🗑 Delete Snapshot</h3>
                    <p style="font-size:12px; color:rgba(255,255,255,0.6); margin:0;">This action cannot be undone</p>
                </div>
                <div style="padding:24px;">
                    <p style="font-size:13px; color:#1E1033; line-height:1.6;">Are you sure you want to permanently
                        delete this snapshot?</p>
                </div>
                <div
                    style="padding:16px 24px; border-top:1px solid #EDE9FE; display:flex; gap:10px; justify-content:flex-end;">
                    <button wire:click="cancelDelete" data-testid="snapshot-delete-cancel"
                        style="padding:10px 20px; border-radius:10px; font-size:13px; font-weight:700; background:transparent; color:#7C6FAB; border:none; cursor:pointer;">
                        Cancel
                    </button>
                    <button wire:click="deleteSnapshot" data-testid="snapshot-delete-confirm"
                        style="padding:10px 20px; border-radius:10px; font-size:13px; font-weight:700; background:#EF4444; color:#fff; border:none; cursor:pointer;">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
