@extends('layouts.app')

@section('title', 'Frontend Infrastructure Demo')

@section('content')
    <div class="space-y-8">
        <!-- Page Header -->
        <div>
            <h1 class="text-3xl font-bold text-neutral-900 dark:text-neutral-100 mb-2">
                Frontend Infrastructure Demo
            </h1>
            <p class="text-neutral-600 dark:text-neutral-400">
                Testing Tailwind CSS v4, Alpine.js, responsive breakpoints, and design system components.
            </p>
        </div>

        <!-- Responsive Breakpoint Indicator -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-xl font-semibold">Responsive Breakpoint System</h2>
            </div>
            <div class="card-body">
                <div x-data="{ breakpoint: document.body.getAttribute('data-breakpoint') }" x-init="window.eventBus.on('breakpoint:change', (data) => { breakpoint = data.to })">
                    <p class="mb-2">Current breakpoint: <strong x-text="breakpoint"></strong></p>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                        Resize your browser window to see the breakpoint change.
                    </p>
                    <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="p-4 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
                            <div class="text-sm font-medium text-primary-700 dark:text-primary-300">Mobile</div>
                            <div class="text-xs text-primary-600 dark:text-primary-400">320px+</div>
                        </div>
                        <div class="p-4 bg-secondary-50 dark:bg-secondary-900/20 rounded-lg">
                            <div class="text-sm font-medium text-secondary-700 dark:text-secondary-300">Tablet</div>
                            <div class="text-xs text-secondary-600 dark:text-secondary-400">768px+</div>
                        </div>
                        <div class="p-4 bg-success-50 dark:bg-success-900/20 rounded-lg">
                            <div class="text-sm font-medium text-success-700 dark:text-success-300">Desktop</div>
                            <div class="text-xs text-success-600 dark:text-success-400">1024px+</div>
                        </div>
                        <div class="p-4 bg-warning-50 dark:bg-warning-900/20 rounded-lg">
                            <div class="text-sm font-medium text-warning-700 dark:text-warning-300">Wide</div>
                            <div class="text-xs text-warning-600 dark:text-warning-400">1280px+</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Button Components -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-xl font-semibold">Button Components</h2>
            </div>
            <div class="card-body space-y-6">
                <div>
                    <h3 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">Primary Buttons</h3>
                    <div class="flex flex-wrap gap-2">
                        <x-button variant="primary" size="sm">Small</x-button>
                        <x-button variant="primary" size="md">Medium</x-button>
                        <x-button variant="primary" size="lg">Large</x-button>
                        <x-button variant="primary" size="md" disabled>Disabled</x-button>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">Secondary Buttons</h3>
                    <div class="flex flex-wrap gap-2">
                        <x-button variant="secondary" size="sm">Small</x-button>
                        <x-button variant="secondary" size="md">Medium</x-button>
                        <x-button variant="secondary" size="lg">Large</x-button>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">Outline Buttons</h3>
                    <div class="flex flex-wrap gap-2">
                        <x-button variant="outline" size="sm">Small</x-button>
                        <x-button variant="outline" size="md">Medium</x-button>
                        <x-button variant="outline" size="lg">Large</x-button>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">Loading States</h3>
                    <div class="flex flex-wrap gap-2">
                        <x-button variant="primary" size="md" loading>Loading...</x-button>
                        <x-button variant="secondary" size="md" loading>Processing</x-button>
                        <x-button variant="outline" size="md" loading>Saving</x-button>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">Button Links</h3>
                    <div class="flex flex-wrap gap-2">
                        <x-button variant="primary" size="md" href="#" aria-label="Navigate to dashboard">Go to
                            Dashboard</x-button>
                        <x-button variant="secondary" size="md" href="#" aria-label="View profile">View
                            Profile</x-button>
                        <x-button variant="outline" size="md" href="#" aria-label="Learn more">Learn
                            More</x-button>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">With ARIA Labels</h3>
                    <div class="flex flex-wrap gap-2">
                        <x-button variant="primary" size="md" aria-label="Save character changes">Save</x-button>
                        <x-button variant="secondary" size="md" aria-label="Cancel and return">Cancel</x-button>
                        <x-button variant="outline" size="md"
                            aria-label="Delete character permanently">Delete</x-button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Badge Components -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-xl font-semibold">Badge Components</h2>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <h3 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Status Badges</h3>
                    <div class="flex flex-wrap gap-2">
                        <span class="badge badge-primary">Primary</span>
                        <span class="badge badge-secondary">Secondary</span>
                        <span class="badge badge-success">Success</span>
                        <span class="badge badge-warning">Warning</span>
                        <span class="badge badge-error">Error</span>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Grade Badges</h3>
                    <div class="flex flex-wrap gap-2">
                        <span class="grade-badge grade-ss">SS</span>
                        <span class="grade-badge grade-s">S</span>
                        <span class="grade-badge grade-a">A</span>
                        <span class="grade-badge grade-b">B</span>
                        <span class="grade-badge grade-c">C</span>
                        <span class="grade-badge grade-d">D</span>
                        <span class="grade-badge grade-e">E</span>
                        <span class="grade-badge grade-f">F</span>
                        <span class="grade-badge grade-g">G</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Components -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-xl font-semibold">Form Components</h2>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label class="form-label" for="demo-input">Text Input</label>
                    <input type="text" id="demo-input" class="form-input" placeholder="Enter text...">
                    <p class="form-help">This is help text for the input field.</p>
                </div>
                <div>
                    <label class="form-label" for="demo-select">Select Input</label>
                    <select id="demo-select" class="form-select">
                        <option>Option 1</option>
                        <option>Option 2</option>
                        <option>Option 3</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Checkbox</label>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="demo-checkbox" class="form-checkbox">
                        <label for="demo-checkbox" class="text-sm text-neutral-700 dark:text-neutral-300">
                            I agree to the terms and conditions
                        </label>
                    </div>
                </div>
                <div>
                    <label class="form-label" for="demo-error">Input with Error</label>
                    <input type="text" id="demo-error" class="form-input" aria-invalid="true" value="Invalid input">
                    <p class="form-error">This field has an error.</p>
                </div>
            </div>
        </div>

        <!-- Alert Components -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-xl font-semibold">Alert Components</h2>
            </div>
            <div class="card-body space-y-4">
                <div class="alert alert-success">
                    <strong>Success!</strong> Your changes have been saved.
                </div>
                <div class="alert alert-warning">
                    <strong>Warning!</strong> Please review your input.
                </div>
                <div class="alert alert-error">
                    <strong>Error!</strong> Something went wrong.
                </div>
                <div class="alert alert-info">
                    <strong>Info:</strong> This is an informational message.
                </div>
            </div>
        </div>

        <!-- Stat Bars -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-xl font-semibold">Stat Bar Components</h2>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Speed</span>
                        <span class="text-sm text-neutral-600 dark:text-neutral-400">450 / 600</span>
                    </div>
                    <div class="stat-bar">
                        <div class="stat-bar-fill stat-bar-speed" style="width: 75%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Stamina</span>
                        <span class="text-sm text-neutral-600 dark:text-neutral-400">380 / 600</span>
                    </div>
                    <div class="stat-bar">
                        <div class="stat-bar-fill stat-bar-stamina" style="width: 63%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Power</span>
                        <span class="text-sm text-neutral-600 dark:text-neutral-400">520 / 600</span>
                    </div>
                    <div class="stat-bar">
                        <div class="stat-bar-fill stat-bar-power" style="width: 87%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Guts</span>
                        <span class="text-sm text-neutral-600 dark:text-neutral-400">290 / 600</span>
                    </div>
                    <div class="stat-bar">
                        <div class="stat-bar-fill stat-bar-guts" style="width: 48%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Wisdom</span>
                        <span class="text-sm text-neutral-600 dark:text-neutral-400">410 / 600</span>
                    </div>
                    <div class="stat-bar">
                        <div class="stat-bar-fill stat-bar-wisdom" style="width: 68%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Event Bus Test -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-xl font-semibold">Event Bus Test</h2>
            </div>
            <div class="card-body" x-data="{
                message: 'No events yet',
                count: 0
            }" x-init="window.eventBus.on('test:event', (data) => {
                message = data.message;
                count++;
            });">
                <p class="mb-4">Message: <strong x-text="message"></strong></p>
                <p class="mb-4">Event count: <strong x-text="count"></strong></p>
                <button
                    @click="window.eventBus.emit('test:event', { message: 'Event triggered at ' + new Date().toLocaleTimeString() })"
                    class="btn btn-primary btn-md">
                    Trigger Event
                </button>
            </div>
        </div>

        <!-- Alpine.js Test -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-xl font-semibold">Alpine.js Test</h2>
            </div>
            <div class="card-body" x-data="{ count: 0, open: false }">
                <div class="space-y-4">
                    <div>
                        <p class="mb-2">Counter: <strong x-text="count"></strong></p>
                        <div class="flex gap-2">
                            <button @click="count++" class="btn btn-primary btn-sm">Increment</button>
                            <button @click="count--" class="btn btn-secondary btn-sm">Decrement</button>
                            <button @click="count = 0" class="btn btn-outline btn-sm">Reset</button>
                        </div>
                    </div>
                    <div>
                        <button @click="open = !open" class="btn btn-primary btn-md">
                            Toggle Content
                        </button>
                        <div x-show="open" x-transition class="mt-4 p-4 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
                            <p class="text-primary-700 dark:text-primary-300">
                                This content is toggled with Alpine.js transitions!
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
