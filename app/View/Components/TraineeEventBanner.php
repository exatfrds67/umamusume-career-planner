<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TraineeEventBanner extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  string  $type  Event type (event, warning, achievement, training)
     * @param  string  $title  Event title
     * @param  string  $message  Event message
     * @param  string  $icon  Icon emoji or name
     * @param  bool  $dismissible  Whether banner can be closed
     */
    public function __construct(
        public string $type = 'event',
        public string $title = 'Event',
        public string $message = '',
        public string $icon = '📢',
        public bool $dismissible = true,
    ) {
        $this->type = strtolower($type);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.trainee-event-banner');
    }

    /**
     * Get banner background color.
     */
    public function bgColor(): string
    {
        return match ($this->type) {
            'warning' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800',
            'achievement' => 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800',
            'training' => 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800',
            default => 'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800',
        };
    }

    /**
     * Get text color classes.
     */
    public function textColor(): string
    {
        return match ($this->type) {
            'warning' => 'text-red-900 dark:text-red-200',
            'achievement' => 'text-green-900 dark:text-green-200',
            'training' => 'text-blue-900 dark:text-blue-200',
            default => 'text-orange-900 dark:text-orange-200',
        };
    }

    /**
     * Get accent color for title.
     */
    public function accentColor(): string
    {
        return match ($this->type) {
            'warning' => 'text-red-600 dark:text-red-400',
            'achievement' => 'text-green-600 dark:text-green-400',
            'training' => 'text-blue-600 dark:text-blue-400',
            default => 'text-orange-600 dark:text-orange-400',
        };
    }

    /**
     * Get icon for the event type.
     */
    public function getIcon(): string
    {
        if ($this->icon) {
            return $this->icon;
        }

        return match ($this->type) {
            'warning' => '⚠️',
            'achievement' => '🏆',
            'training' => '💪',
            default => '📢',
        };
    }
}
