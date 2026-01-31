/**
 * API Testing Page JavaScript
 * View-specific logic for testing external APIs (Umapyoi.net and UmamusumeDB.com)
 */

// Helper function to display results
function displayResult(elementId, status, message, data = null) {
    const element = document.getElementById(elementId);
    const statusColor =
        status === "success"
            ? "text-green-600"
            : status === "error"
              ? "text-red-600"
              : "text-yellow-600";

    let html = `<div class="${statusColor} font-semibold mb-2">${status.toUpperCase()}: ${message}</div>`;

    if (data) {
        html += `<pre class="text-xs overflow-auto max-h-96 bg-white p-2 rounded border">${JSON.stringify(data, null, 2)}</pre>`;
    }

    element.innerHTML = html;
}

// Umapyoi.net API Tests
async function testUmapyoiCharacters() {
    console.group("🧪 Testing Umapyoi Characters List");
    const url = "https://api.umapyoi.net/api/v1/character/list";
    console.log("URL:", url);

    try {
        const response = await fetch(url, {
            method: "GET",
            headers: {
                Accept: "application/json",
                "User-Agent": "UmamusumeCareerPlanner/1.0",
            },
        });

        console.log("Status:", response.status, response.statusText);
        console.log("Headers:", Object.fromEntries(response.headers.entries()));

        if (response.ok) {
            const data = await response.json();
            console.log("Response Data:", data);
            console.log(
                "Character Count:",
                Array.isArray(data) ? data.length : "Not an array",
            );
            displayResult(
                "umapyoi-results",
                "success",
                `Fetched ${Array.isArray(data) ? data.length : 0} characters`,
                data,
            );
        } else {
            const text = await response.text();
            console.error("Error Response:", text);
            displayResult(
                "umapyoi-results",
                "error",
                `HTTP ${response.status}: ${response.statusText}`,
                {
                    body: text,
                },
            );
        }
    } catch (error) {
        console.error("Fetch Error:", error);
        displayResult("umapyoi-results", "error", error.message);
    }
    console.groupEnd();
}

async function testUmapyoiCharacter() {
    console.group("🧪 Testing Umapyoi Single Character");
    const characterId = "1"; // Test with ID 1
    const url = `https://api.umapyoi.net/api/v1/character/${characterId}`;
    console.log("URL:", url);

    try {
        const response = await fetch(url);
        console.log("Status:", response.status, response.statusText);

        if (response.ok) {
            const data = await response.json();
            console.log("Response Data:", data);
            displayResult(
                "umapyoi-results",
                "success",
                `Fetched character ${characterId}`,
                data,
            );
        } else {
            const text = await response.text();
            console.error("Error Response:", text);
            displayResult(
                "umapyoi-results",
                "error",
                `HTTP ${response.status}: ${response.statusText}`,
                {
                    body: text,
                },
            );
        }
    } catch (error) {
        console.error("Fetch Error:", error);
        displayResult("umapyoi-results", "error", error.message);
    }
    console.groupEnd();
}

async function testUmapyoiSupport() {
    console.group("🧪 Testing Umapyoi Support Cards");
    const url = "https://api.umapyoi.net/api/v1/support";
    console.log("URL:", url);

    try {
        const response = await fetch(url);
        console.log("Status:", response.status, response.statusText);

        if (response.ok) {
            const data = await response.json();
            console.log("Response Data:", data);
            displayResult(
                "umapyoi-results",
                "success",
                `Fetched ${Array.isArray(data) ? data.length : 0} support cards`,
                data,
            );
        } else {
            const text = await response.text();
            console.error("Error Response:", text);
            displayResult(
                "umapyoi-results",
                "error",
                `HTTP ${response.status}: ${response.statusText}`,
                {
                    body: text,
                },
            );
        }
    } catch (error) {
        console.error("Fetch Error:", error);
        displayResult("umapyoi-results", "error", error.message);
    }
    console.groupEnd();
}

async function testUmapyoiSkills() {
    console.group("🧪 Testing Umapyoi Skills");
    const url = "https://api.umapyoi.net/api/v1/skill";
    console.log("URL:", url);

    try {
        const response = await fetch(url);
        console.log("Status:", response.status, response.statusText);

        if (response.ok) {
            const data = await response.json();
            console.log("Response Data:", data);
            displayResult(
                "umapyoi-results",
                "success",
                `Fetched ${Array.isArray(data) ? data.length : 0} skills`,
                data,
            );
        } else {
            const text = await response.text();
            console.error("Error Response:", text);
            displayResult(
                "umapyoi-results",
                "error",
                `HTTP ${response.status}: ${response.statusText}`,
                {
                    body: text,
                },
            );
        }
    } catch (error) {
        console.error("Fetch Error:", error);
        displayResult("umapyoi-results", "error", error.message);
    }
    console.groupEnd();
}

async function testUmapyoiNews() {
    console.group("🧪 Testing Umapyoi News");
    const url = "https://api.umapyoi.net/api/v1/news/latest/10";
    console.log("URL:", url);

    try {
        const response = await fetch(url);
        console.log("Status:", response.status, response.statusText);

        if (response.ok) {
            const data = await response.json();
            console.log("Response Data:", data);
            displayResult(
                "umapyoi-results",
                "success",
                `Fetched ${Array.isArray(data) ? data.length : 0} news items`,
                data,
            );
        } else {
            const text = await response.text();
            console.error("Error Response:", text);
            displayResult(
                "umapyoi-results",
                "error",
                `HTTP ${response.status}: ${response.statusText}`,
                {
                    body: text,
                },
            );
        }
    } catch (error) {
        console.error("Fetch Error:", error);
        displayResult("umapyoi-results", "error", error.message);
    }
    console.groupEnd();
}

async function testUmapyoiHealth() {
    console.group("🧪 Testing Umapyoi Health Check");
    const url = "https://api.umapyoi.net/health";
    console.log("URL:", url);

    try {
        const response = await fetch(url);
        console.log("Status:", response.status, response.statusText);

        if (response.ok) {
            const data = await response.json();
            console.log("Response Data:", data);
            displayResult("umapyoi-results", "success", "API is healthy", data);
        } else {
            const text = await response.text();
            console.error("Error Response:", text);
            displayResult(
                "umapyoi-results",
                "error",
                `HTTP ${response.status}: ${response.statusText}`,
                {
                    body: text,
                },
            );
        }
    } catch (error) {
        console.error("Fetch Error:", error);
        displayResult("umapyoi-results", "error", error.message);
    }
    console.groupEnd();
}

// UmamusumeDB.com Tests
async function testUmamusumeDBCharacter() {
    console.group("🧪 Testing UmamusumeDB Character Page");
    const url = "https://umamusumedb.com/characters/special_week_2025/";
    console.log("URL:", url);

    try {
        const response = await fetch(url, {
            mode: "no-cors",
        });
        console.log("Status:", response.status, response.statusText);
        console.log("Note: no-cors mode - limited response info available");
        displayResult(
            "umamusumedb-results",
            "info",
            "Request sent (no-cors mode). Check Network tab for details.",
            {
                note: "CORS prevents reading response. Check Network tab in DevTools.",
            },
        );
    } catch (error) {
        console.error("Fetch Error:", error);
        displayResult("umamusumedb-results", "error", error.message);
    }
    console.groupEnd();
}

async function testUmamusumeDBCard() {
    console.group("🧪 Testing UmamusumeDB Support Card Page");
    const url = "https://umamusumedb.com/cards/kitasan_black_ssr/";
    console.log("URL:", url);

    try {
        const response = await fetch(url, {
            mode: "no-cors",
        });
        console.log("Status:", response.status, response.statusText);
        displayResult(
            "umamusumedb-results",
            "info",
            "Request sent (no-cors mode). Check Network tab for details.",
            {
                note: "CORS prevents reading response. Check Network tab in DevTools.",
            },
        );
    } catch (error) {
        console.error("Fetch Error:", error);
        displayResult("umamusumedb-results", "error", error.message);
    }
    console.groupEnd();
}

async function testUmamusumeDBRobots() {
    console.group("🧪 Testing UmamusumeDB robots.txt");
    const url = "https://umamusumedb.com/robots.txt";
    console.log("URL:", url);

    try {
        const response = await fetch(url);
        console.log("Status:", response.status, response.statusText);

        if (response.ok) {
            const text = await response.text();
            console.log("robots.txt content:", text);
            displayResult(
                "umamusumedb-results",
                "success",
                "Fetched robots.txt",
                {
                    content: text,
                },
            );
        } else {
            displayResult(
                "umamusumedb-results",
                "error",
                `HTTP ${response.status}: ${response.statusText}`,
            );
        }
    } catch (error) {
        console.error("Fetch Error:", error);
        displayResult("umamusumedb-results", "error", error.message);
    }
    console.groupEnd();
}

async function testUmamusumeDBSitemap() {
    console.group("🧪 Testing UmamusumeDB sitemap");
    const url = "https://umamusumedb.com/sitemap-index.xml";
    console.log("URL:", url);

    try {
        const response = await fetch(url);
        console.log("Status:", response.status, response.statusText);

        if (response.ok) {
            const text = await response.text();
            console.log(
                "Sitemap content (first 500 chars):",
                text.substring(0, 500),
            );
            displayResult("umamusumedb-results", "success", "Fetched sitemap", {
                preview: text.substring(0, 500) + "...",
                fullLength: text.length,
            });
        } else {
            displayResult(
                "umamusumedb-results",
                "error",
                `HTTP ${response.status}: ${response.statusText}`,
            );
        }
    } catch (error) {
        console.error("Fetch Error:", error);
        displayResult("umamusumedb-results", "error", error.message);
    }
    console.groupEnd();
}

// Initialize on page load
document.addEventListener("DOMContentLoaded", () => {
    console.log(
        "%c🚀 API Testing Page Loaded",
        "color: blue; font-size: 16px; font-weight: bold",
    );
    console.log(
        "Click any button to test API endpoints. All requests will be logged here.",
    );
});

// Export functions to global scope for onclick handlers
window.testUmapyoiCharacters = testUmapyoiCharacters;
window.testUmapyoiCharacter = testUmapyoiCharacter;
window.testUmapyoiSupport = testUmapyoiSupport;
window.testUmapyoiSkills = testUmapyoiSkills;
window.testUmapyoiNews = testUmapyoiNews;
window.testUmapyoiHealth = testUmapyoiHealth;
window.testUmamusumeDBCharacter = testUmamusumeDBCharacter;
window.testUmamusumeDBCard = testUmamusumeDBCard;
window.testUmamusumeDBRobots = testUmamusumeDBRobots;
window.testUmamusumeDBSitemap = testUmamusumeDBSitemap;
