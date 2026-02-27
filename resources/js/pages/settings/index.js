/**
 * Settings Page
 * Handles all settings tabs: account, appearance, integrations,
 * AI, accessibility, notifications, advanced, and danger zone.
 *
 * Expects window.pageData.settings to be injected by the Blade view with:
 *   - user: { name, email }
 *   - prefs, accessSettings, notifPrefs, aiSettings
 */

// Uses window.Alpine set by app.js (Livewire-bundled Alpine instance)
document.addEventListener("alpine:init", () => {
    Alpine.data("settingsData", () => {
        const dataElement = document.getElementById("settings-data");
        const serverData = dataElement
            ? JSON.parse(dataElement.textContent)
            : {};
        const user = serverData.user || {};
        const prefs = serverData.prefs || {};
        const accessSettings = serverData.accessSettings || {};
        const notifPrefs = serverData.notifPrefs || {};
        const aiSettings = serverData.aiSettings || {};

        return {
            active: "account",
            showPasswordModal: false,
            showDeleteModal: false,
            deleteConfirmation: "",
            saved: false,
            savedMessage: "Settings saved successfully",
            errorMessage: "",

            // Profile
            name: user.name || "",
            email: user.email || "",
            language: prefs.language || "en",
            timezone: prefs.timezone || "Asia/Tokyo",

            // Appearance
            theme: localStorage.getItem("theme") || prefs.theme || "system",
            fontSize: prefs.font_size || 100,

            // Password modal
            currentPassword: "",
            newPassword: "",
            confirmPassword: "",
            passwordError: "",

            /**
             * Initialize toggle states from saved user preferences.
             * Reads from server-rendered data then applies to DOM.
             */
            initToggles() {
                const map = {
                    analytics: prefs.analytics ?? false,
                    "cloud-backup": prefs.cloud_backup ?? false,
                    "cloud-ai": prefs.cloud_ai ?? false,
                    umapyoi: prefs.umapyoi ?? true,
                    umamusumedb: prefs.umamusumedb ?? true,
                    "two-factor": prefs.two_factor ?? false,
                    "compact-mode": prefs.compact_mode ?? false,
                    animations: prefs.animations ?? true,
                    "show-model": aiSettings.show_model ?? true,
                    "high-contrast": accessSettings.high_contrast ?? false,
                    "reduced-motion": accessSettings.reduced_motion ?? false,
                    "screen-reader-opt":
                        accessSettings.screen_reader_optimization ?? false,
                    "training-notif": notifPrefs.training_alerts ?? true,
                    "race-notif": notifPrefs.race_reminders ?? true,
                    "goal-notif": notifPrefs.goal_progress ?? true,
                    "budget-notif": notifPrefs.budget_alerts ?? true,
                    "in-app-notif": notifPrefs.in_app ?? true,
                    "sound-notif": notifPrefs.sound ?? false,
                    "auto-accept": prefs.auto_accept_ai ?? false,
                    "skill-hints": prefs.skill_hints ?? true,
                    "sp-budget": prefs.sp_budget_alerts ?? true,
                    "lazy-loading": prefs.lazy_loading ?? true,
                    "debug-mode": prefs.debug_mode ?? false,
                    "error-reporting": prefs.error_reporting ?? false,
                };

                // Apply the visual state to each toggle
                Object.entries(map).forEach(([id, isOn]) => {
                    const btn = document.getElementById(id);
                    if (!btn) {
                        return;
                    }
                    const thumb = btn.querySelector('[aria-hidden="true"]');
                    btn.setAttribute("aria-checked", String(isOn));
                    if (isOn) {
                        btn.classList.add("bg-primary-600");
                        btn.classList.remove("bg-gray-200", "dark:bg-gray-600");
                        if (thumb) {
                            thumb.classList.add("translate-x-5");
                            thumb.classList.remove("translate-x-0");
                        }
                    } else {
                        btn.classList.remove("bg-primary-600");
                        btn.classList.add("bg-gray-200");
                        if (thumb) {
                            thumb.classList.remove("translate-x-5");
                            thumb.classList.add("translate-x-0");
                        }
                    }
                });

                // Wire the event delegation for all toggles
                this.$el.addEventListener("click", function (e) {
                    const btn = e.target.closest('[role="switch"]');
                    if (!btn) {
                        return;
                    }
                    const isOn = btn.getAttribute("aria-checked") === "true";
                    btn.setAttribute("aria-checked", String(!isOn));
                    const thumb = btn.querySelector('[aria-hidden="true"]');
                    if (isOn) {
                        btn.classList.remove("bg-primary-600");
                        btn.classList.add("bg-gray-200");
                        if (thumb) {
                            thumb.classList.remove("translate-x-5");
                            thumb.classList.add("translate-x-0");
                        }
                    } else {
                        btn.classList.add("bg-primary-600");
                        btn.classList.remove("bg-gray-200");
                        if (thumb) {
                            thumb.classList.add("translate-x-5");
                            thumb.classList.remove("translate-x-0");
                        }
                    }
                });

                // Apply compact mode if enabled
                if (prefs.compact_mode) {
                    document.body.classList.add("compact");
                }

                // Apply font size
                if (prefs.font_size && prefs.font_size !== 100) {
                    document.documentElement.style.fontSize =
                        prefs.font_size / 100 + "rem";
                }
            },

            /**
             * Apply theme: 'light' | 'dark' | 'system'
             */
            applyTheme(t) {
                this.theme = t;
                if (t === "system") {
                    localStorage.removeItem("theme");
                    const prefersDark = window.matchMedia(
                        "(prefers-color-scheme: dark)",
                    ).matches;
                    if (prefersDark) {
                        document.documentElement.classList.add("dark");
                    } else {
                        document.documentElement.classList.remove("dark");
                    }
                } else {
                    localStorage.setItem("theme", t);
                    if (t === "dark") {
                        document.documentElement.classList.add("dark");
                    } else {
                        document.documentElement.classList.remove("dark");
                    }
                }
            },

            /**
             * Apply font size percentage to the document root.
             */
            applyFontSize(val) {
                this.fontSize = parseInt(val, 10);
                document.documentElement.style.fontSize =
                    this.fontSize / 100 + "rem";
            },

            /**
             * Clear all localStorage keys (except theme) and show toast.
             */
            clearLocalCache() {
                const theme = localStorage.getItem("theme");
                localStorage.clear();
                if (theme) {
                    localStorage.setItem("theme", theme);
                }
                this.savedMessage = "Local cache cleared";
                this.saved = true;
                setTimeout(() => {
                    this.saved = false;
                    this.savedMessage = "Settings saved successfully";
                }, 3000);
            },

            /**
             * Collect all toggle states from the DOM.
             */
            getToggleStates() {
                const result = {};
                document
                    .querySelectorAll('[role="switch"][id]')
                    .forEach((btn) => {
                        result[btn.id] =
                            btn.getAttribute("aria-checked") === "true";
                    });
                return result;
            },

            /**
             * Save only the account profile fields (name, email, language, timezone).
             */
            async saveAccount() {
                const resp = await fetch("/settings", {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]',
                        ).content,
                        Accept: "application/json",
                    },
                    body: JSON.stringify({
                        name: this.name,
                        email: this.email,
                        preferences: {
                            language: this.language,
                            timezone: this.timezone,
                        },
                    }),
                });
                if (resp.ok) {
                    this.savedMessage = "Profile saved";
                    this.saved = true;
                    setTimeout(() => {
                        this.saved = false;
                        this.savedMessage = "Settings saved successfully";
                    }, 3000);
                } else {
                    const data = await resp.json();
                    const firstError = data.errors
                        ? Object.values(data.errors)[0][0]
                        : data.message || "Failed to save";
                    alert(firstError);
                }
            },

            /**
             * Save ALL settings across every tab.
             */
            async saveSettings() {
                const toggles = this.getToggleStates();
                const prefsPayload = {
                    language: this.language,
                    timezone: this.timezone,
                    theme: this.theme,
                    compact_mode: toggles["compact-mode"] ?? false,
                    animations: toggles["animations"] ?? true,
                    font_size: this.fontSize,
                    analytics: toggles["analytics"] ?? false,
                    cloud_backup: toggles["cloud-backup"] ?? false,
                    cloud_ai: toggles["cloud-ai"] ?? false,
                    umapyoi: toggles["umapyoi"] ?? true,
                    umamusumedb: toggles["umamusumedb"] ?? true,
                    two_factor: toggles["two-factor"] ?? false,
                    auto_accept_ai: toggles["auto-accept"] ?? false,
                    skill_hints: toggles["skill-hints"] ?? true,
                    sp_budget_alerts: toggles["sp-budget"] ?? true,
                    lazy_loading: toggles["lazy-loading"] ?? true,
                    debug_mode: toggles["debug-mode"] ?? false,
                    error_reporting: toggles["error-reporting"] ?? false,
                };

                const defaultFacilityEl =
                    document.getElementById("default-facility");
                const cardSortingEl = document.getElementById("card-sorting");
                const autoSaveEl = document.getElementById("auto-save");
                const backupFrequencyEl =
                    document.getElementById("backup-frequency");
                const colorblindModeEl =
                    document.getElementById("colorblind-mode");
                const quietStartEl = document.getElementById("quiet-start");
                const quietEndEl = document.getElementById("quiet-end");

                if (defaultFacilityEl) {
                    prefsPayload.default_facility = defaultFacilityEl.value;
                }
                if (cardSortingEl) {
                    prefsPayload.card_sorting = cardSortingEl.value;
                }
                if (autoSaveEl) {
                    prefsPayload.auto_save = autoSaveEl.value;
                }
                if (backupFrequencyEl) {
                    prefsPayload.backup_frequency = backupFrequencyEl.value;
                }

                const aiProviderEl = document.getElementById("ai-provider");
                const aiModelEl = document.getElementById("ai-model");
                const recFrequencyEl = document.getElementById(
                    "recommendation-frequency",
                );
                const explanationDetailEl =
                    document.getElementById("explanation-detail");
                const dailyLimitEl = document.getElementById("daily-limit");
                const weeklyLimitEl = document.getElementById("weekly-limit");
                const monthlyLimitEl = document.getElementById("monthly-limit");

                const aiSettingsPayload = {
                    provider: aiProviderEl ? aiProviderEl.value : "ollama",
                    model: aiModelEl ? aiModelEl.value : "llama3.3",
                    recommendation_frequency: recFrequencyEl
                        ? recFrequencyEl.value
                        : "per_turn",
                    explanation_detail: explanationDetailEl
                        ? explanationDetailEl.value
                        : "detailed",
                    show_model: toggles["show-model"] ?? true,
                    daily_limit: dailyLimitEl
                        ? parseFloat(dailyLimitEl.value) || 0.5
                        : 0.5,
                    weekly_limit: weeklyLimitEl
                        ? parseFloat(weeklyLimitEl.value) || 5.0
                        : 5.0,
                    monthly_limit: monthlyLimitEl
                        ? parseFloat(monthlyLimitEl.value) || 10.0
                        : 10.0,
                };

                const colorBlindValue = colorblindModeEl
                    ? colorblindModeEl.value
                          .toLowerCase()
                          .split(" ")[0]
                          .replace("(", "")
                          .replace(")", "")
                    : "none";

                const accessibilitySettingsPayload = {
                    high_contrast: toggles["high-contrast"] ?? false,
                    reduced_motion: toggles["reduced-motion"] ?? false,
                    screen_reader_optimization:
                        toggles["screen-reader-opt"] ?? false,
                    color_blind_mode: [
                        "none",
                        "deuteranopia",
                        "protanopia",
                        "tritanopia",
                    ].includes(colorBlindValue)
                        ? colorBlindValue
                        : "none",
                };

                const notificationPreferencesPayload = {
                    training_alerts: toggles["training-notif"] ?? true,
                    race_reminders: toggles["race-notif"] ?? true,
                    goal_progress: toggles["goal-notif"] ?? true,
                    budget_alerts: toggles["budget-notif"] ?? true,
                    in_app: toggles["in-app-notif"] ?? true,
                    sound: toggles["sound-notif"] ?? false,
                    quiet_hours_start: quietStartEl
                        ? quietStartEl.value
                        : "22:00",
                    quiet_hours_end: quietEndEl ? quietEndEl.value : "08:00",
                };

                const resp = await fetch("/settings", {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]',
                        ).content,
                        Accept: "application/json",
                    },
                    body: JSON.stringify({
                        name: this.name,
                        email: this.email,
                        preferences: prefsPayload,
                        ai_settings: aiSettingsPayload,
                        accessibility_settings: accessibilitySettingsPayload,
                        notification_preferences:
                            notificationPreferencesPayload,
                    }),
                });

                if (resp.ok) {
                    this.savedMessage = "Settings saved successfully";
                    this.saved = true;
                    setTimeout(() => {
                        this.saved = false;
                    }, 3000);
                } else {
                    const data = await resp.json();
                    const firstError = data.errors
                        ? Object.values(data.errors)[0][0]
                        : data.message || "Failed to save settings";
                    alert(firstError);
                }
            },

            /**
             * Change the user's password via the API.
             */
            async changePassword() {
                this.passwordError = "";
                const resp = await fetch("/settings/password", {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]',
                        ).content,
                        Accept: "application/json",
                    },
                    body: JSON.stringify({
                        current_password: this.currentPassword,
                        password: this.newPassword,
                        password_confirmation: this.confirmPassword,
                    }),
                });
                if (resp.ok) {
                    this.showPasswordModal = false;
                    this.currentPassword = "";
                    this.newPassword = "";
                    this.confirmPassword = "";
                    this.savedMessage = "Password changed successfully";
                    this.saved = true;
                    setTimeout(() => {
                        this.saved = false;
                        this.savedMessage = "Settings saved successfully";
                    }, 3000);
                } else {
                    const data = await resp.json();
                    this.passwordError = data.errors
                        ? Object.values(data.errors)[0][0]
                        : data.message || "Failed to change password";
                }
            },

            /**
             * Reset all settings to application defaults and persist them.
             */
            async resetDefaults() {
                if (
                    !confirm(
                        "Reset all settings to defaults? This cannot be undone.",
                    )
                ) {
                    return;
                }

                // Reset Alpine state
                this.theme = "system";
                this.fontSize = 100;
                this.language = "en";
                this.timezone = "Asia/Tokyo";

                // Apply theme system preference immediately
                this.applyTheme("system");
                document.documentElement.style.fontSize = "1rem";

                // Reset all toggle switches to their default positions
                const defaults = {
                    analytics: false,
                    "cloud-backup": false,
                    "cloud-ai": false,
                    umapyoi: true,
                    umamusumedb: true,
                    "two-factor": false,
                    "compact-mode": false,
                    animations: true,
                    "show-model": true,
                    "high-contrast": false,
                    "reduced-motion": false,
                    "screen-reader-opt": false,
                    "training-notif": true,
                    "race-notif": true,
                    "goal-notif": true,
                    "budget-notif": true,
                    "in-app-notif": true,
                    "sound-notif": false,
                    "auto-accept": false,
                    "skill-hints": true,
                    "sp-budget": true,
                    "lazy-loading": true,
                    "debug-mode": false,
                    "error-reporting": false,
                };

                Object.entries(defaults).forEach(([id, isOn]) => {
                    const btn = document.getElementById(id);
                    if (!btn) {
                        return;
                    }
                    const thumb = btn.querySelector('[aria-hidden="true"]');
                    btn.setAttribute("aria-checked", String(isOn));
                    if (isOn) {
                        btn.classList.add("bg-primary-600");
                        btn.classList.remove("bg-gray-200", "dark:bg-gray-600");
                        if (thumb) {
                            thumb.classList.add("translate-x-5");
                            thumb.classList.remove("translate-x-0");
                        }
                    } else {
                        btn.classList.remove("bg-primary-600");
                        btn.classList.add("bg-gray-200");
                        if (thumb) {
                            thumb.classList.remove("translate-x-5");
                            thumb.classList.add("translate-x-0");
                        }
                    }
                });

                // Save the defaults to the server
                await this.saveSettings();
                this.savedMessage = "Settings reset to defaults";
                this.saved = true;
                setTimeout(() => {
                    this.saved = false;
                    this.savedMessage = "Settings saved successfully";
                }, 3000);
            },

            /**
             * Delete the user's account after typing "DELETE".
             */
            async deleteAccount() {
                if (this.deleteConfirmation !== "DELETE") {
                    return;
                }
                const resp = await fetch("/settings/account", {
                    method: "DELETE",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]',
                        ).content,
                        Accept: "application/json",
                    },
                    body: JSON.stringify({
                        confirmation: this.deleteConfirmation,
                    }),
                });
                if (resp.ok) {
                    const data = await resp.json();
                    window.location.href = data.redirect || "/";
                } else {
                    const data = await resp.json();
                    alert(data.message || "Failed to delete account");
                }
            },
        };
    });
});
