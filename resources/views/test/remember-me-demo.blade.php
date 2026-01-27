@extends('layouts.guest')

@section('title', 'Remember Me Demo - Umamusume Career Planner')

@section('content')
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl w-full space-y-8">
            <div class="glass-card rounded-2xl p-8">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        Remember Me Functionality Demo
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Testing the "Remember Me" checkbox functionality
                    </p>
                </div>

                <div class="space-y-6">
                    <!-- Demo Form -->
                    <form class="space-y-4" action="#" method="POST" onsubmit="return false;">
                        @csrf

                        <div>
                            <label for="demo-email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Email address
                            </label>
                            <input id="demo-email" name="email" type="email" value="demo@example.com" readonly
                                class="appearance-none relative block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-white rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm bg-gray-50 dark:bg-gray-700">
                        </div>

                        <x-password-input id="demo-password" name="password" label="Password" value="demo-password"
                            readonly />

                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input id="demo-remember" name="remember" type="checkbox"
                                    class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 dark:border-gray-600 rounded"
                                    onchange="updateRememberStatus()">
                                <label for="demo-remember" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                                    Remember me
                                </label>
                            </div>
                        </div>

                        <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">
                                Remember Me Status:
                            </h3>
                            <p id="remember-status" class="text-sm text-blue-700 dark:text-blue-300">
                                Checkbox is currently: <span class="font-semibold">unchecked</span>
                            </p>
                            <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                                Form value: <span id="form-value" class="font-mono">false</span>
                            </p>
                        </div>

                        <button type="button" onclick="simulateLogin()"
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                            Simulate Login (Demo Only)
                        </button>
                    </form>

                    <!-- Information Panel -->
                    <div class="mt-8 p-6 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <h3 class="text-lg font-medium text-green-800 dark:text-green-200 mb-4">
                            ✅ Remember Me Functionality Status
                        </h3>

                        <div class="space-y-3 text-sm text-green-700 dark:text-green-300">
                            <div class="flex items-start">
                                <span class="text-green-500 mr-2">✓</span>
                                <div>
                                    <strong>Database Column:</strong> <code>remember_token</code> exists in
                                    <code>ucp_users</code> table
                                </div>
                            </div>

                            <div class="flex items-start">
                                <span class="text-green-500 mr-2">✓</span>
                                <div>
                                    <strong>User Model:</strong> Properly configured with <code>remember_token</code>
                                    property
                                </div>
                            </div>

                            <div class="flex items-start">
                                <span class="text-green-500 mr-2">✓</span>
                                <div>
                                    <strong>LoginRequest:</strong> Handles <code>remember</code> field with
                                    <code>$this->boolean('remember')</code>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <span class="text-green-500 mr-2">✓</span>
                                <div>
                                    <strong>Authentication:</strong> Uses <code>Auth::attempt($credentials,
                                        $remember)</code>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <span class="text-green-500 mr-2">✓</span>
                                <div>
                                    <strong>Session Config:</strong> Configured for persistent sessions
                                </div>
                            </div>

                            <div class="flex items-start">
                                <span class="text-green-500 mr-2">✓</span>
                                <div>
                                    <strong>Form Validation:</strong> Remember field included in validation rules
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- How It Works -->
                    <div class="mt-6 p-6 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                            How Remember Me Works
                        </h3>

                        <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                            <div class="flex items-start">
                                <span class="text-primary-500 mr-2">1.</span>
                                <div>User checks "Remember me" checkbox and submits login form</div>
                            </div>

                            <div class="flex items-start">
                                <span class="text-primary-500 mr-2">2.</span>
                                <div>LoginRequest converts checkbox value to boolean using
                                    <code>$this->boolean('remember')</code></div>
                            </div>

                            <div class="flex items-start">
                                <span class="text-primary-500 mr-2">3.</span>
                                <div>Laravel's <code>Auth::attempt()</code> receives the remember parameter</div>
                            </div>

                            <div class="flex items-start">
                                <span class="text-primary-500 mr-2">4.</span>
                                <div>If remember is true, Laravel generates a remember token and stores it in the database
                                </div>
                            </div>

                            <div class="flex items-start">
                                <span class="text-primary-500 mr-2">5.</span>
                                <div>A persistent cookie is set in the user's browser with the remember token</div>
                            </div>

                            <div class="flex items-start">
                                <span class="text-primary-500 mr-2">6.</span>
                                <div>On future visits, Laravel automatically logs in the user using the remember token</div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <a href="{{ route('login') }}"
                            class="text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 font-medium">
                            ← Back to Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateRememberStatus() {
            const checkbox = document.getElementById('demo-remember');
            const statusText = document.getElementById('remember-status');
            const formValue = document.getElementById('form-value');

            const isChecked = checkbox.checked;

            statusText.innerHTML =
                `Checkbox is currently: <span class="font-semibold">${isChecked ? 'checked' : 'unchecked'}</span>`;
            formValue.textContent = isChecked ? 'true' : 'false';
        }

        function simulateLogin() {
            const checkbox = document.getElementById('demo-remember');
            const isChecked = checkbox.checked;

            alert(
                `Demo Login Simulation:\n\nEmail: demo@example.com\nPassword: [hidden]\nRemember Me: ${isChecked ? 'YES' : 'NO'}\n\nIn a real login, this would ${isChecked ? 'create a persistent session that lasts for years' : 'create a session that expires when the browser closes'}.`);
        }
    </script>
@endsection
