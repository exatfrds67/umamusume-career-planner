<div class="space-y-2">
    <!-- Header -->
    <div class="flex items-center justify-between">
        @if($showLabel)
            <span class="font-semibold {{ $colorClasses() }} {{ match($size) { 'sm' => 'text-xs', 'lg' => 'text-base', default => 'text-sm' } }}">
                {{ $goal }} Win Goal
            </span>
        @endif
        <span class="{{ $colorClasses() }} {{ match($size) { 'sm' => 'text-xs', 'lg' => 'text-base', default => 'text-sm' } }}">
            {{ $current }} / {{ $target }}
        </span>
    </div>

    <!-- Progress bar -->
    <div class="w-full {{ $bgColor() }} rounded-full overflow-hidden border {{ match($goal) { 'G1' => 'border-yellow-300 dark:border-yellow-700', 'G2' => 'border-blue-300 dark:border-blue-700', 'G3' => 'border-purple-300 dark:border-purple-700', 'OP' => 'border-green-300 dark:border-green-700', default => 'border-gray-300 dark:border-gray-700' } }}">
        <div class="h-2 transition-all duration-300 rounded-full {{ match($goal) { 'G1' => 'bg-yellow-400 dark:bg-yellow-500', 'G2' => 'bg-blue-400 dark:bg-blue-500', 'G3' => 'bg-purple-400 dark:bg-purple-500', 'OP' => 'bg-green-400 dark:bg-green-500', default => 'bg-gray-400 dark:bg-gray-500' } }}"
            role="progressbar"
            aria-valuenow="{{ $percentage() }}"
            aria-valuemin="0"
            aria-valuemax="100"
            style="width: {{ $percentage() }}%;"></div>
    </div>

    <!-- Completion badge -->
    @if($isComplete())
        <div class="text-xs font-semibold {{ $colorClasses() }} text-center">
            ✅ Goal Achieved!
        </div>
    @endif
</div>