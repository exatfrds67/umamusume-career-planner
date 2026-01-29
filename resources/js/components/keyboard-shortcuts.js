/**
 * keyboardShortcuts - Alpine.js component for keyboard shortcut management
 * 
 * Purpose: Handle application keyboard shortcuts and display help
 * 
 * Features:
 *   - Global keyboard shortcut registry
 *   - Customizable shortcuts
 *   - Help dialog display
 *   - Platform-aware (Ctrl/Cmd)
 *   - Accessibility support
 * 
 * Usage:
 *   <div x-data="keyboardShortcuts()" @init="init()">
 *       <!-- shortcuts display -->
 *   </div>
 */

export function keyboardShortcuts() {
    return {
        // State
        shortcuts: [
            {
                id: 'search',
                key: 'K',
                ctrl: true,
                label: 'Search Plans',
                action: 'openSearch',
            },
            {
                id: 'new-plan',
                key: 'N',
                ctrl: true,
                label: 'New Plan',
                action: 'newPlan',
            },
            {
                id: 'save',
                key: 'S',
                ctrl: true,
                label: 'Save Plan',
                action: 'savePlan',
            },
            {
                id: 'export',
                key: 'E',
                ctrl: true,
                shift: true,
                label: 'Export',
                action: 'exportPlan',
            },
            {
                id: 'import',
                key: 'I',
                ctrl: true,
                shift: true,
                label: 'Import',
                action: 'importPlan',
            },
            {
                id: 'settings',
                key: ',',
                ctrl: true,
                label: 'Settings',
                action: 'openSettings',
            },
            {
                id: 'help',
                key: '?',
                label: 'Help',
                action: 'showHelp',
            },
            {
                id: 'escape',
                key: 'Escape',
                label: 'Close Dialog',
                action: 'closeDialog',
            },
        ],
        customShortcuts: {},
        showHelpDialog: false,
        lastAction: null,
        isMac: /Mac|iPhone|iPad|iPod/.test(navigator.platform),

        // Initialize
        init() {
            this.loadCustomShortcuts();
            this.registerKeyboardListener();
        },

        // Register global keyboard listener
        registerKeyboardListener() {
            window.addEventListener('keydown', (e) => {
                this.handleKeyDown(e);
            });
        },

        // Handle keydown events
        handleKeyDown(event) {
            // Get the shortcut that matches this key combination
            const shortcut = this.shortcuts.find(s => {
                if (s.key !== event.key.toUpperCase() && s.key !== event.key) {
                    return false;
                }

                const ctrlCheck =
                    (s.ctrl || false) === (event.ctrlKey || event.metaKey);
                const shiftCheck = (s.shift || false) === event.shiftKey;
                const altCheck = (s.alt || false) === event.altKey;

                return ctrlCheck && shiftCheck && altCheck;
            });

            if (shortcut) {
                event.preventDefault();
                this.executeAction(shortcut.action);
            }
        },

        // Execute shortcut action
        executeAction(action) {
            this.lastAction = action;

            switch (action) {
                case 'openSearch':
                    this.$dispatch('shortcut-search', { action });
                    break;
                case 'newPlan':
                    this.$dispatch('shortcut-new-plan', { action });
                    break;
                case 'savePlan':
                    this.$dispatch('shortcut-save', { action });
                    break;
                case 'exportPlan':
                    this.$dispatch('shortcut-export', { action });
                    break;
                case 'importPlan':
                    this.$dispatch('shortcut-import', { action });
                    break;
                case 'openSettings':
                    this.$dispatch('shortcut-settings', { action });
                    break;
                case 'showHelp':
                    this.showHelpDialog = true;
                    break;
                case 'closeDialog':
                    this.showHelpDialog = false;
                    this.$dispatch('shortcut-close', { action });
                    break;
                default:
                    break;
            }

            this.$dispatch('shortcut-executed', { action });
        },

        // Customize shortcut
        customizeShortcut(id, newKey) {
            const shortcut = this.shortcuts.find(s => s.id === id);
            if (shortcut) {
                this.customShortcuts[id] = newKey;
                this.saveCustomShortcuts();
                this.$dispatch('shortcut-customized', { id, newKey });
            }
        },

        // Load custom shortcuts
        loadCustomShortcuts() {
            const saved = localStorage.getItem('customShortcuts');
            if (saved) {
                this.customShortcuts = JSON.parse(saved);
                // Apply custom shortcuts to registry
                Object.entries(this.customShortcuts).forEach(([id, key]) => {
                    const shortcut = this.shortcuts.find(s => s.id === id);
                    if (shortcut && typeof key === 'string') {
                        shortcut.key = key;
                    }
                });
            }
        },

        // Save custom shortcuts
        saveCustomShortcuts() {
            localStorage.setItem('customShortcuts', JSON.stringify(this.customShortcuts));
        },

        // Format shortcut display
        formatShortcut(shortcut) {
            const parts = [];

            if (shortcut.ctrl) {
                parts.push(this.isMac ? '⌘' : 'Ctrl');
            }
            if (shortcut.shift) {
                parts.push('Shift');
            }
            if (shortcut.alt) {
                parts.push(this.isMac ? '⌥' : 'Alt');
            }

            parts.push(shortcut.key.toUpperCase());
            return parts.join('+');
        },

        // Reset to defaults
        resetShortcuts() {
            this.customShortcuts = {};
            localStorage.removeItem('customShortcuts');
            this.init();
            this.$dispatch('shortcuts-reset');
        },
    };
}
