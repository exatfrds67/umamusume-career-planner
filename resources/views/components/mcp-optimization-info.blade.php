@props(['data'])

<div
    class="mb-4 p-3 glass-card-inner rounded-lg border-2 border-blue-200 dark:border-blue-800 transition-colors duration-300">
    <h4 class="text-sm font-semibold text-blue-700 dark:text-blue-300 mb-2 transition-colors duration-300">
        🤖 Agent Analysis
    </h4>
    <div class="space-y-2 text-xs">
        @if (isset($data['agent_workflow']))
            <div>
                <span
                    class="text-blue-700 dark:text-blue-300 font-medium transition-colors duration-300">Workflow:</span>
                <span class="text-blue-900 dark:text-blue-100 ml-1 transition-colors duration-300">
                    {{ $data['agent_workflow'] }}
                </span>
            </div>
        @endif

        @if (isset($data['confidence_score']))
            <div class="flex justify-between">
                <span class="text-blue-700 dark:text-blue-300 transition-colors duration-300">Confidence Score</span>
                <span class="font-medium text-blue-900 dark:text-blue-100 transition-colors duration-300">
                    {{ number_format($data['confidence_score'] * 100, 0) }}%
                </span>
            </div>
        @endif

        @if (isset($data['agents_consulted']) && is_array($data['agents_consulted']))
            <div>
                <span class="text-blue-700 dark:text-blue-300 font-medium transition-colors duration-300">Agents
                    Consulted:</span>
                <div class="mt-1 flex flex-wrap gap-1">
                    @foreach ($data['agents_consulted'] as $agent)
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs glass-card-inner transition-colors duration-300">
                            {{ $agent }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        @if (isset($data['processing_time_ms']))
            <div class="flex justify-between">
                <span class="text-blue-700 dark:text-blue-300 transition-colors duration-300">Processing Time</span>
                <span class="font-medium text-blue-900 dark:text-blue-100 transition-colors duration-300">
                    {{ $data['processing_time_ms'] }}ms
                </span>
            </div>
        @endif

        @if (isset($data['cached']) && $data['cached'])
            <div class="flex items-center gap-1 text-green-700 dark:text-green-300">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <span class="font-medium">Cached Result</span>
            </div>
        @endif
    </div>
</div>
