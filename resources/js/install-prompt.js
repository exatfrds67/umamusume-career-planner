/**
 * PWA Install Prompt Manager
 *
 * Tracks user engagement and shows a custom install prompt
 * when criteria are met: 2+ visits, 5+ minutes, 3+ pages.
 *
 * @see Requirements: NFR-PWA-05
 */

const STORAGE_KEY = 'ucp_install_metrics';
const PROMPT_DISMISSED_KEY = 'ucp_install_dismissed';
const PROMPT_INSTALLED_KEY = 'ucp_install_completed';

const CRITERIA = {
    minVisits: 2,
    minTimeSpentMs: 5 * 60 * 1000,
    minPages: 3,
};

let deferredPrompt = null;

/**
 * Get stored engagement metrics.
 *
 * @returns {{ visits: number, totalTimeMs: number, pagesViewed: string[], firstVisit: string, lastVisit: string, sessionStart: number }}
 */
function getMetrics() {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (raw) {
            return JSON.parse(raw);
        }
    } catch {
        // Corrupted data
    }

    return {
        visits: 0,
        totalTimeMs: 0,
        pagesViewed: [],
        firstVisit: new Date().toISOString(),
        lastVisit: new Date().toISOString(),
        sessionStart: Date.now(),
    };
}

/**
 * Save engagement metrics.
 *
 * @param {object} metrics
 */
function saveMetrics(metrics) {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(metrics));
    } catch {
        // Storage full or unavailable
    }
}

/**
 * Record a new page view.
 *
 * @param {string} path
 */
export function recordPageView(path) {
    const metrics = getMetrics();

    if (!metrics.pagesViewed.includes(path)) {
        metrics.pagesViewed.push(path);
    }

    metrics.lastVisit = new Date().toISOString();
    saveMetrics(metrics);
}

/**
 * Record a new visit/session.
 */
export function recordVisit() {
    const metrics = getMetrics();
    metrics.visits += 1;
    metrics.sessionStart = Date.now();
    metrics.lastVisit = new Date().toISOString();
    saveMetrics(metrics);
}

/**
 * Update time spent on the current session.
 */
export function updateTimeSpent() {
    const metrics = getMetrics();
    const sessionDuration = Date.now() - (metrics.sessionStart || Date.now());
    metrics.totalTimeMs += sessionDuration;
    metrics.sessionStart = Date.now();
    saveMetrics(metrics);
}

/**
 * Check if all engagement criteria are met.
 *
 * @returns {boolean}
 */
export function criteriaMetForInstall() {
    const metrics = getMetrics();
    const sessionDuration = Date.now() - (metrics.sessionStart || Date.now());
    const totalTime = metrics.totalTimeMs + sessionDuration;

    return (
        metrics.visits >= CRITERIA.minVisits &&
        totalTime >= CRITERIA.minTimeSpentMs &&
        metrics.pagesViewed.length >= CRITERIA.minPages
    );
}

/**
 * Check if the user has already dismissed or installed the app.
 *
 * @returns {boolean}
 */
export function hasUserDismissedOrInstalled() {
    try {
        return (
            localStorage.getItem(PROMPT_DISMISSED_KEY) === 'true' ||
            localStorage.getItem(PROMPT_INSTALLED_KEY) === 'true'
        );
    } catch {
        return false;
    }
}

/**
 * Check if a deferred install prompt is available.
 *
 * @returns {boolean}
 */
export function isInstallAvailable() {
    return deferredPrompt !== null;
}

/**
 * Trigger the native install prompt.
 *
 * @returns {Promise<string>} - 'accepted' or 'dismissed'
 */
export async function showInstallPrompt() {
    if (!deferredPrompt) {
        return 'unavailable';
    }

    deferredPrompt.prompt();
    const { outcome } = await deferredPrompt.userChoice;

    if (outcome === 'accepted') {
        localStorage.setItem(PROMPT_INSTALLED_KEY, 'true');
    }

    deferredPrompt = null;
    return outcome;
}

/**
 * Dismiss the install prompt and remember the decision.
 */
export function dismissInstallPrompt() {
    localStorage.setItem(PROMPT_DISMISSED_KEY, 'true');
    deferredPrompt = null;
}

/**
 * Check if the app is already running as a PWA.
 *
 * @returns {boolean}
 */
export function isRunningAsPwa() {
    return (
        window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true
    );
}

/**
 * Get engagement summary for debugging or display.
 *
 * @returns {{ visits: number, timeSpentMinutes: number, pagesViewed: number, criteriaMet: boolean }}
 */
export function getEngagementSummary() {
    const metrics = getMetrics();
    const sessionDuration = Date.now() - (metrics.sessionStart || Date.now());

    return {
        visits: metrics.visits,
        timeSpentMinutes: Math.round((metrics.totalTimeMs + sessionDuration) / 60000),
        pagesViewed: metrics.pagesViewed.length,
        criteriaMet: criteriaMetForInstall(),
    };
}

/**
 * Initialize the install prompt system.
 * Called once on app load.
 */
export function initInstallPrompt() {
    if (isRunningAsPwa()) {
        return;
    }

    recordVisit();
    recordPageView(window.location.pathname);

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;

        if (criteriaMetForInstall() && !hasUserDismissedOrInstalled()) {
            window.dispatchEvent(new CustomEvent('pwa-install-ready'));
        }
    });

    window.addEventListener('appinstalled', () => {
        localStorage.setItem(PROMPT_INSTALLED_KEY, 'true');
        deferredPrompt = null;
        window.dispatchEvent(new CustomEvent('pwa-installed'));
    });

    setInterval(() => {
        updateTimeSpent();

        if (deferredPrompt && criteriaMetForInstall() && !hasUserDismissedOrInstalled()) {
            window.dispatchEvent(new CustomEvent('pwa-install-ready'));
        }
    }, 60000);
}

/**
 * Alpine.js component for install prompt banner.
 */
export const installPromptManager = () => ({
    showBanner: false,
    isInstalled: false,
    engagement: { visits: 0, timeSpentMinutes: 0, pagesViewed: 0, criteriaMet: false },

    init() {
        this.isInstalled = isRunningAsPwa() || localStorage.getItem(PROMPT_INSTALLED_KEY) === 'true';
        this.engagement = getEngagementSummary();

        window.addEventListener('pwa-install-ready', () => {
            this.showBanner = true;
            this.engagement = getEngagementSummary();
        });

        window.addEventListener('pwa-installed', () => {
            this.showBanner = false;
            this.isInstalled = true;
        });
    },

    async install() {
        const result = await showInstallPrompt();
        if (result === 'accepted') {
            this.showBanner = false;
            this.isInstalled = true;
        }
    },

    dismiss() {
        dismissInstallPrompt();
        this.showBanner = false;
    },
});
