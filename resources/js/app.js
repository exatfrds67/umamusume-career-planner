import "./bootstrap";

// Import Alpine.js
import Alpine from "alpinejs";

// Import EventBus
import { eventBus } from "./core/EventBus.js";

// Import design system components
import CharacterAvatar from "./components/CharacterAvatar.js";
import BackgroundSystem from "./components/BackgroundSystem.js";
import AssetOptimization from "./components/AssetOptimization.js";
import DesignSystem from "./components/DesignSystem.js";
import ResponsiveSystem from "./components/ResponsiveSystem.js";
import AccessibilitySystem from "./components/AccessibilitySystem.js";
import AccessibilitySettings from "./components/AccessibilitySettings.js";

// Initialize Alpine.js
window.Alpine = Alpine;
Alpine.start();

// Initialize global event bus
window.eventBus = eventBus;

// PWA Service Worker Registration and Management
class PWAManager {
    constructor() {
        this.isOnline = navigator.onLine;
        this.serviceWorker = null;
        this.updateAvailable = false;

        this.init();
    }

    /**
     * Initialize PWA functionality
     */
    async init() {
        // Register service worker
        await this.registerServiceWorker();

        // Setup offline detection
        this.setupOfflineDetection();

        // Setup background sync registration
        this.setupBackgroundSync();

        // Setup update notifications
        this.setupUpdateNotifications();
    }

    /**
     * Register service worker with proper lifecycle management
     */
    async registerServiceWorker() {
        if ("serviceWorker" in navigator) {
            try {
                console.log("🔧 Registering service worker...");

                const registration = await navigator.serviceWorker.register(
                    "/sw.js",
                    {
                        scope: "/",
                    }
                );

                this.serviceWorker = registration;

                // Handle service worker updates
                registration.addEventListener("updatefound", () => {
                    const newWorker = registration.installing;

                    if (newWorker) {
                        newWorker.addEventListener("statechange", () => {
                            if (
                                newWorker.state === "installed" &&
                                navigator.serviceWorker.controller
                            ) {
                                // New service worker is available
                                this.updateAvailable = true;
                                this.notifyUpdateAvailable();
                            }
                        });
                    }
                });

                // Handle service worker messages
                navigator.serviceWorker.addEventListener("message", (event) => {
                    this.handleServiceWorkerMessage(event);
                });

                console.log("✅ Service worker registered successfully");

                // Announce to accessibility system
                if (
                    typeof AccessibilitySystem !== "undefined" &&
                    AccessibilitySystem.announce
                ) {
                    AccessibilitySystem.announce("PWA features enabled");
                }
            } catch (error) {
                console.error("❌ Service worker registration failed:", error);
            }
        } else {
            console.warn("⚠️ Service workers not supported in this browser");
        }
    }

    /**
     * Setup offline detection and UI updates
     */
    setupOfflineDetection() {
        // Create offline indicator
        this.createOfflineIndicator();

        // Listen for online/offline events
        window.addEventListener("online", () => {
            this.isOnline = true;
            this.updateOfflineStatus();
            this.syncPendingData();
            if (
                typeof AccessibilitySystem !== "undefined" &&
                AccessibilitySystem.announce
            ) {
                AccessibilitySystem.announce("Connection restored");
            }
        });

        window.addEventListener("offline", () => {
            this.isOnline = false;
            this.updateOfflineStatus();
            if (
                typeof AccessibilitySystem !== "undefined" &&
                AccessibilitySystem.announce
            ) {
                AccessibilitySystem.announce(
                    "Connection lost - working offline",
                    "assertive"
                );
            }
        });

        // Initial status update
        this.updateOfflineStatus();
    }

    /**
     * Create offline status indicator
     */
    createOfflineIndicator() {
        const indicator = document.createElement("div");
        indicator.id = "offline-indicator";
        indicator.className =
            "fixed top-0 left-0 right-0 bg-amber-500 text-white text-center py-2 px-4 text-sm font-medium transform -translate-y-full transition-transform duration-300 z-50";
        indicator.innerHTML = `
            <div class="flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                <span>You're offline - some features may be limited</span>
            </div>
        `;

        document.body.appendChild(indicator);
    }

    /**
     * Update offline status indicator
     */
    updateOfflineStatus() {
        const indicator = document.getElementById("offline-indicator");
        if (!indicator) return;

        if (this.isOnline) {
            indicator.classList.add("-translate-y-full");
            document.body.classList.remove("offline-mode");
        } else {
            indicator.classList.remove("-translate-y-full");
            document.body.classList.add("offline-mode");
        }
    }

    /**
     * Setup background sync for future data synchronization
     */
    setupBackgroundSync() {
        if (
            "serviceWorker" in navigator &&
            "sync" in window.ServiceWorkerRegistration.prototype
        ) {
            console.log("✅ Background sync supported");

            // Register background sync for character data
            this.registerBackgroundSync("character-data-sync");
        } else {
            console.warn("⚠️ Background sync not supported");
        }
    }

    /**
     * Register background sync
     * @param {string} tag - Sync tag identifier
     */
    async registerBackgroundSync(tag) {
        try {
            if (this.serviceWorker && this.serviceWorker.sync) {
                await this.serviceWorker.sync.register(tag);
                console.log(`✅ Background sync registered: ${tag}`);
            }
        } catch (error) {
            console.error(
                `❌ Background sync registration failed: ${tag}`,
                error
            );
        }
    }

    /**
     * Setup update notifications
     */
    setupUpdateNotifications() {
        // Check for updates periodically
        setInterval(() => {
            if (this.serviceWorker) {
                this.serviceWorker.update();
            }
        }, 60000); // Check every minute
    }

    /**
     * Notify user about available updates
     */
    notifyUpdateAvailable() {
        const updateBanner = document.createElement("div");
        updateBanner.id = "update-banner";
        updateBanner.className =
            "fixed bottom-4 right-4 bg-blue-600 text-white p-4 rounded-lg shadow-lg max-w-sm z-50";
        updateBanner.innerHTML = `
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                </svg>
                <div class="flex-1">
                    <p class="font-medium">Update Available</p>
                    <p class="text-sm opacity-90 mt-1">A new version is ready to install</p>
                    <div class="flex gap-2 mt-3">
                        <button id="update-now" class="bg-white text-blue-600 px-3 py-1 rounded text-sm font-medium hover:bg-blue-50">
                            Update Now
                        </button>
                        <button id="update-later" class="text-white opacity-75 hover:opacity-100 px-3 py-1 rounded text-sm">
                            Later
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(updateBanner);

        // Handle update actions
        document.getElementById("update-now").addEventListener("click", () => {
            this.applyUpdate();
        });

        document
            .getElementById("update-later")
            .addEventListener("click", () => {
                updateBanner.remove();
            });

        // Auto-remove after 10 seconds if no action
        setTimeout(() => {
            if (document.getElementById("update-banner")) {
                updateBanner.remove();
            }
        }, 10000);
    }

    /**
     * Apply service worker update
     */
    async applyUpdate() {
        if (this.serviceWorker && this.serviceWorker.waiting) {
            // Tell the waiting service worker to skip waiting
            this.serviceWorker.waiting.postMessage({ type: "SKIP_WAITING" });

            // Reload the page to activate the new service worker
            window.location.reload();
        }
    }

    /**
     * Handle messages from service worker
     * @param {MessageEvent} event - Message event
     */
    handleServiceWorkerMessage(event) {
        const { type, payload } = event.data;

        switch (type) {
            case "CACHE_UPDATED":
                console.log("📦 Cache updated:", payload);
                break;
            case "OFFLINE_FALLBACK":
                console.log("📱 Offline fallback activated");
                break;
            case "BACKGROUND_SYNC":
                console.log("🔄 Background sync completed:", payload);
                break;
            default:
                console.log("📨 Service worker message:", event.data);
        }
    }

    /**
     * Sync pending data when connection is restored
     */
    async syncPendingData() {
        if (this.isOnline && this.serviceWorker) {
            try {
                // Trigger background sync for pending data
                await this.registerBackgroundSync("character-data-sync");
                console.log("🔄 Syncing pending data...");
            } catch (error) {
                console.error("❌ Failed to sync pending data:", error);
            }
        }
    }

    /**
     * Check if app is running as PWA
     * @returns {boolean} Whether app is installed as PWA
     */
    isPWA() {
        return (
            window.matchMedia("(display-mode: standalone)").matches ||
            window.navigator.standalone === true
        );
    }

    /**
     * Get connection status
     * @returns {boolean} Whether device is online
     */
    getConnectionStatus() {
        return this.isOnline;
    }
}

/**
 * UmamusumeCareerPlanner Application
 * Comprehensive design system with existing assets integration and PWA support
 */
class UmamusumeCareerPlanner {
    constructor() {
        this.components = {
            CharacterAvatar,
            BackgroundSystem,
            AssetOptimization,
            DesignSystem,
            ResponsiveSystem,
            AccessibilitySystem,
        };

        // Initialize PWA manager
        this.pwa = new PWAManager();

        // Initialize accessibility settings
        this.accessibilitySettings = new AccessibilitySettings();

        this.init();
    }

    /**
     * Initialize the application
     */
    init() {
        // Wait for DOM to be ready
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", () => this.setup());
        } else {
            this.setup();
        }
    }

    /**
     * Setup the application
     */
    setup() {
        console.log("🎮 UmamusumeCareerPlanner Design System Initialized");
        console.log(
            `📱 PWA Mode: ${this.pwa.isPWA() ? "Installed" : "Browser"}`
        );
        console.log(
            `🌐 Connection: ${
                this.pwa.getConnectionStatus() ? "Online" : "Offline"
            }`
        );

        // Initialize background system
        this.setupBackgroundSystem();

        // Initialize asset optimization
        this.setupAssetOptimization();

        // Initialize responsive system
        this.setupResponsiveSystem();

        // Initialize accessibility system
        this.setupAccessibilitySystem();

        // Setup demo components if in development
        if (this.isDevelopment()) {
            this.setupDemoComponents();
        }

        // Setup global event listeners
        this.setupGlobalEvents();

        // Announce system ready
        if (
            typeof AccessibilitySystem !== "undefined" &&
            AccessibilitySystem.announce
        ) {
            AccessibilitySystem.announce(
                "UmamusumeCareerPlanner design system ready"
            );
        }
    }

    /**
     * Setup background system
     */
    setupBackgroundSystem() {
        // Add observer for background changes
        BackgroundSystem.addObserver((type, data) => {
            if (type === "theme") {
                console.log(`🎨 Theme changed to: ${data}`);
            }
            if (type === "background") {
                console.log(
                    `🖼️ Background updated: ${data.theme} ${data.device}`
                );
            }
        });

        // Create theme toggle if container exists
        const themeToggleContainer = document.querySelector(
            "#theme-toggle-container"
        );
        if (themeToggleContainer) {
            BackgroundSystem.createThemeToggle(themeToggleContainer);
        }
    }

    /**
     * Setup asset optimization
     */
    setupAssetOptimization() {
        // Asset optimization is automatically initialized as singleton
        // Add observer for optimization events if needed
        console.log("✅ Asset optimization system initialized");

        // Preload critical images for better performance
        const criticalImages = [
            "/images/app_bg/uma_musume_race_planner_bg_light_1536x1028.png",
            "/images/app_bg/uma_musume_race_planner_bg_dark_1536x1028.png",
            "/images/app_bg/uma_musume_race_planner_bg_light_1028x1536.png",
            "/images/app_bg/uma_musume_race_planner_bg_dark_1028x1536.png",
        ];

        AssetOptimization.preloadImages(criticalImages);
    }

    /**
     * Setup responsive system
     */
    setupResponsiveSystem() {
        // Add observer for breakpoint changes
        ResponsiveSystem.addObserver((type, data) => {
            if (type === "breakpoint") {
                console.log(
                    `📱 Breakpoint changed: ${data.previous} → ${data.current}`
                );
                document.body.setAttribute("data-breakpoint", data.current);
            }
        });

        // Set initial breakpoint
        document.body.setAttribute(
            "data-breakpoint",
            ResponsiveSystem.currentBreakpoint
        );
    }

    /**
     * Setup accessibility system
     */
    setupAccessibilitySystem() {
        // Initialize accessibility system first
        if (typeof AccessibilitySystem !== "undefined") {
            // System is already initialized as singleton
            console.log("✅ Accessibility system initialized");
        }

        // Create accessibility controls if container exists
        const accessibilityContainer = document.querySelector(
            "#accessibility-controls"
        );
        if (accessibilityContainer) {
            this.createAccessibilityControls(accessibilityContainer);
        }

        // Create accessibility settings panel
        const settingsContainer =
            document.querySelector("#accessibility-settings-container") ||
            document.body;
        this.accessibilitySettings.createSettingsPanel(settingsContainer);

        // Create accessibility settings trigger in header/navigation
        const headerContainer = document.querySelector(
            ".accessibility-trigger-container"
        );
        if (headerContainer) {
            this.accessibilitySettings.createSettingsTrigger(headerContainer);
        }

        // Ensure live region exists
        if (!document.getElementById("aria-live-region")) {
            const liveRegion = document.createElement("div");
            liveRegion.id = "aria-live-region";
            liveRegion.className = "sr-only";
            liveRegion.setAttribute("aria-live", "polite");
            liveRegion.setAttribute("aria-atomic", "true");
            document.body.appendChild(liveRegion);
        }
    }

    /**
     * Create accessibility controls
     * @param {HTMLElement} container - Container element
     */
    createAccessibilityControls(container) {
        const controls = document.createElement("div");
        controls.className = "flex gap-2 items-center";

        // High contrast toggle
        const highContrastBtn = DesignSystem.Button.create({
            text: "High Contrast",
            variant: "outline",
            size: "sm",
            onClick: () => {
                if (
                    typeof AccessibilitySystem !== "undefined" &&
                    AccessibilitySystem.toggleHighContrast
                ) {
                    AccessibilitySystem.toggleHighContrast();
                }
            },
        });

        // Reduced motion toggle
        const reducedMotionBtn = DesignSystem.Button.create({
            text: "Reduce Motion",
            variant: "outline",
            size: "sm",
            onClick: () => {
                if (
                    typeof AccessibilitySystem !== "undefined" &&
                    AccessibilitySystem.toggleReducedMotion
                ) {
                    AccessibilitySystem.toggleReducedMotion();
                }
            },
        });

        // Text size controls
        const textSizeContainer = document.createElement("div");
        textSizeContainer.className = "flex items-center gap-1";

        const decreaseTextBtn = DesignSystem.Button.create({
            text: "A-",
            variant: "outline",
            size: "sm",
            ariaLabel: "Decrease text size",
            onClick: () => {
                if (
                    typeof AccessibilitySystem !== "undefined" &&
                    AccessibilitySystem.setTextSize
                ) {
                    const current =
                        AccessibilitySystem.preferences?.textSize || 100;
                    AccessibilitySystem.setTextSize(current - 10);
                }
            },
        });

        const increaseTextBtn = DesignSystem.Button.create({
            text: "A+",
            variant: "outline",
            size: "sm",
            ariaLabel: "Increase text size",
            onClick: () => {
                if (
                    typeof AccessibilitySystem !== "undefined" &&
                    AccessibilitySystem.setTextSize
                ) {
                    const current =
                        AccessibilitySystem.preferences?.textSize || 100;
                    AccessibilitySystem.setTextSize(current + 10);
                }
            },
        });

        textSizeContainer.appendChild(decreaseTextBtn);
        textSizeContainer.appendChild(increaseTextBtn);

        controls.appendChild(highContrastBtn);
        controls.appendChild(reducedMotionBtn);
        controls.appendChild(textSizeContainer);

        container.appendChild(controls);
    }

    /**
     * Setup demo components for development
     */
    setupDemoComponents() {
        // Create demo character selection if container exists
        const characterDemoContainer =
            document.querySelector("#character-demo");
        if (characterDemoContainer) {
            this.createCharacterDemo(characterDemoContainer);
        }

        // Create design system demo if container exists
        const designSystemDemo = document.querySelector("#design-system-demo");
        if (designSystemDemo) {
            this.createDesignSystemDemo(designSystemDemo);
        }
    }

    /**
     * Create character demo
     * @param {HTMLElement} container - Container element
     */
    createCharacterDemo(container) {
        const demoCharacters = [
            { id: 1, name: "Agnes Tachyon", scenario_type: "ura_finale" },
            { id: 2, name: "Gold Ship", scenario_type: "unity_cup" },
            { id: 3, name: "Narita Brian", scenario_type: "ura_finale" },
            { id: 4, name: "Tokai Teio", scenario_type: "unity_cup" },
            { id: 5, name: "Vodka", scenario_type: "ura_finale" },
            { id: 6, name: "Silence Suzuka", scenario_type: "ura_finale" },
        ];

        CharacterAvatar.createCharacterSelection({
            characters: demoCharacters,
            onSelect: (character) => {
                if (
                    typeof AccessibilitySystem !== "undefined" &&
                    AccessibilitySystem.announce
                ) {
                    AccessibilitySystem.announce(`Selected ${character.name}`);
                }
                console.log("Selected character:", character);
            },
            container,
        });
    }

    /**
     * Create design system demo
     * @param {HTMLElement} container - Container element
     */
    createDesignSystemDemo(container) {
        // Button examples
        const buttonSection = document.createElement("section");
        buttonSection.innerHTML =
            '<h3 class="text-lg font-semibold mb-4">Buttons</h3>';

        const buttonContainer = document.createElement("div");
        buttonContainer.className = "flex gap-2 flex-wrap mb-6";

        const buttons = [
            { text: "Primary", variant: "primary" },
            { text: "Secondary", variant: "secondary" },
            { text: "Outline", variant: "outline" },
            { text: "Small", variant: "primary", size: "sm" },
            { text: "Large", variant: "primary", size: "lg" },
            { text: "Disabled", variant: "primary", disabled: true },
        ];

        buttons.forEach((config) => {
            const btn = DesignSystem.Button.create(config);
            buttonContainer.appendChild(btn);
        });

        buttonSection.appendChild(buttonContainer);

        // Card example
        const cardSection = document.createElement("section");
        cardSection.innerHTML =
            '<h3 class="text-lg font-semibold mb-4">Cards</h3>';

        const card = DesignSystem.Card.create({
            title: "Character Stats",
            content: "<p>Speed: 1200 | Stamina: 800 | Power: 900</p>",
            actions: [
                DesignSystem.Button.create({
                    text: "Edit",
                    variant: "primary",
                    size: "sm",
                }),
                DesignSystem.Button.create({
                    text: "Delete",
                    variant: "outline",
                    size: "sm",
                }),
            ],
        });

        cardSection.appendChild(card);

        // Form example
        const formSection = document.createElement("section");
        formSection.innerHTML =
            '<h3 class="text-lg font-semibold mb-4">Forms</h3>';

        const form = document.createElement("form");
        form.className = "max-w-md";

        const nameInput = DesignSystem.Form.createInput({
            id: "demo-name",
            label: "Character Name",
            placeholder: "Enter character name",
            required: true,
            helpText: "Choose a unique name for your character",
        });

        const scenarioSelect = DesignSystem.Form.createSelect({
            id: "demo-scenario",
            label: "Scenario Type",
            required: true,
            options: [
                { value: "", text: "Select scenario..." },
                { value: "ura_finale", text: "URA Finale" },
                { value: "unity_cup", text: "Unity Cup" },
            ],
        });

        const enabledCheckbox = DesignSystem.Form.createCheckbox({
            id: "demo-enabled",
            label: "Enable advanced features",
        });

        form.appendChild(nameInput);
        form.appendChild(scenarioSelect);
        form.appendChild(enabledCheckbox);

        formSection.appendChild(form);

        // Grade badges example
        const badgeSection = document.createElement("section");
        badgeSection.innerHTML =
            '<h3 class="text-lg font-semibold mb-4">Grade Badges</h3>';

        const badgeContainer = document.createElement("div");
        badgeContainer.className = "flex gap-2 flex-wrap";

        const grades = ["SS", "S", "A", "B", "C", "D", "E", "F", "G"];
        grades.forEach((grade) => {
            const badge = DesignSystem.Badge.createGrade(grade);
            badgeContainer.appendChild(badge);
        });

        badgeSection.appendChild(badgeContainer);

        // Assemble demo
        container.appendChild(buttonSection);
        container.appendChild(cardSection);
        container.appendChild(formSection);
        container.appendChild(badgeSection);
    }

    /**
     * Setup global event listeners
     */
    setupGlobalEvents() {
        // Handle form submissions
        document.addEventListener("submit", (e) => {
            const form = e.target;
            if (form.classList.contains("ajax-form")) {
                e.preventDefault();
                this.handleAjaxForm(form);
            }
        });

        // Handle modal triggers
        document.addEventListener("click", (e) => {
            const trigger = e.target.closest("[data-modal-target]");
            if (trigger) {
                e.preventDefault();
                const modalId = trigger.getAttribute("data-modal-target");
                DesignSystem.Modal.show(modalId);
            }
        });
    }

    /**
     * Handle AJAX form submission
     * @param {HTMLFormElement} form - Form element
     */
    async handleAjaxForm(form) {
        const formData = new FormData(form);
        const submitButton = form.querySelector('[type="submit"]');

        // Show loading state
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = "Loading...";
        }

        try {
            const response = await fetch(form.action, {
                method: form.method,
                body: formData,
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]'
                    )?.content,
                },
            });

            const result = await response.json();

            if (response.ok) {
                if (
                    typeof AccessibilitySystem !== "undefined" &&
                    AccessibilitySystem.announce
                ) {
                    AccessibilitySystem.announce("Form submitted successfully");
                }
                DesignSystem.Alert.toast({
                    message: result.message || "Success!",
                    type: "success",
                });
            } else {
                throw new Error(result.message || "Form submission failed");
            }
        } catch (error) {
            console.error("Form submission error:", error);
            if (
                typeof AccessibilitySystem !== "undefined" &&
                AccessibilitySystem.announce
            ) {
                AccessibilitySystem.announce(
                    `Error: ${error.message}`,
                    "assertive"
                );
            }
            DesignSystem.Alert.toast({
                message: error.message,
                type: "error",
            });
        } finally {
            // Restore button state
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent =
                    submitButton.getAttribute("data-original-text") || "Submit";
            }
        }
    }

    /**
     * Check if in development mode
     * @returns {boolean} Whether in development mode
     */
    isDevelopment() {
        return (
            window.location.hostname === "localhost" ||
            window.location.hostname === "127.0.0.1" ||
            window.location.hostname.includes("local")
        );
    }

    /**
     * Get component instance
     * @param {string} name - Component name
     * @returns {*} Component instance
     */
    getComponent(name) {
        return this.components[name];
    }

    /**
     * Get PWA manager instance
     * @returns {PWAManager} PWA manager instance
     */
    getPWA() {
        return this.pwa;
    }
}

// Initialize the application
const app = new UmamusumeCareerPlanner();

// Make components available globally for development
if (app.isDevelopment()) {
    window.UmamusumeCareerPlanner = app;
    window.CharacterAvatar = CharacterAvatar;
    window.BackgroundSystem = BackgroundSystem;
    window.AssetOptimization = AssetOptimization;
    window.DesignSystem = DesignSystem;
    window.ResponsiveSystem = ResponsiveSystem;
    window.AccessibilitySystem = AccessibilitySystem;
    window.AccessibilitySettings = AccessibilitySettings;
    window.PWAManager = PWAManager;
}

export default app;
