<div class="relative" x-data="{ open: @entangle('isOpen') }" @click.away="open = false; $wire.closeDropdown()"
    @keydown.escape.window="open = false; $wire.closeDropdown()"
    @popover-opened.window="if ($event.detail !== 'notifications' && open) { open = false; $wire.closeDropdown(); }">
    {{-- Notification Bell Button --}}
    <button type="button" id="notification-btn" wire:click="toggleDropdown"
        x-on:click="$dispatch('popover-opened', 'notifications')"
        class="-m-2.5 p-2.5 text-neutral-400 hover:text-neutral-500 dark:text-neutral-300 dark:hover:text-neutral-100 transition-colors duration-200 relative"
        aria-label="View notifications" :aria-expanded="open.toString()">
        <span class="sr-only">View notifications</span>
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>

        {{-- Unread Badge --}}
        @if ($this->unreadCount > 0)
            <span
                class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white ring-2 ring-white dark:ring-neutral-800">
                {{ $this->unreadCount > 9 ? '9+' : $this->unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown Panel --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="absolute right-0 z-50 mt-2 w-80 origin-top-right rounded-lg bg-white dark:bg-neutral-800 shadow-lg ring-1 ring-neutral-900/5 dark:ring-neutral-700 focus:outline-hidden"
        role="menu" aria-orientation="vertical" aria-label="Notifications">

        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-neutral-100 dark:border-neutral-700">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Notifications</h3>
            @if ($this->unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-xs text-primary-600 dark:text-primary-400 hover:underline">
                    Mark all read
                </button>
            @endif
        </div>

        {{-- Notification List --}}
        <div class="max-h-72 overflow-y-auto">
            @forelse ($this->notifications as $notification)
                <div wire:key="notif-{{ $notification->id }}"
                    class="px-4 py-3 border-b border-neutral-50 dark:border-neutral-700/50 hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors {{ $notification->read_at ? '' : 'bg-primary-50/50 dark:bg-primary-900/10' }}">
                    <div class="flex items-start gap-3">
                        {{-- Icon --}}
                        <div class="shrink-0 mt-0.5">
                            @php
                                $type = $notification->data['type'] ?? 'info';
                                $iconClass = match ($type) {
                                    'training' => 'text-blue-500',
                                    'race' => 'text-green-500',
                                    'goal' => 'text-yellow-500',
                                    'warning' => 'text-orange-500',
                                    'error' => 'text-red-500',
                                    default => 'text-neutral-400',
                                };
                            @endphp
                            <svg class="w-4 h-4 {{ $iconClass }}" fill="currentColor" viewBox="0 0 20 20">
                                <circle cx="10" cy="10" r="4" />
                            </svg>
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <p
                                class="text-sm text-neutral-900 dark:text-white {{ $notification->read_at ? 'font-normal' : 'font-medium' }}">
                                {{ $notification->data['message'] ?? 'Notification' }}
                            </p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>

                        {{-- Mark as read --}}
                        @unless ($notification->read_at)
                            <button wire:click="markAsRead('{{ $notification->id }}')"
                                class="shrink-0 p-1 text-neutral-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                                aria-label="Mark as read">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        @endunless
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center">
                    <svg class="mx-auto h-8 w-8 text-neutral-300 dark:text-neutral-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">No notifications yet</p>
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        @if ($this->notifications->isNotEmpty())
            <div class="px-4 py-2 border-t border-neutral-100 dark:border-neutral-700 text-center">
                <a href="{{ route('settings.index') }}#notifications"
                    class="text-xs text-primary-600 dark:text-primary-400 hover:underline">
                    Notification settings
                </a>
            </div>
        @endif
    </div>
</div>