<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Testing - Umapyoi & UmamusumeDB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/js/pages/test-api.js'])
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
</body>

</html>
