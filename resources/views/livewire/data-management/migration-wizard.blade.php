<div>
    @if ($step === 'idle')
        {{-- Storage Conversion CTA --}}
        <div
            style="background:linear-gradient(135deg,#F5F3FF,#EDE9FE); border:2px solid #C4B5FD; border-radius:16px; padding:24px 28px; display:flex; align-items:center; gap:20px; flex-wrap:wrap;">
            <div style="font-size:40px; flex-shrink:0;">🟠→🟣</div>
            <div style="flex:1; min-width:200px;">
                <div style="font-size:16px; font-weight:800; color:#1E1033; margin-bottom:4px;">Convert Local Data to Account</div>
                <div style="font-size:13px; color:#7C6FAB; line-height:1.5;">Move your locally stored characters and
                    career runs into your account for cloud backup, cross-device access, and advanced analytics.</div>
            </div>
            <button wire:click="startMigration" data-testid="migration-start-btn"
                style="padding:12px 24px; border-radius:10px; font-size:14px; font-weight:700; background:linear-gradient(135deg,#E879A0,#7C3AED); color:#fff; border:none; cursor:pointer; box-shadow:0 4px 12px rgba(232,121,160,.35); white-space:nowrap; flex-shrink:0;">
                Start Migration →
            </button>
        </div>

        {{-- Other data cards --}}
        <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:14px; margin-top:20px;">
            <a href="{{ route('export.index') }}"
                style="background:#fff; border:1px solid #EDE9FE; border-radius:14px; padding:18px 20px; text-decoration:none; display:block; transition:all 0.15s;">
                <div style="font-size:24px; margin-bottom:8px;">📤</div>
                <div style="font-size:14px; font-weight:800; color:#1E1033; margin-bottom:4px;">Export Data</div>
                <div style="font-size:12px; color:#7C6FAB;">Download JSON, CSV, or Excel</div>
            </a>
            <a href="{{ route('import.index') }}"
                style="background:#fff; border:1px solid #EDE9FE; border-radius:14px; padding:18px 20px; text-decoration:none; display:block; transition:all 0.15s;">
                <div style="font-size:24px; margin-bottom:8px;">📥</div>
                <div style="font-size:14px; font-weight:800; color:#1E1033; margin-bottom:4px;">Import Data</div>
                <div style="font-size:12px; color:#7C6FAB;">Bring in external data files</div>
            </a>
            <a href="{{ route('backup.index') }}"
                style="background:#fff; border:1px solid #EDE9FE; border-radius:14px; padding:18px 20px; text-decoration:none; display:block; transition:all 0.15s;">
                <div style="font-size:24px; margin-bottom:8px;">☁️</div>
                <div style="font-size:14px; font-weight:800; color:#1E1033; margin-bottom:4px;">Cloud Backup</div>
                <div style="font-size:12px; color:#7C6FAB;">Automatic 30-day snapshots</div>
            </a>
            <a href="{{ route('migration.index') }}"
                style="background:#fff; border:1px solid #EDE9FE; border-radius:14px; padding:18px 20px; text-decoration:none; display:block; transition:all 0.15s;">
                <div style="font-size:24px; margin-bottom:8px;">🔄</div>
                <div style="font-size:14px; font-weight:800; color:#1E1033; margin-bottom:4px;">Legacy Migration</div>
                <div style="font-size:12px; color:#7C6FAB;">Convert older data formats</div>
            </a>
        </div>
    @elseif ($step === 'validate' || $step === 'preview' || $step === 'migrate' || $step === 'done')
        {{-- Migration Modal --}}
        <div style="position:fixed; inset:0; z-index:100; background:rgba(15,10,40,0.8); backdrop-filter:blur(8px); display:flex; align-items:center; justify-content:center;"
            role="dialog" aria-modal="true" aria-label="Migration Wizard">
            <div
                style="background:#fff; border-radius:24px; width:100%; max-width:520px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(124,58,237,0.4);">

                {{-- Modal Header with step indicators --}}
                <div style="background:linear-gradient(135deg,#1E1033,#3B1F6E); padding:20px 24px;">
                    <div style="font-size:18px; font-weight:900; color:#fff; margin-bottom:12px;">🔄 Data Migration
                        Wizard</div>
                    {{-- Step indicators --}}
                    <div style="display:flex; align-items:center; gap:0;">
                        @php
                            $steps = [
                                'validate' => 'Validate',
                                'preview' => 'Preview',
                                'migrate' => 'Migrate',
                                'done' => 'Done',
                            ];
                            $stepOrder = array_keys($steps);
                            $currentIdx = array_search($step, $stepOrder);
                        @endphp
                        @foreach ($steps as $key => $label)
                            @php
                                $idx = array_search($key, $stepOrder);
                                $isPast = $idx < $currentIdx;
                                $isCurrent = $idx === $currentIdx;
                            @endphp
                            <div style="display:flex; align-items:center; flex:1;">
                                <div style="display:flex; flex-direction:column; align-items:center; flex-shrink:0;">
                                    <div
                                        style="
                                        width:28px; height:28px; border-radius:50%;
                                        display:flex; align-items:center; justify-content:center;
                                        font-size:11px; font-weight:800;
                                        {{ $isPast ? 'background:#10B981; color:#fff;' : '' }}
                                        {{ $isCurrent ? 'background:linear-gradient(135deg,#E879A0,#7C3AED); color:#fff;' : '' }}
                                        {{ !$isPast && !$isCurrent ? 'background:rgba(255,255,255,0.15); color:rgba(255,255,255,0.5);' : '' }}
                                    ">
                                        {{ $isPast ? '✓' : $idx + 1 }}</div>
                                    <div
                                        style="font-size:9px; font-weight:700; margin-top:3px; color:{{ $isCurrent ? '#F9A8D4' : 'rgba(255,255,255,0.4)' }}; white-space:nowrap;">
                                        {{ $label }}</div>
                                </div>
                                @if (!$loop->last)
                                    <div
                                        style="flex:1; height:2px; background:{{ $isPast ? '#10B981' : 'rgba(255,255,255,0.15)' }}; margin:0 4px 18px;">
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Modal Body --}}
                <div style="padding:24px 28px;">

                    @if ($step === 'validate')
                        <div style="font-size:15px; font-weight:800; color:#1E1033; margin-bottom:16px;">Step 1 —
                            Validate Local Data</div>

                        {{-- Browser storage contents --}}
                        <div
                            style="background:#F9F5FF; border:1px solid #EDE9FE; border-radius:12px; padding:16px; margin-bottom:16px;">
                            <div
                                style="font-size:12px; font-weight:800; color:#7C6FAB; text-transform:uppercase; letter-spacing:1px; margin-bottom:12px;">
                                Browser Storage Contents</div>
                            <div style="display:flex; flex-direction:column; gap:8px;">
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <span style="font-size:13px; color:#1E1033;">👤 Characters</span>
                                    <span
                                        style="font-size:14px; font-weight:800; color:#7C3AED;">{{ $validationResult['characters'] ?? 0 }}</span>
                                </div>
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <span style="font-size:13px; color:#1E1033;">🏇 Career Runs</span>
                                    <span
                                        style="font-size:14px; font-weight:800; color:#7C3AED;">{{ $validationResult['careers'] ?? 0 }}</span>
                                </div>
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <span style="font-size:13px; color:#1E1033;">🎴 Support Builds</span>
                                    <span
                                        style="font-size:14px; font-weight:800; color:#7C3AED;">{{ $validationResult['builds'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>

                        <div
                            style="background:#D1FAE5; border:1px solid #6EE7B7; border-radius:10px; padding:12px 16px; margin-bottom:16px; font-size:13px; font-weight:700; color:#065F46;">
                            ✅ Validation passed · {{ $validationResult['total'] ?? 0 }} assets ready to convert
                        </div>

                        {{-- Keep local copy toggle --}}
                        <div
                            style="display:flex; align-items:center; gap:12px; margin-bottom:20px; padding:12px 16px; background:#F9F5FF; border-radius:10px; border:1px solid #EDE9FE;">
                            <div wire:click="$toggle('keepLocalCopy')" data-testid="keep-local-toggle"
                                style="
                                    width:44px; height:24px; border-radius:99px; cursor:pointer; position:relative; flex-shrink:0; transition:background 0.2s;
                                    background:{{ $keepLocalCopy ? 'linear-gradient(135deg,#E879A0,#7C3AED)' : '#D1D5DB' }};
                                "
                                role="switch" aria-checked="{{ $keepLocalCopy ? 'true' : 'false' }}"
                                aria-label="Keep local copy">
                                <div
                                    style="
                                    position:absolute; top:2px; width:20px; height:20px; border-radius:50%; background:#fff;
                                    transition:left 0.2s; left:{{ $keepLocalCopy ? '22px' : '2px' }};
                                    box-shadow:0 1px 4px rgba(0,0,0,0.2);
                                ">
                                </div>
                            </div>
                            <div>
                                <div style="font-size:13px; font-weight:700; color:#1E1033;">Keep local copy</div>
                                <div style="font-size:11px; color:#7C6FAB;">Preserve local data after migration</div>
                            </div>
                        </div>

                        <button wire:click="goToPreview" data-testid="migration-preview-btn"
                            style="width:100%; padding:12px; border-radius:10px; font-size:14px; font-weight:700; background:linear-gradient(135deg,#E879A0,#7C3AED); color:#fff; border:none; cursor:pointer; box-shadow:0 4px 12px rgba(232,121,160,.35);">
                            Preview Migration →
                        </button>
                    @elseif ($step === 'preview')
                        <div style="font-size:15px; font-weight:800; color:#1E1033; margin-bottom:16px;">Step 2 —
                            Preview</div>

                        <div
                            style="display:flex; flex-direction:column; gap:8px; margin-bottom:20px; max-height:280px; overflow-y:auto;">
                            @forelse ($previewItems as $item)
                                <div
                                    style="display:flex; align-items:center; justify-content:space-between; padding:10px 14px; background:#F9F5FF; border:1px solid #EDE9FE; border-radius:10px;">
                                    <span style="font-size:13px; font-weight:700; color:#1E1033;">👤
                                        {{ $item['name'] }}</span>
                                    <span
                                        style="font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; background:#D1FAE5; color:#065F46;">✅
                                        Ready</span>
                                </div>
                            @empty
                                <div style="text-align:center; padding:24px; color:#7C6FAB; font-size:13px;">No items to
                                    migrate.</div>
                            @endforelse
                        </div>

                        <button wire:click="runMigration" data-testid="migration-confirm-btn"
                            style="width:100%; padding:12px; border-radius:10px; font-size:14px; font-weight:700; background:linear-gradient(135deg,#F59E0B,#F97316); color:#fff; border:none; cursor:pointer; box-shadow:0 4px 12px rgba(245,158,11,.35);">
                            Confirm & Migrate
                        </button>
                    @elseif ($step === 'migrate')
                        <div style="text-align:center; padding:20px 0;">
                            <div style="font-size:40px; margin-bottom:12px;">⚙️</div>
                            <div style="font-size:16px; font-weight:800; color:#1E1033; margin-bottom:8px;">Migrating
                                data…</div>
                            <div
                                style="height:10px; background:#EDE9FE; border-radius:99px; overflow:hidden; margin-bottom:8px;">
                                <div
                                    style="height:100%; width:{{ $totalCount > 0 ? round(($migratedCount / $totalCount) * 100) : 0 }}%; background:linear-gradient(90deg,#E879A0,#7C3AED); border-radius:99px; transition:width 0.28s ease;">
                                </div>
                            </div>
                            <div style="font-size:13px; color:#7C6FAB;">{{ $migratedCount }}/{{ $totalCount }}
                                assets migrated</div>
                        </div>
                    @elseif ($step === 'done')
                        <div style="text-align:center; padding:10px 0 20px;">
                            <div style="font-size:48px; margin-bottom:12px;">🎉</div>
                            <div style="font-size:20px; font-weight:900; color:#1E1033; margin-bottom:8px;">Migration Complete!</div>
                            <div style="font-size:13px; color:#7C6FAB; margin-bottom:20px;">{{ $migratedCount }} asset{{ $migratedCount !== 1 ? 's' : '' }} successfully migrated to your account.</div>

                            <div
                                style="display:flex; flex-direction:column; gap:8px; margin-bottom:20px; text-align:left;">
                                @foreach ($migratedItems as $item)
                                    <div
                                        style="display:flex; align-items:center; gap:10px; padding:10px 14px; background:#D1FAE5; border:1px solid #6EE7B7; border-radius:10px;">
                                        <span style="font-size:14px;">✅</span>
                                        <span
                                            style="font-size:13px; font-weight:700; color:#065F46;">{{ $item['name'] }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <button wire:click="cancelWizard" data-testid="migration-done-btn"
                                style="padding:12px 32px; border-radius:10px; font-size:14px; font-weight:700; background:linear-gradient(135deg,#E879A0,#7C3AED); color:#fff; border:none; cursor:pointer; box-shadow:0 4px 12px rgba(232,121,160,.35);">
                                Done
                            </button>
                        </div>
                    @endif

                </div>

                {{-- Cancel button (not on done step) --}}
                @if ($step !== 'done' && $step !== 'migrate')
                    <div
                        style="padding:12px 28px; border-top:1px solid #EDE9FE; display:flex; justify-content:flex-start;">
                        <button wire:click="cancelWizard" data-testid="migration-cancel-btn"
                            style="padding:9px 18px; border-radius:10px; font-size:13px; font-weight:700; background:transparent; color:#7C6FAB; border:none; cursor:pointer;">
                            ← Cancel
                        </button>
                    </div>
                @endif

            </div>
        </div>
    @endif
</div>
