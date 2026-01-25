<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Testing - Umapyoi & UmamusumeDB</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">External API Testing</h1>

        <!-- Umapyoi.net API Tests -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-semibold mb-4">Umapyoi.net API</h2>
            <p class="text-gray-600 mb-4">Base URL: <code
                    class="bg-gray-100 px-2 py-1 rounded">https://api.umapyoi.net</code></p>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <button onclick="testUmapyoiCharacters()"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Test Characters List
                </button>
                <button onclick="testUmapyoiCharacter()"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Test Single Character
                </button>
                <button onclick="testUmapyoiSupport()"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Test Support Cards
                </button>
                <button onclick="testUmapyoiSkills()"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Test Skills
                </button>
                <button onclick="testUmapyoiNews()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Test News
                </button>
                <button onclick="testUmapyoiHealth()"
                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                    Test Health Check
                </button>
            </div>

            <div id="umapyoi-results" class="bg-gray-50 p-4 rounded border border-gray-200 min-h-[200px]">
                <p class="text-gray-500">Click a button to test an endpoint. Check the browser console (F12) for
                    detailed logs.</p>
            </div>
        </div>

        <!-- UmamusumeDB.com Tests -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-semibold mb-4">UmamusumeDB.com</h2>
            <p class="text-gray-600 mb-4">Base URL: <code
                    class="bg-gray-100 px-2 py-1 rounded">https://umamusumedb.com</code></p>
            <p class="text-yellow-600 mb-4">⚠️ Note: No public API available. Testing web pages only.</p>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <button onclick="testUmamusumeDBCharacter()"
                    class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded">
                    Test Character Page
                </button>
                <button onclick="testUmamusumeDBCard()"
                    class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded">
                    Test Support Card Page
                </button>
                <button onclick="testUmamusumeDBRobots()"
                    class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded">
                    Check robots.txt
                </button>
                <button onclick="testUmamusumeDBSitemap()"
                    class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded">
                    Check Sitemap
                </button>
            </div>

            <div id="umamusumedb-results" class="bg-gray-50 p-4 rounded border border-gray-200 min-h-[200px]">
                <p class="text-gray-500">Click a button to test. Check the browser console (F12) for detailed logs.</p>
            </div>
        </div>

        <!-- Instructions -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-2">How to Use</h3>
            <ol class="list-decimal list-inside space-y-2 text-gray-700">
                <li>Open Chrome DevTools (F12 or Right-click → Inspect)</li>
                <li>Go to the "Network" tab to see HTTP requests</li>
                <li>Go to the "Console" tab to see detailed logs</li>
                <li>Click any button above to test an API endpoint</li>
                <li>Check the Network tab for request/response details</li>
                <li>Check the Console tab for parsed JSON data</li>
            </ol>
        </div>
    </div>

    <script>
        // Helper function to display results
        function displayResult(elementId, status, message, data = null) {
            const element = document.getElementById(elementId);
            const statusColor = status === 'success' ? 'text-green-600' :
                status === 'error' ? 'text-red-600' : 'text-yellow-600';

            let html = `<div class="${statusColor} font-semibold mb-2">${status.toUpperCase()}: ${message}</div>`;

            if (data) {
                html +=
                    `<pre class="text-xs overflow-auto max-h-96 bg-white p-2 rounded border">${JSON.stringify(data, null, 2)}</pre>`;
            }

            element.innerHTML = html;
        }

        // Umapyoi.net API Tests
        async function testUmapyoiCharacters() {
            console.group('🧪 Testing Umapyoi Characters List');
            const url = 'https://api.umapyoi.net/api/v1/character/list';
            console.log('URL:', url);

            try {
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'User-Agent': 'UmamusumeCareerPlanner/1.0'
                    }
                });

                console.log('Status:', response.status, response.statusText);
                console.log('Headers:', Object.fromEntries(response.headers.entries()));

                if (response.ok) {
                    const data = await response.json();
                    console.log('Response Data:', data);
                    console.log('Character Count:', Array.isArray(data) ? data.length : 'Not an array');
                    displayResult('umapyoi-results', 'success',
                        `Fetched ${Array.isArray(data) ? data.length : 0} characters`, data);
                } else {
                    const text = await response.text();
                    console.error('Error Response:', text);
                    displayResult('umapyoi-results', 'error', `HTTP ${response.status}: ${response.statusText}`, {
                        body: text
                    });
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                displayResult('umapyoi-results', 'error', error.message);
            }
            console.groupEnd();
        }

        async function testUmapyoiCharacter() {
            console.group('🧪 Testing Umapyoi Single Character');
            const characterId = '1'; // Test with ID 1
            const url = `https://api.umapyoi.net/api/v1/character/${characterId}`;
            console.log('URL:', url);

            try {
                const response = await fetch(url);
                console.log('Status:', response.status, response.statusText);

                if (response.ok) {
                    const data = await response.json();
                    console.log('Response Data:', data);
                    displayResult('umapyoi-results', 'success', `Fetched character ${characterId}`, data);
                } else {
                    const text = await response.text();
                    console.error('Error Response:', text);
                    displayResult('umapyoi-results', 'error', `HTTP ${response.status}: ${response.statusText}`, {
                        body: text
                    });
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                displayResult('umapyoi-results', 'error', error.message);
            }
            console.groupEnd();
        }

        async function testUmapyoiSupport() {
            console.group('🧪 Testing Umapyoi Support Cards');
            const url = 'https://api.umapyoi.net/api/v1/support';
            console.log('URL:', url);

            try {
                const response = await fetch(url);
                console.log('Status:', response.status, response.statusText);

                if (response.ok) {
                    const data = await response.json();
                    console.log('Response Data:', data);
                    displayResult('umapyoi-results', 'success',
                        `Fetched ${Array.isArray(data) ? data.length : 0} support cards`, data);
                } else {
                    const text = await response.text();
                    console.error('Error Response:', text);
                    displayResult('umapyoi-results', 'error', `HTTP ${response.status}: ${response.statusText}`, {
                        body: text
                    });
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                displayResult('umapyoi-results', 'error', error.message);
            }
            console.groupEnd();
        }

        async function testUmapyoiSkills() {
            console.group('🧪 Testing Umapyoi Skills');
            const url = 'https://api.umapyoi.net/api/v1/skill';
            console.log('URL:', url);

            try {
                const response = await fetch(url);
                console.log('Status:', response.status, response.statusText);

                if (response.ok) {
                    const data = await response.json();
                    console.log('Response Data:', data);
                    displayResult('umapyoi-results', 'success',
                        `Fetched ${Array.isArray(data) ? data.length : 0} skills`, data);
                } else {
                    const text = await response.text();
                    console.error('Error Response:', text);
                    displayResult('umapyoi-results', 'error', `HTTP ${response.status}: ${response.statusText}`, {
                        body: text
                    });
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                displayResult('umapyoi-results', 'error', error.message);
            }
            console.groupEnd();
        }

        async function testUmapyoiNews() {
            console.group('🧪 Testing Umapyoi News');
            const url = 'https://api.umapyoi.net/api/v1/news/latest/10';
            console.log('URL:', url);

            try {
                const response = await fetch(url);
                console.log('Status:', response.status, response.statusText);

                if (response.ok) {
                    const data = await response.json();
                    console.log('Response Data:', data);
                    displayResult('umapyoi-results', 'success',
                        `Fetched ${Array.isArray(data) ? data.length : 0} news items`, data);
                } else {
                    const text = await response.text();
                    console.error('Error Response:', text);
                    displayResult('umapyoi-results', 'error', `HTTP ${response.status}: ${response.statusText}`, {
                        body: text
                    });
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                displayResult('umapyoi-results', 'error', error.message);
            }
            console.groupEnd();
        }

        async function testUmapyoiHealth() {
            console.group('🧪 Testing Umapyoi Health Check');
            const url = 'https://api.umapyoi.net/health';
            console.log('URL:', url);

            try {
                const response = await fetch(url);
                console.log('Status:', response.status, response.statusText);

                if (response.ok) {
                    const data = await response.json();
                    console.log('Response Data:', data);
                    displayResult('umapyoi-results', 'success', 'API is healthy', data);
                } else {
                    const text = await response.text();
                    console.error('Error Response:', text);
                    displayResult('umapyoi-results', 'error', `HTTP ${response.status}: ${response.statusText}`, {
                        body: text
                    });
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                displayResult('umapyoi-results', 'error', error.message);
            }
            console.groupEnd();
        }

        // UmamusumeDB.com Tests
        async function testUmamusumeDBCharacter() {
            console.group('🧪 Testing UmamusumeDB Character Page');
            const url = 'https://umamusumedb.com/characters/special_week_2025/';
            console.log('URL:', url);

            try {
                const response = await fetch(url, {
                    mode: 'no-cors'
                });
                console.log('Status:', response.status, response.statusText);
                console.log('Note: no-cors mode - limited response info available');
                displayResult('umamusumedb-results', 'info',
                    'Request sent (no-cors mode). Check Network tab for details.', {
                        note: 'CORS prevents reading response. Check Network tab in DevTools.'
                    });
            } catch (error) {
                console.error('Fetch Error:', error);
                displayResult('umamusumedb-results', 'error', error.message);
            }
            console.groupEnd();
        }

        async function testUmamusumeDBCard() {
            console.group('🧪 Testing UmamusumeDB Support Card Page');
            const url = 'https://umamusumedb.com/cards/kitasan_black_ssr/';
            console.log('URL:', url);

            try {
                const response = await fetch(url, {
                    mode: 'no-cors'
                });
                console.log('Status:', response.status, response.statusText);
                displayResult('umamusumedb-results', 'info',
                    'Request sent (no-cors mode). Check Network tab for details.', {
                        note: 'CORS prevents reading response. Check Network tab in DevTools.'
                    });
            } catch (error) {
                console.error('Fetch Error:', error);
                displayResult('umamusumedb-results', 'error', error.message);
            }
            console.groupEnd();
        }

        async function testUmamusumeDBRobots() {
            console.group('🧪 Testing UmamusumeDB robots.txt');
            const url = 'https://umamusumedb.com/robots.txt';
            console.log('URL:', url);

            try {
                const response = await fetch(url);
                console.log('Status:', response.status, response.statusText);

                if (response.ok) {
                    const text = await response.text();
                    console.log('robots.txt content:', text);
                    displayResult('umamusumedb-results', 'success', 'Fetched robots.txt', {
                        content: text
                    });
                } else {
                    displayResult('umamusumedb-results', 'error', `HTTP ${response.status}: ${response.statusText}`);
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                displayResult('umamusumedb-results', 'error', error.message);
            }
            console.groupEnd();
        }

        async function testUmamusumeDBSitemap() {
            console.group('🧪 Testing UmamusumeDB sitemap');
            const url = 'https://umamusumedb.com/sitemap-index.xml';
            console.log('URL:', url);

            try {
                const response = await fetch(url);
                console.log('Status:', response.status, response.statusText);

                if (response.ok) {
                    const text = await response.text();
                    console.log('Sitemap content (first 500 chars):', text.substring(0, 500));
                    displayResult('umamusumedb-results', 'success', 'Fetched sitemap', {
                        preview: text.substring(0, 500) + '...',
                        fullLength: text.length
                    });
                } else {
                    displayResult('umamusumedb-results', 'error', `HTTP ${response.status}: ${response.statusText}`);
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                displayResult('umamusumedb-results', 'error', error.message);
            }
            console.groupEnd();
        }

        // Log initial message
        console.log('%c🚀 API Testing Page Loaded', 'color: blue; font-size: 16px; font-weight: bold');
        console.log('Click any button to test API endpoints. All requests will be logged here.');
    </script>
</body>

</html>
