/**
 * App Update Flow Manager
 *
 * Detects service worker updates, shows "Update available" notification,
 * and implements automatic update after 24 hours.
 *
 * @see Requirements: NFR-PWA-01
 */

const UPDATE_DETECTED_KEY = 'ucp_update_detected_at';
const AUTO_UPDATE_DELAY_MS = 24 * 60 * 60 * 1000;

let pendingWorker = null;

/**
 * Get the timestamp when an update was first detected.
 *
 * @returns {number|null}
 */
function getUpdateDetectedAt() {
    try {
        const val = localStorage.getItem(UPDATE_DETECTED_KEY);
        return val ? parseInt(val, 10) : null;
    } catch {
        return null;
    }
}

/**
 * Record when an update was detected.
 */
function setUpdateDetectedAt() {
    try {
        if (!localStorage.getItem(UPDATE_DETECTED_KEY)) {
            localStorage.setItem(UPDATE_DETECTED_KEY, Date.now().toString());
        }
    } catch {
        // Storage unavailable
    }
}

/**
 * Clear the update detection timestamp after applying.
 */
function clearUpdateDetected() {
    try {
        localStorage.removeItem(UPDATE_DETECTED_KEY);
    } catch {
        // Storage unavailable
    }
}

/**
 * Check if auto-update threshold has been reached.
 *
 * @returns {boolean}
 */
export function shouldAutoUpdate() {
    const detectedAt = getUpdateDetectedAt();
    if (!detectedAt) {
        return false;
    }

    return (Date.now() - detectedAt) >= AUTO_UPDATE_DELAY_MS;
}

/**
 * Apply the pending update by sending SKIP_WAITING to the new SW.
 */
export function applyUpdate() {
    if (pendingWorker) {
        pendingWorker.postMessage({ type: 'SKIP_WAITING' });
        clearUpdateDetected();
    }
}

/**
 * Dismiss the update notification without applying.
 */
export function dismissUpdate() {
    window.dispatchEvent(new CustomEvent('app-update-dismissed'));
}

/**
 * Set up update detection on a service worker registration.
 *
 * @param {ServiceWorkerRegistration} registration
 */
export function setupUpdateDetection(registration) {
    registration.addEventListener('updatefound', () => {
        const newWorker = registration.installing;
        if (!newWorker) {
            return;
        }

        newWorker.addEventListener('statechange', () => {
            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                pendingWorker = newWorker;
                setUpdateDetectedAt();

                if (shouldAutoUpdate()) {
                    applyUpdate();
                } else {
                    window.dispatchEvent(new CustomEvent('app-update-available'));
                }
            }
        });
    });

    navigator.serviceWorker.addEventListener('controllerchange', () => {
        clearUpdateDetected();
        window.location.reload();
    });

    checkForPendingAutoUpdate(registration);
}

/**
 * Check if there is a pending update that should be auto-applied.
 *
 * @param {ServiceWorkerRegistration} registration
 */
function checkForPendingAutoUpdate(registration) {
    if (registration.waiting) {
        pendingWorker = registration.waiting;

        if (shouldAutoUpdate()) {
            applyUpdate();
        } else {
            window.dispatchEvent(new CustomEvent('app-update-available'));
        }
    }
}

/**
 * Schedule periodic checks for auto-update threshold.
 */
export function scheduleAutoUpdateCheck() {
    setInterval(() => {
        if (pendingWorker && shouldAutoUpdate()) {
            applyUpdate();
        }
    }, 60 * 60 * 1000);
}

/**
 * Alpine.js component for update notification banner.
 */
export const appUpdateManager = () => ({
    showUpdateBanner: false,
    updateDetectedAt: null,

    init() {
        window.addEventListener('app-update-available', () => {
            this.showUpdateBanner = true;
            this.updateDetectedAt = getUpdateDetectedAt();
        });

        window.addEventListener('app-update-dismissed', () => {
            this.showUpdateBanner = false;
        });
    },

    update() {
        applyUpdate();
        this.showUpdateBanner = false;
    },

    dismiss() {
        dismissUpdate();
        this.showUpdateBanner = false;
    },

    /**
     * Get hours remaining until auto-update.
     *
     * @returns {number}
     */
    hoursUntilAutoUpdate() {
        if (!this.updateDetectedAt) {
            return 24;
        }

        const elapsed = Date.now() - this.updateDetectedAt;
        const remaining = Math.max(0, AUTO_UPDATE_DELAY_MS - elapsed);
        return Math.ceil(remaining / (60 * 60 * 1000));
    },
});
