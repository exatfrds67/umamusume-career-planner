@extends('layouts.app')

@section('title', 'AI Browser Components Test')

@section('content')
    <script>
        window.__quickMessage = null;
        window.addEventListener('send-quick-message', (event) => {
            window.__quickMessage = event.detail.message;
        });
    </script>

    @php
        $recommendation = (object) [
            'id' => 'browser-rec-1',
            'priority' => 'high',
            'action' => 'Build stamina before the next long race',
            'reasoning' => 'Upcoming race distance is punishing with your current stamina.',
            'expected_outcomes' => [
                'stamina_gain' => '+60',
                'bond_increase' => '+7 each',
            ],
            'risks' => ['Lower short-term speed gains'],
            'confidence_score' => 0.88,
        ];
    @endphp

    <main class="mx-auto flex max-w-5xl flex-col gap-8">
        <section id="loading-skeleton-demo" class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-900">
            <h1 class="mb-4 text-xl font-semibold">Loading Skeleton</h1>
            <x-loading-skeleton variant="card" :lines="4" :show-avatar="true" class="w-full" />
        </section>

        <section id="error-state-demo" class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-900">
            <h2 class="mb-4 text-xl font-semibold">Error State</h2>
            <x-error-state
                title="Model unavailable"
                message="The selected model is offline. Try another model or wait a moment."
                icon="offline"
                :retryable="true"
            >
                <button type="button" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white">
                    Retry now
                </button>
            </x-error-state>
        </section>

        <section id="recommendation-card-demo" class="rounded-xl border border-neutral-200 bg-white p-6 dark:border-neutral-700 dark:bg-neutral-900">
            <h2 class="mb-4 text-xl font-semibold">Recommendation Card</h2>
            <x-ai.recommendation-card :recommendation="$recommendation" :expanded="true" />
        </section>
    </main>
@endsection