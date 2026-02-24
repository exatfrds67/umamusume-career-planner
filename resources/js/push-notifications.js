/**
 * Push Notifications Client
 *
 * Handles push notification subscription, unsubscription,
 * and permission management for the PWA.
 *
 * Requirements: NFR-PWA-04
 */

const PUSH_API_BASE = '/api/v1/push';

/**
 * Check if push notifications are supported
 */
export function isPushSupported() {
    return 'serviceWorker' in navigator
        && 'PushManager' in window
        && 'Notification' in window;
}

/**
 * Get current notification permission status
 * @returns {'granted'|'denied'|'default'}
 */
export function getPermissionStatus() {
    if (!isPushSupported()) {
        return 'denied';
    }

    return Notification.permission;
}

/**
 * Request notification permission from the user
 * @returns {Promise<'granted'|'denied'|'default'>}
 */
export async function requestPermission() {
    if (!isPushSupported()) {
        return 'denied';
    }

    return await Notification.requestPermission();
}

/**
 * Subscribe to push notifications
 * @param {string} vapidPublicKey - VAPID public key from server
 * @returns {Promise<{success: boolean, message: string}>}
 */
export async function subscribeToPush(vapidPublicKey) {
    if (!isPushSupported()) {
        return { success: false, message: 'Push notifications not supported.' };
    }

    const permission = await requestPermission();

    if (permission !== 'granted') {
        return { success: false, message: 'Notification permission denied.' };
    }

    try {
        const registration = await navigator.serviceWorker.ready;

        const existingSub = await registration.pushManager.getSubscription();
        if (existingSub) {
            await sendSubscriptionToServer(existingSub);
            return { success: true, message: 'Already subscribed.' };
        }

        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
        });

        await sendSubscriptionToServer(subscription);

        return { success: true, message: 'Successfully subscribed to push notifications.' };
    } catch (error) {
        console.error('[Push] Subscription failed:', error);
        return { success: false, message: `Subscription failed: ${error.message}` };
    }
}

/**
 * Unsubscribe from push notifications
 * @returns {Promise<{success: boolean, message: string}>}
 */
export async function unsubscribeFromPush() {
    try {
        const registration = await navigator.serviceWorker.ready;
        const subscription = await registration.pushManager.getSubscription();

        if (!subscription) {
            return { success: true, message: 'No active subscription.' };
        }

        await removeSubscriptionFromServer(subscription.endpoint);
        await subscription.unsubscribe();

        return { success: true, message: 'Successfully unsubscribed.' };
    } catch (error) {
        console.error('[Push] Unsubscribe failed:', error);
        return { success: false, message: `Unsubscribe failed: ${error.message}` };
    }
}

/**
 * Check if the user is currently subscribed
 * @returns {Promise<boolean>}
 */
export async function isSubscribed() {
    if (!isPushSupported()) {
        return false;
    }

    try {
        const registration = await navigator.serviceWorker.ready;
        const subscription = await registration.pushManager.getSubscription();
        return subscription !== null;
    } catch {
        return false;
    }
}

/**
 * Update notification preferences
 * @param {Object} preferences
 * @returns {Promise<{success: boolean, message: string}>}
 */
export async function updatePreferences(preferences) {
    try {
        const registration = await navigator.serviceWorker.ready;
        const subscription = await registration.pushManager.getSubscription();

        if (!subscription) {
            return { success: false, message: 'No active subscription.' };
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        const response = await fetch(`${PUSH_API_BASE}/preferences`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
            },
            body: JSON.stringify({
                endpoint: subscription.endpoint,
                preferences,
            }),
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        return { success: true, message: 'Preferences updated.' };
    } catch (error) {
        console.error('[Push] Preferences update failed:', error);
        return { success: false, message: `Update failed: ${error.message}` };
    }
}

/**
 * Send subscription data to the server
 * @param {PushSubscription} subscription
 */
async function sendSubscriptionToServer(subscription) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const key = subscription.getKey('p256dh');
    const auth = subscription.getKey('auth');

    const response = await fetch(`${PUSH_API_BASE}/subscribe`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken || '',
        },
        body: JSON.stringify({
            endpoint: subscription.endpoint,
            keys: {
                p256dh: key ? btoa(String.fromCharCode(...new Uint8Array(key))) : '',
                auth: auth ? btoa(String.fromCharCode(...new Uint8Array(auth))) : '',
            },
            contentEncoding: (PushManager.supportedContentEncodings || ['aesgcm'])[0],
        }),
    });

    if (!response.ok) {
        throw new Error(`Server returned ${response.status}`);
    }
}

/**
 * Remove subscription from server
 * @param {string} endpoint
 */
async function removeSubscriptionFromServer(endpoint) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    await fetch(`${PUSH_API_BASE}/unsubscribe`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken || '',
        },
        body: JSON.stringify({ endpoint }),
    });
}

/**
 * Convert a base64 URL string to a Uint8Array (for applicationServerKey)
 * @param {string} base64String
 * @returns {Uint8Array}
 */
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding)
        .replace(/-/g, '+')
        .replace(/_/g, '/');

    const rawData = atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; i++) {
        outputArray[i] = rawData.charCodeAt(i);
    }

    return outputArray;
}

/**
 * Alpine.js component for push notification UI
 */
export function pushNotificationManager() {
    return {
        supported: false,
        subscribed: false,
        permission: 'default',
        loading: false,
        message: '',

        async init() {
            this.supported = isPushSupported();
            this.permission = getPermissionStatus();
            this.subscribed = await isSubscribed();
        },

        async toggle() {
            this.loading = true;
            this.message = '';

            try {
                if (this.subscribed) {
                    const result = await unsubscribeFromPush();
                    this.message = result.message;
                    this.subscribed = !result.success;
                } else {
                    const vapidKey = document.querySelector('meta[name="vapid-public-key"]')?.content;

                    if (!vapidKey) {
                        this.message = 'Push notifications not configured.';
                        return;
                    }

                    const result = await subscribeToPush(vapidKey);
                    this.message = result.message;
                    this.subscribed = result.success;
                    this.permission = getPermissionStatus();
                }
            } finally {
                this.loading = false;
            }
        },
    };
}
