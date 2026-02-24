/**
 * k6 Load Test Script
 *
 * Tests the application under varying concurrent user loads.
 * Measures p50, p95, p99 response times for critical endpoints.
 *
 * Requirements: NFR-P-09
 *
 * Usage:
 *   k6 run scripts/load-test.js
 *   k6 run scripts/load-test.js --env BASE_URL=http://localhost:8000
 *   k6 run scripts/load-test.js --env STAGE=smoke
 *
 * Stages:
 *   smoke    - 10 users, 1 minute
 *   load     - 10→50→100→200 users over 10 minutes
 *   stress   - ramp to 200 users, hold, then ramp down
 *   soak     - 50 users for 30 minutes
 */

import http from 'k6/http';
import { check, sleep, group } from 'k6';
import { Counter, Rate, Trend } from 'k6/metrics';

// Custom metrics
const pageLoadDuration = new Trend('page_load_duration', true);
const apiResponseDuration = new Trend('api_response_duration', true);
const errorRate = new Rate('errors');
const requestCount = new Counter('total_requests');

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8000';
const STAGE = __ENV.STAGE || 'load';

// Test configurations per stage
const stageConfigs = {
    smoke: {
        stages: [
            { duration: '30s', target: 10 },
            { duration: '30s', target: 10 },
        ],
        thresholds: {
            http_req_duration: ['p(95)<3000'],
            errors: ['rate<0.05'],
        },
    },
    load: {
        stages: [
            { duration: '2m', target: 10 },
            { duration: '3m', target: 50 },
            { duration: '3m', target: 100 },
            { duration: '2m', target: 200 },
        ],
        thresholds: {
            http_req_duration: ['p(95)<5000', 'p(99)<10000'],
            errors: ['rate<0.10'],
        },
    },
    stress: {
        stages: [
            { duration: '2m', target: 50 },
            { duration: '3m', target: 100 },
            { duration: '5m', target: 200 },
            { duration: '3m', target: 200 },
            { duration: '2m', target: 0 },
        ],
        thresholds: {
            http_req_duration: ['p(95)<8000'],
            errors: ['rate<0.15'],
        },
    },
    soak: {
        stages: [
            { duration: '2m', target: 50 },
            { duration: '26m', target: 50 },
            { duration: '2m', target: 0 },
        ],
        thresholds: {
            http_req_duration: ['p(95)<5000'],
            errors: ['rate<0.05'],
        },
    },
};

const config = stageConfigs[STAGE] || stageConfigs.load;

export const options = {
    stages: config.stages,
    thresholds: {
        ...config.thresholds,
        page_load_duration: ['p(95)<5000'],
        api_response_duration: ['p(95)<3000'],
    },
};

/**
 * Register and authenticate a test user.
 */
export function setup() {
    const timestamp = Date.now();
    const email = `loadtest-${timestamp}@example.com`;
    const password = 'LoadTest123!';

    // Register user
    const registerRes = http.post(`${BASE_URL}/api/register`, JSON.stringify({
        name: `Load Test User ${timestamp}`,
        email: email,
        password: password,
        password_confirmation: password,
    }), {
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    });

    let token = '';
    if (registerRes.status === 201 || registerRes.status === 200) {
        const body = JSON.parse(registerRes.body);
        token = body.token || body.data?.token || '';
    }

    // If registration failed, try login
    if (!token) {
        const loginRes = http.post(`${BASE_URL}/api/login`, JSON.stringify({
            email: email,
            password: password,
        }), {
            headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        });

        if (loginRes.status === 200) {
            const body = JSON.parse(loginRes.body);
            token = body.token || body.data?.token || '';
        }
    }

    return { token, email };
}

/**
 * Main test scenario — simulates typical user behavior.
 */
export default function (data) {
    const headers = {
        'Content-Type': 'application/json',
        Accept: 'application/json',
    };

    if (data.token) {
        headers.Authorization = `Bearer ${data.token}`;
    }

    group('Public Pages', function () {
        const welcomeRes = http.get(`${BASE_URL}/`);
        pageLoadDuration.add(welcomeRes.timings.duration);
        requestCount.add(1);
        check(welcomeRes, {
            'welcome page status 200': (r) => r.status === 200,
        }) || errorRate.add(1);

        sleep(1);

        const loginRes = http.get(`${BASE_URL}/login`);
        pageLoadDuration.add(loginRes.timings.duration);
        requestCount.add(1);
        check(loginRes, {
            'login page status 200': (r) => r.status === 200,
        }) || errorRate.add(1);

        sleep(1);
    });

    if (data.token) {
        group('Authenticated Pages', function () {
            const dashRes = http.get(`${BASE_URL}/dashboard`, { headers });
            pageLoadDuration.add(dashRes.timings.duration);
            requestCount.add(1);
            check(dashRes, {
                'dashboard loads': (r) => r.status === 200 || r.status === 302,
            }) || errorRate.add(1);

            sleep(1);
        });

        group('API Endpoints', function () {
            const meRes = http.get(`${BASE_URL}/api/me`, { headers });
            apiResponseDuration.add(meRes.timings.duration);
            requestCount.add(1);
            check(meRes, {
                'GET /api/me status 200': (r) => r.status === 200,
            }) || errorRate.add(1);

            sleep(0.5);

            const charsRes = http.get(`${BASE_URL}/api/characters`, { headers });
            apiResponseDuration.add(charsRes.timings.duration);
            requestCount.add(1);
            check(charsRes, {
                'GET /api/characters status 200': (r) => r.status === 200,
            }) || errorRate.add(1);

            sleep(0.5);

            const skillsRes = http.get(`${BASE_URL}/api/skills`, { headers });
            apiResponseDuration.add(skillsRes.timings.duration);
            requestCount.add(1);
            check(skillsRes, {
                'GET /api/skills status 200': (r) => r.status === 200,
            }) || errorRate.add(1);

            sleep(0.5);
        });
    }

    sleep(Math.random() * 2 + 1);
}

/**
 * Print summary after test run.
 */
export function teardown(data) {
    console.log(`\n=== Load Test Complete ===`);
    console.log(`Stage: ${STAGE}`);
    console.log(`Base URL: ${BASE_URL}`);
    console.log(`Test User: ${data.email}`);
}
