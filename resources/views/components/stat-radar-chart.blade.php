<div class="flex flex-col items-center gap-4">
    <!-- SVG Radar Chart -->
    <div class="relative {{ $getSizeClasses() }}" role="img" aria-label="Stat radar chart">
        <svg viewBox="0 0 {{ $size === 'sm' ? 64 : ($size === 'lg' ? 192 : 128) }} {{ $size === 'sm' ? 64 : ($size === 'lg' ? 192 : 128) }}"
            class="w-full h-full" xmlns="http://www.w3.org/2000/svg">

            <!-- Background grid -->
            <defs>

            </defs>

            <!-- Grid circles/pentagons -->
            @foreach ($getGridPoints() as $level => $gridPointsString)
                <polygon points="{{ $gridPointsString }}" class="grid-line dark:stroke-neutral-600" />
            @endforeach

            <!-- Grid value labels -->
            @php
                $svgSize = $size === 'sm' ? 64 : ($size === 'lg' ? 192 : 128);
                $centerX = $svgSize / 2;
                $centerY = $svgSize / 2;
                $maxRadius = $svgSize / 2.2;
                $labelOffset = $size === 'sm' ? 2 : ($size === 'lg' ? 6 : 4);
            @endphp
            @foreach ([1, 2, 3, 4, 5] as $level)
                @php
                    $radius = ($level / 5) * $maxRadius;
                    $value = round(($level / 5) * $max);
                    // Position label at top (12 o'clock position)
                    $labelY = $centerY - $radius - $labelOffset;
                @endphp
                <text x="{{ $centerX }}" y="{{ $labelY }}"
                    style="font-size: {{ $size === 'sm' ? '3px' : ($size === 'lg' ? '9px' : '6px') }};"
                    class="grid-label dark:fill-neutral-400">{{ $value }}</text>
            @endforeach

            <!-- Grid radial lines from center -->
            @php
                $svgSize = $size === 'sm' ? 64 : ($size === 'lg' ? 192 : 128);
                $centerX = $svgSize / 2;
                $centerY = $svgSize / 2;
                $maxRadius = $svgSize / 2.2;
            @endphp
            @foreach ($getStatNames() as $index => $stat)
                @php
                    $angle = ($index * 360) / 5 - 90;
                    $radians = deg2rad($angle);
                    $x = $centerX + $maxRadius * cos($radians);
                    $y = $centerY + $maxRadius * sin($radians);
                @endphp
                <line x1="{{ $centerX }}" y1="{{ $centerY }}" x2="{{ $x }}"
                    y2="{{ $y }}" class="grid-line dark:stroke-neutral-600" />
            @endforeach

            <!-- Data polygon -->
            <polygon points="{{ implode(' ', $calculatePoints()) }}"
                class="radar-fill {{ $animated ? 'radar-fill-animated' : '' }} {{ match ($stats['speed'] ?? 0) {default => 'fill-blue-400/30'} }} dark:fill-blue-500/20"
                style="fill: url(#radarGradient);" />

            <!-- Gradient for radar fill -->
            <defs>
                <linearGradient id="radarGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color: #fb7185; stop-opacity: 0.3" />
                    <stop offset="100%" style="stop-color: #0ea5e9; stop-opacity: 0.3" />
                </linearGradient>
            </defs>

            <!-- Data points -->
            @foreach ($getStatNames() as $index => $stat)
                @php
                    $percentage = $getStatPercentages()[$stat];
                    $angle = ($index * 360) / 5 - 90;
                    $radians = deg2rad($angle);
                    $radius = ($percentage / 100) * $maxRadius;
                    $x = $centerX + $radius * cos($radians);
                    $y = $centerY + $radius * sin($radians);
                @endphp
                <circle cx="{{ $x }}" cy="{{ $y }}" r="3" class="{{ $getStatColor($stat) }}"
                    style="fill: {{ $getSvgFillColor($stat) }};" role="presentation">
                    <title>{{ ucfirst($stat) }}: {{ $getStatValue($stat) }}</title>
                </circle>
            @endforeach
        </svg>
    </div>

    <!-- Legend -->
    @if ($showLabels)
        <div class="flex items-center justify-center gap-x-2 gap-y-1 flex-wrap max-w-full">
            @foreach ($getStatNames() as $stat)
                <div class="flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full shrink-0"
                        style="background-color: {{ $getSvgFillColor($stat) }};"></span>
                    <span class="text-xs font-medium whitespace-nowrap {{ $getStatColor($stat) }}">
                        {{ ucfirst($stat) }}
                        @if ($showValues)
                            <span class="text-[10px] opacity-75">({{ $getStatValue($stat) }})</span>
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    @endif
</div>
