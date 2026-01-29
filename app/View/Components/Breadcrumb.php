<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Breadcrumb extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  array<int, array{label: string, url?: string}>  $items  Array of breadcrumb items
     * @param  bool  $showHome  Whether to show the home link
     */
    public function __construct(
        public array $items = [],
        public bool $showHome = true,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.breadcrumb');
    }

    /**
     * Get all breadcrumb items including home if enabled.
     *
     * @return array<int, array{label: string, url?: string}>
     */
    public function allItems(): array
    {
        if (! $this->showHome) {
            return $this->items;
        }

        return array_merge(
            [['label' => 'Home', 'url' => route('dashboard')]],
            $this->items
        );
    }

    /**
     * Generate JSON-LD structured data for breadcrumbs.
     */
    public function jsonLd(): string
    {
        $items = $this->allItems();
        $itemListElements = [];

        foreach ($items as $index => $item) {
            $itemListElements[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['label'],
                'item' => $item['url'] ?? null,
            ];
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemListElements,
        ];

        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}
