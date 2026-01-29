<div class="flex flex-col items-center gap-4">
    <!-- SVG Radar Chart -->
    <div class="relative {{ $getSizeClasses() }}" role="img" aria-label="Stat radar chart">
        <svg viewBox="0 0 {{ $size === 'sm' ? 128 : ($size === 'lg' ? 384 : 256) }} {{ $size === 'sm' ? 128 : ($size === 'lg' ? 384 : 256) }}"
            class="w-full h-full"
            xmlns="http://www.w3.org/2000/svg">

            <!-- Background grid -->
            <defs>
                <style>
                    .grid-line {
                        stroke: currentColor;
                        stroke-width: 0.5;
                        opacity: 0.2;
                        fill: none;
                    }
                    
                    .radar-fill {
                        opacity: 0.3;
                        @if($animated)
                            animation: radarFill 1.2s ease-out forwards;
                        @endif
                    }
                    
                    @if($animated)
                        @keyframes radarFill {
                            from {
                                opacity: 0;
                            }
                            to {
                                opacity: 0.3;
                            }
                        }
                    @endif
                </style>
            </defs>

            <!-- Grid circles/pentagons -->
            @foreach($getGridPoints() as $level => $gridPointsString)
                <polygon points="{{ $gridPointsString }}"
                    class="grid-line dark:stroke-gray-600" />
            @endforeach

            <!-- Grid radial lines from center -->
            @php
                $size = $size === 'sm' ? 128 : ($size === 'lg' ? 384 : 256);
                $centerX = $size / 2;
                $centerY = $size / 2;
                $maxRadius = $size / 2.2;
            @endphp
            @foreach($getStatNames() as $index => $stat)
                @php
                    $angle = (($index * 360) / 5) - 90;
                    $radians = deg2rad($angle);
                    $x = $centerX + ($maxRadius * cos($radians));
                    $y = $centerY + ($maxRadius * sin($radians));
                @endphp
                <line x1="{{ $centerX }}" y1="{{ $centerY }}" x2="{{ $x }}" y2="{{ $y }}"
                    class="grid-line dark:stroke-gray-600" />
            @endforeach

            <!-- Data polygon -->
            <polygon points="{{ implode(' ', $calculatePoints()) }}"
                class="radar-fill {{ match($stats['speed'] ?? 0) { default => 'fill-rose-400/30' } }} dark:fill-rose-500/20"
                style="fill: url(#radarGradient);" />

            <!-- Gradient for radar fill -->
            <defs>
                <linearGradient id="radarGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color: #fb7185; stop-opacity: 0.3" />
                    <stop offset="100%" style="stop-color: #0ea5e9; stop-opacity: 0.3" />
                </linearGradient>
            </defs>

            <!-- Data points -->
            @foreach($getStatNames() as $index => $stat)
                @php
                    $percentage = $getStatPercentages()[$stat];
                    $angle = (($index * 360) / 5) - 90;
                    $radians = deg2rad($angle);
                    $radius = ($percentage / 100) * $maxRadius;
                    $x = $centerX + ($radius * cos($radians));
                    $y = $centerY + ($radius * sin($radians));
                @endphp
                <circle cx="{{ $x }}" cy="{{ $y }}" r="3"
                    class="{{ $getStatColor($stat) }}"
                    style="fill: {{ $getSvgFillColor($stat) }};"
                    role="presentation">
                    <title>{{ ucfirst($stat) }}: {{ $getStatValue($stat) }}</title>
                </circle>
            @endforeach
        </svg>
    </div>

    <!-- Legend -->
    @if($showLabels)
        <div class="flex flex-wrap justify-center gap-4">
            @foreach($getStatNames() as $stat)
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full {{ $getStatColor($stat) }}"
                        style="background-color: {{ $getSvgFillColor($stat) }};"></span>
                    <span class="text-sm font-medium {{ $getStatColor($stat) }}">
                        {{ ucfirst($stat) }}
                        @if($showValues)
                            <span class="text-xs opacity-75">({{ $getStatValue($stat) }})</span>
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    @endif
</div>