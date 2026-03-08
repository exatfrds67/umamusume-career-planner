@props(['href' => '#', 'active' => false])

<a href="{{ $href }}"
   {{ $attributes->class([
       'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors',
       'border-transparent text-neutral-500 hover:border-neutral-300 hover:text-neutral-700' => !$active,
       'border-indigo-500 text-indigo-600' => $active,
   ]) }}>
    {{ $slot }}
</a>
