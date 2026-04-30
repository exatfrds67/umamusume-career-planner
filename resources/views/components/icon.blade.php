<div {{ $attributes->merge(['class' => 'inline-flex']) }}>
  @switch($name ?? '')
    @case('user-character')
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <!-- Head -->
        <circle cx="12" cy="7" r="4"/>
        <!-- Body -->
        <path d="M12 11v6M9 15l-3 3M15 15l3 3M9 11l-2 3M15 11l2 3"/>
      </svg>
      @break

    @case('loading')
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin">
        <circle cx="12" cy="12" r="10"/>
        <path d="M12 2a10 10 0 0 1 10 10"/>
      </svg>
      @break

    @case('error')
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
        <circle cx="12" cy="12" r="10"/>
        <path d="M12 7v5M12 17h.01" fill="white" stroke="white" stroke-width="2"/>
      </svg>
      @break

    @default
      <!-- Fallback: generic icon -->
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="1"/>
        <circle cx="19" cy="12" r="1"/>
        <circle cx="5" cy="12" r="1"/>
      </svg>
  @endswitch
</div>
