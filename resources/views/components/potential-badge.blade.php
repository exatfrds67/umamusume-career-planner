<span class="inline-flex items-center rounded-full font-semibold {{ $badgeColor() }} {{ $sizeClasses() }}" {{ $attributes }}>
    @if($showLabel)
        <span class="opacity-90 mr-0.5">Lv</span>
    @endif
    <span>{{ $level }}</span>
</span>