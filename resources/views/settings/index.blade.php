@extends('layouts.app')

@section('title', 'Settings')

@section('content')
    @php
        $prefs = $user->preferences ? $user->preferences->getArrayCopy() : [];
        $aiSettings = $user->ai_settings ? $user->ai_settings->getArrayCopy() : [];
        $accessSettings = $user->accessibility_settings ? $user->accessibility_settings->getArrayCopy() : [];
        $notifPrefs = $user->notification_preferences ? $user->notification_preferences->getArrayCopy() : [];
        $storageMode = \App\Enums\StorageMode::fromRequest(request());
        $isAccountMode = $storageMode === \App\Enums\StorageMode::ACCOUNT;
        $currentTheme = $prefs['theme'] ?? 'light';
        $currentLang = $prefs['language'] ?? 'en';
        $compactMode = (bool) ($prefs['compact_mode'] ?? false);
        $aiProvider = $aiSettings['provider'] ?? 'ollama';
        $aiCostLimit = $aiSettings['budget_limit'] ?? config('ai.hybrid.cost_threshold', 0.1);
    @endphp

    <div style="max-width:680px;margin:0 auto;padding:24px 16px 48px;" x-data="{
        storageMode: '{{ $storageMode->value }}',
        theme: '{{ $currentTheme }}',
        lang: '{{ $currentLang }}',
        compactMode: {{ $compactMode ? 'true' : 'false' }},
        aiProvider: '{{ $aiProvider }}',
        aiCostLimit: {{ (float) $aiCostLimit }},
        showConversionCta: {{ $storageMode === \App\Enums\StorageMode::LOCAL ? 'true' : 'false' }},
        showDeleteConfirm: false,
        deleteConfirmText: '',
        saved: false,
        saving: false,
        saveMsg: '',
        setTheme(val) {
            this.theme = val;
            if (val === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            localStorage.setItem('theme', val);
        },
        async saveAll() {
            this.saving = true;
            this.saved = false;
            try {
                const resp = await fetch('{{ route('settings.update') }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        preferences: {
                            theme: this.theme,
                            language: this.lang,
                            compact_mode: this.compactMode,
                        },
                        ai_settings: {
                            provider: this.aiProvider,
                            budget_limit: parseFloat(this.aiCostLimit),
                        },
                    }),
                });
                if (resp.ok) {
                    this.saved = true;
                    this.saveMsg = 'Settings saved!';
                    setTimeout(() => { this.saved = false; }, 3000);
                }
            } finally {
                this.saving = false;
            }
        },
        resetDefaults() {
            if (!confirm('Reset all settings to defaults?')) return;
            this.theme = 'light';
            this.lang = 'en';
            this.compactMode = false;
            this.aiProvider = 'ollama';
            this.aiCostLimit = 0.10;
            this.setTheme('light');
            this.saveAll();
        },
        replayOnboarding() {
            localStorage.removeItem('uma_onboarded');
            window.location.href = '/';
        },
    }">
        {{-- Page Header --}}
        <div style="margin-bottom:28px;">
            <h1 style="font-size:22px;font-weight:900;color:#1E1033;font-family:'Nunito',sans-serif;margin:0 0 4px;">Settings
            </h1>
            <p style="font-size:13px;color:#7C6FAB;margin:0;">Manage your preferences, AI configuration, and account
                settings.</p>
        </div>

        {{-- Save Banner --}}
        <div x-show="saved" x-transition
            style="background:#F0FDF4;border:1px solid #6EE7B7;border-radius:12px;padding:12px 16px;margin-bottom:20px;display:flex;align-items:center;gap:10px;"
            role="status" aria-live="polite">
            <span style="color:#065F46;font-size:13px;font-weight:700;">✓</span>
            <span style="color:#065F46;font-size:13px;font-weight:600;" x-text="saveMsg"></span>
        </div>

        {{-- ── SECTION 1: Storage ── --}}
        <div
            style="background:#fff;border-radius:16px;border:1px solid #EDE9FE;box-shadow:0 2px 12px rgba(124,58,237,0.07);margin-bottom:20px;overflow:hidden;">
            <div style="padding:16px 20px 12px;border-bottom:1px solid #EDE9FE;">
                <span
                    style="font-size:11px;font-weight:800;color:#7C6FAB;text-transform:uppercase;letter-spacing:1.5px;">Storage</span>
            </div>
            <div style="padding:4px 0;">

                {{-- Storage Mode Row --}}
                <div
                    style="display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-bottom:1px solid #F9F5FF;">
                    <div>
                        <div style="font-size:14px;font-weight:700;color:#1E1033;">Storage Mode</div>
                        <div style="font-size:12px;color:#7C6FAB;margin-top:2px;">Where your career run data is saved</div>
                    </div>
                    <div style="display:flex;gap:6px;">
                        <button @click="storageMode = 'local'; showConversionCta = true;"
                            :style="storageMode === 'local'
                                ?
                                'background:linear-gradient(135deg,#E879A0,#7C3AED);color:#fff;border:none;' :
                                'background:#EDE9FE;color:#7C6FAB;border:none;'"
                            style="padding:7px 14px;border-radius:10px;font-size:12px;font-weight:700;font-family:\'Nunito\',sans-serif;cursor:pointer;transition:all .15s;"
                            data-testid="settings-storage-local" aria-pressed="storageMode === 'local'">🟠 Local</button>
                        <button @click="storageMode = 'account'; showConversionCta = false;"
                            :style="storageMode === 'account'
                                ?
                                'background:linear-gradient(135deg,#E879A0,#7C3AED);color:#fff;border:none;' :
                                'background:#EDE9FE;color:#7C6FAB;border:none;'"
                            style="padding:7px 14px;border-radius:10px;font-size:12px;font-weight:700;font-family:\'Nunito\',sans-serif;cursor:pointer;transition:all .15s;"
                            data-testid="settings-storage-account" aria-pressed="storageMode === 'account'">🟣
                            Account</button>
                    </div>
                </div>

                {{-- Conversion CTA (Local mode only) --}}
                <div x-show="showConversionCta" x-transition
                    style="margin:12px 20px;background:linear-gradient(135deg,#FFFBEB,#FEF3C7);border:1px solid #FCD34D;border-radius:12px;padding:14px 16px;display:flex;justify-content:space-between;align-items:center;gap:12px;">
                    <div>
                        <div style="font-size:13px;font-weight:700;color:#92400E;">🟠→🟣 Convert to Account Mode</div>
                        <div style="font-size:12px;color:#B45309;margin-top:3px;">Convert your local data for cross-device
                            sync</div>
                    </div>
                    <a href="{{ route('data.index') }}"
                        style="background:linear-gradient(135deg,#F59E0B,#F97316);color:#fff;padding:8px 16px;border-radius:10px;font-size:12px;font-weight:700;font-family:'Nunito',sans-serif;text-decoration:none;box-shadow:0 4px 12px rgba(245,158,11,.35);white-space:nowrap;"
                        data-testid="settings-convert-cta">Convert →</a>
                </div>

                {{-- Replay Onboarding Row --}}
                <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 20px;">
                    <div>
                        <div style="font-size:14px;font-weight:700;color:#1E1033;">Replay Onboarding</div>
                        <div style="font-size:12px;color:#7C6FAB;margin-top:2px;">Go through the welcome flow again</div>
                    </div>
                    <button @click="replayOnboarding()"
                        style="background:#EDE9FE;color:#7C3AED;padding:7px 14px;border-radius:10px;font-size:12px;font-weight:700;font-family:'Nunito',sans-serif;border:none;cursor:pointer;transition:all .15s;"
                        data-testid="settings-replay-onboarding">Replay →</button>
                </div>
            </div>
        </div>

        {{-- ── SECTION 2: AI Advisor ── --}}
        <div
            style="background:#fff;border-radius:16px;border:1px solid #EDE9FE;box-shadow:0 2px 12px rgba(124,58,237,0.07);margin-bottom:20px;overflow:hidden;">
            <div style="padding:16px 20px 12px;border-bottom:1px solid #EDE9FE;">
                <span style="font-size:11px;font-weight:800;color:#7C6FAB;text-transform:uppercase;letter-spacing:1.5px;">AI
                    Advisor</span>
            </div>
            <div style="padding:4px 0;">

                {{-- Default AI Model --}}
                <div
                    style="display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-bottom:1px solid #F9F5FF;">
                    <div>
                        <div style="font-size:14px;font-weight:700;color:#1E1033;">Default AI Model</div>
                        <div style="font-size:12px;color:#7C6FAB;margin-top:2px;">Model used for recommendations</div>
                    </div>
                    <select x-model="aiProvider"
                        style="padding:7px 12px;border:1px solid #EDE9FE;background:#F9F5FF;color:#7C3AED;font-weight:700;font-family:'Nunito',sans-serif;border-radius:10px;font-size:12px;cursor:pointer;"
                        data-testid="settings-ai-model-select" aria-label="Default AI model">
                        <option value="bedrock">☁️ AWS Bedrock Claude</option>
                        <option value="ollama">💻 Local Ollama</option>
                    </select>
                </div>

                {{-- AI Cost Threshold --}}
                <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 20px;">
                    <div>
                        <div style="font-size:14px;font-weight:700;color:#1E1033;">AI Cost Threshold</div>
                        <div style="font-size:12px;color:#7C6FAB;margin-top:2px;">Daily Bedrock spend limit before fallback
                            to Ollama</div>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="font-size:12px;color:#7C6FAB;">$</span>
                        <input type="number" x-model="aiCostLimit" min="0.01" max="10" step="0.01"
                            style="width:70px;padding:7px 10px;border:1px solid #EDE9FE;background:#F9F5FF;color:#1E1033;font-weight:700;font-family:'Nunito',sans-serif;border-radius:10px;font-size:12px;"
                            data-testid="settings-ai-cost-threshold" aria-label="AI cost threshold in USD per day">
                        <span style="font-size:12px;color:#7C6FAB;">USD/day</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── SECTION 3: Appearance ── --}}
        <div
            style="background:#fff;border-radius:16px;border:1px solid #EDE9FE;box-shadow:0 2px 12px rgba(124,58,237,0.07);margin-bottom:20px;overflow:hidden;">
            <div style="padding:16px 20px 12px;border-bottom:1px solid #EDE9FE;">
                <span
                    style="font-size:11px;font-weight:800;color:#7C6FAB;text-transform:uppercase;letter-spacing:1.5px;">Appearance</span>
            </div>
            <div style="padding:4px 0;">

                {{-- Theme --}}
                <div
                    style="display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-bottom:1px solid #F9F5FF;">
                    <div>
                        <div style="font-size:14px;font-weight:700;color:#1E1033;">Theme</div>
                        <div style="font-size:12px;color:#7C6FAB;margin-top:2px;">Light or dark mode</div>
                    </div>
                    <div style="display:flex;gap:6px;">
                        <button @click="setTheme('light')"
                            :style="theme === 'light'
                                ?
                                'background:linear-gradient(135deg,#E879A0,#7C3AED);color:#fff;border:none;' :
                                'background:#EDE9FE;color:#7C6FAB;border:none;'"
                            style="padding:7px 14px;border-radius:10px;font-size:12px;font-weight:700;font-family:\'Nunito\',sans-serif;cursor:pointer;transition:all .15s;"
                            data-testid="settings-theme-light" aria-pressed="theme === 'light'">☀️ Light</button>
                        <button @click="setTheme('dark')"
                            :style="theme === 'dark'
                                ?
                                'background:linear-gradient(135deg,#E879A0,#7C3AED);color:#fff;border:none;' :
                                'background:#EDE9FE;color:#7C6FAB;border:none;'"
                            style="padding:7px 14px;border-radius:10px;font-size:12px;font-weight:700;font-family:\'Nunito\',sans-serif;cursor:pointer;transition:all .15s;"
                            data-testid="settings-theme-dark" aria-pressed="theme === 'dark'">🌙 Dark</button>
                    </div>
                </div>

                {{-- Compact Mode --}}
                <div
                    style="display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-bottom:1px solid #F9F5FF;">
                    <div>
                        <div style="font-size:14px;font-weight:700;color:#1E1033;">Compact Mode</div>
                        <div style="font-size:12px;color:#7C6FAB;margin-top:2px;">Reduce spacing for more data density</div>
                    </div>
                    {{-- Toggle --}}
                    <button @click="compactMode = !compactMode"
                        :style="compactMode
                            ?
                            'background:linear-gradient(135deg,#E879A0,#7C3AED);' :
                            'background:#D1D5DB;'"
                        style="position:relative;width:44px;height:24px;border-radius:99px;border:none;cursor:pointer;transition:background .2s;flex-shrink:0;"
                        role="switch" :aria-checked="compactMode.toString()" aria-label="Compact mode"
                        data-testid="settings-compact-mode-toggle">
                        <span :style="compactMode ? 'left:22px;' : 'left:2px;'"
                            style="position:absolute;top:2px;width:20px;height:20px;background:#fff;border-radius:50%;transition:left .2s;box-shadow:0 1px 3px rgba(0,0,0,.2);"></span>
                    </button>
                </div>

                {{-- Language --}}
                <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 20px;">
                    <div>
                        <div style="font-size:14px;font-weight:700;color:#1E1033;">Language</div>
                        <div style="font-size:12px;color:#7C6FAB;margin-top:2px;">Interface language</div>
                    </div>
                    <div style="display:flex;gap:6px;">
                        <button @click="lang = 'en'"
                            :style="lang === 'en'
                                ?
                                'background:linear-gradient(135deg,#E879A0,#7C3AED);color:#fff;border:none;' :
                                'background:#EDE9FE;color:#7C6FAB;border:none;'"
                            style="padding:7px 14px;border-radius:10px;font-size:12px;font-weight:700;font-family:\'Nunito\',sans-serif;cursor:pointer;transition:all .15s;"
                            data-testid="settings-lang-en">English</button>
                        <button @click="lang = 'ja'"
                            :style="lang === 'ja'
                                ?
                                'background:linear-gradient(135deg,#E879A0,#7C3AED);color:#fff;border:none;' :
                                'background:#EDE9FE;color:#7C6FAB;border:none;'"
                            style="padding:7px 14px;border-radius:10px;font-size:12px;font-weight:700;font-family:\'Nunito\',sans-serif;cursor:pointer;transition:all .15s;"
                            data-testid="settings-lang-ja">日本語</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── SECTION 4: Notifications ── --}}
        <div
            style="background:#fff;border-radius:16px;border:1px solid #EDE9FE;box-shadow:0 2px 12px rgba(124,58,237,0.07);margin-bottom:20px;overflow:hidden;">
            <div style="padding:16px 20px 12px;border-bottom:1px solid #EDE9FE;">
                <span
                    style="font-size:11px;font-weight:800;color:#7C6FAB;text-transform:uppercase;letter-spacing:1.5px;">Notifications</span>
            </div>
            <div style="padding:12px 20px 16px;">
                @livewire('settings.notification-settings')
            </div>
        </div>

        {{-- ── SECTION 5: Accessibility ── --}}
        <div
            style="background:#fff;border-radius:16px;border:1px solid #EDE9FE;box-shadow:0 2px 12px rgba(124,58,237,0.07);margin-bottom:20px;overflow:hidden;">
            <div style="padding:16px 20px 12px;border-bottom:1px solid #EDE9FE;">
                <span
                    style="font-size:11px;font-weight:800;color:#7C6FAB;text-transform:uppercase;letter-spacing:1.5px;">Accessibility</span>
            </div>
            <div style="padding:12px 20px 16px;">
                @livewire('settings.accessibility-settings')
            </div>
        </div>

        {{-- ── SECTION 6: Account (Account mode only) ── --}}
        @if ($isAccountMode)
            <div style="background:#fff;border-radius:16px;border:1px solid #EDE9FE;box-shadow:0 2px 12px rgba(124,58,237,0.07);margin-bottom:20px;overflow:hidden;"
                data-testid="settings-account-section">
                <div style="padding:16px 20px 12px;border-bottom:1px solid #EDE9FE;">
                    <span
                        style="font-size:11px;font-weight:800;color:#7C6FAB;text-transform:uppercase;letter-spacing:1.5px;">Account</span>
                </div>
                <div style="padding:4px 0;">

                    {{-- Profile Row --}}
                    <div
                        style="display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-bottom:1px solid #F9F5FF;">
                        <div>
                            <div style="font-size:14px;font-weight:700;color:#1E1033;">Profile</div>
                            <div style="font-size:12px;color:#7C6FAB;margin-top:2px;">Edit trainer name and avatar</div>
                        </div>
                        <a href="{{ route('profile.show') }}"
                            style="background:#EDE9FE;color:#7C3AED;padding:7px 14px;border-radius:10px;font-size:12px;font-weight:700;font-family:'Nunito',sans-serif;text-decoration:none;transition:all .15s;"
                            data-testid="settings-profile-link">Edit Profile →</a>
                    </div>

                    {{-- Danger Zone --}}
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 20px;">
                        <div>
                            <div style="font-size:14px;font-weight:700;color:#DC2626;">Danger Zone</div>
                            <div style="font-size:12px;color:#7C6FAB;margin-top:2px;">Delete all career data permanently
                            </div>
                        </div>
                        <button @click="showDeleteConfirm = true"
                            style="background:#FEE2E2;color:#DC2626;padding:7px 14px;border-radius:10px;font-size:12px;font-weight:700;font-family:'Nunito',sans-serif;border:none;cursor:pointer;transition:all .15s;"
                            data-testid="settings-delete-account-btn">Delete Account</button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ── Footer Buttons ── --}}
        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:8px;">
            <button @click="resetDefaults()"
                style="background:#EDE9FE;color:#7C3AED;padding:10px 20px;border-radius:10px;font-size:14px;font-weight:700;font-family:'Nunito',sans-serif;border:none;cursor:pointer;transition:all .15s;"
                data-testid="settings-reset-btn">Reset to Defaults</button>
            <button @click="saveAll()" :disabled="saving"
                :style="saving
                    ? 'background:linear-gradient(135deg,#E879A0,#7C3AED);color:#fff;padding:10px 20px;border-radius:10px;font-size:14px;font-weight:700;font-family:\'Nunito\',sans-serif;border:none;cursor:not-allowed;box-shadow:0 4px 12px rgba(232,121,160,.35);opacity:0.5;'
                    : 'background:linear-gradient(135deg,#E879A0,#7C3AED);color:#fff;padding:10px 20px;border-radius:10px;font-size:14px;font-weight:700;font-family:\'Nunito\',sans-serif;border:none;cursor:pointer;box-shadow:0 4px 12px rgba(232,121,160,.35);'"
                data-testid="settings-save-btn">
                <span x-show="!saving">Save Settings</span>
                <span x-show="saving">Saving…</span>
            </button>
        </div>

        {{-- ── Delete Confirmation Modal ── --}}
        <div x-show="showDeleteConfirm" x-transition
            style="position:fixed;inset:0;background:rgba(15,10,40,0.75);backdrop-filter:blur(8px);z-index:100;display:flex;align-items:center;justify-content:center;padding:20px;"
            @keydown.escape.window="showDeleteConfirm = false" role="dialog" aria-modal="true"
            aria-labelledby="delete-modal-title">
            <div
                style="background:#fff;border-radius:20px;max-width:440px;width:100%;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.3);">
                <div style="background:linear-gradient(135deg,#7F1D1D,#991B1B);padding:20px 24px;">
                    <h2 id="delete-modal-title" style="font-size:18px;font-weight:900;color:#fff;margin:0;">Delete Account
                    </h2>
                    <p style="font-size:13px;color:rgba(255,255,255,0.7);margin:4px 0 0;">This action is permanent and
                        cannot be undone.</p>
                </div>
                <div style="padding:24px;">
                    <p style="font-size:13px;color:#1E1033;margin:0 0 16px;">Type <strong>DELETE</strong> to confirm
                        account deletion. All your data will be permanently removed.</p>
                    <input type="text" x-model="deleteConfirmText" placeholder="Type DELETE to confirm"
                        style="width:100%;padding:10px 14px;border:1px solid #EDE9FE;border-radius:10px;font-size:13px;font-family:'Nunito',sans-serif;box-sizing:border-box;"
                        data-testid="settings-delete-confirm-input">
                    <div style="display:flex;gap:10px;margin-top:16px;justify-content:flex-end;">
                        <button @click="showDeleteConfirm = false; deleteConfirmText = '';"
                            style="background:#EDE9FE;color:#7C3AED;padding:9px 18px;border-radius:10px;font-size:13px;font-weight:700;font-family:'Nunito',sans-serif;border:none;cursor:pointer;">Cancel</button>
                        <button
                            @click="if(deleteConfirmText === 'DELETE') { $el.closest('form') || fetch('{{ route('settings.account.delete') }}', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json', 'Content-Type': 'application/json' }, body: JSON.stringify({ confirmation: 'DELETE' }) }).then(r => r.json()).then(d => { if(d.redirect) window.location.href = d.redirect; }); }"
                            :disabled="deleteConfirmText !== 'DELETE'"
                            style="background:#EF4444;color:#fff;padding:9px 18px;border-radius:10px;font-size:13px;font-weight:700;font-family:'Nunito',sans-serif;border:none;cursor:pointer;"
                            :style="deleteConfirmText !== 'DELETE' ? 'opacity:0.5;cursor:not-allowed;' : ''"
                            data-testid="settings-delete-confirm-btn">Delete Account</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
