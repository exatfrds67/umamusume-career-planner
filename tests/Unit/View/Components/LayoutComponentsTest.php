<?php

use App\View\Components\CharacterProfile;
use App\View\Components\MemoriesGrid;

describe('CharacterProfile Component', function () {
    it('returns correct layout classes for row layout', function () {
        $component = new CharacterProfile(layout: 'row', imageLeft: true);
        expect($component->layoutClasses())->toContain('flex-row');

        $component = new CharacterProfile(layout: 'row', imageLeft: false);
        expect($component->layoutClasses())->toContain('flex-row-reverse');
    });

    it('returns correct layout classes for column layout', function () {
        $component = new CharacterProfile(layout: 'column');
        expect($component->layoutClasses())->toContain('flex-col')
            ->and($component->layoutClasses())->not->toContain('flex-row-reverse');
    });

    it('returns correct image section classes', function () {
        expect((new CharacterProfile(layout: 'row'))->imageSectionClasses())
            ->toContain('md:w-2/5')
            ->and((new CharacterProfile(layout: 'column'))->imageSectionClasses())
            ->toBe('w-full');
    });

    it('returns correct content section classes', function () {
        expect((new CharacterProfile(layout: 'row'))->contentSectionClasses())
            ->toContain('flex-1')
            ->and((new CharacterProfile(layout: 'column'))->contentSectionClasses())
            ->toBe('w-full');
    });

    it('returns correct image size classes', function () {
        expect((new CharacterProfile(imageSize: 'small'))->imageSizeClasses())->toBe('h-64')
            ->and((new CharacterProfile(imageSize: 'medium'))->imageSizeClasses())->toBe('h-80')
            ->and((new CharacterProfile(imageSize: 'large'))->imageSizeClasses())->toContain('min-h-96');
    });

    it('renders with image and text', function () {
        $view = $this->blade(
            '<x-character-profile image="/image.jpg" name="Special Week" title="Legendary Mare" />',
        );

        $view->assertSee('/image.jpg', false)
            ->assertSee('Special Week')
            ->assertSee('Legendary Mare');
    });

    it('renders with slot content', function () {
        $view = $this->blade(
            '<x-character-profile image="/image.jpg" name="Test">
                <div>Custom Content</div>
            </x-character-profile>',
        );

        $view->assertSee('Custom Content');
    });
});

describe('MemoriesGrid Component', function () {
    it('returns correct grid classes', function () {
        expect((new MemoriesGrid(columns: 2))->gridClasses())->toBe('grid-cols-2')
            ->and((new MemoriesGrid(columns: 3))->gridClasses())->toBe('grid-cols-3')
            ->and((new MemoriesGrid(columns: 4))->gridClasses())->toContain('grid-cols-2');
    });

    it('determines lock status correctly', function () {
        $component = new MemoriesGrid;

        expect($component->isLocked(['isLocked' => true]))->toBeTrue()
            ->and($component->isLocked(['isLocked' => false]))->toBeFalse()
            ->and($component->isLocked([]))->toBeFalse();
    });

    it('determines new flag correctly', function () {
        $component = new MemoriesGrid;

        expect($component->isNew(['isNew' => true]))->toBeTrue()
            ->and($component->isNew(['isNew' => false]))->toBeFalse()
            ->and($component->isNew([]))->toBeFalse();
    });

    it('retrieves item URL correctly', function () {
        $component = new MemoriesGrid;

        expect($component->itemUrl(['url' => '/memories/1']))->toBe('/memories/1')
            ->and($component->itemUrl([]))->toBe('');
    });

    it('renders grid with items', function () {
        $items = [
            ['id' => '1', 'title' => 'Memory 1', 'icon' => '📖'],
            ['id' => '2', 'title' => 'Memory 2', 'icon' => '⭐', 'isLocked' => true],
            ['id' => '3', 'title' => 'Memory 3', 'icon' => '💎', 'isNew' => true],
        ];

        $view = $this->blade(
            '<x-memories-grid :items="$items" title="Special Memories" />',
            ['items' => $items],
        );

        $view->assertSee('Special Memories')
            ->assertSee('Memory 1')
            ->assertSee('Memory 2')
            ->assertSee('Memory 3')
            ->assertSee('NEW');
    });

    it('renders empty state when no items', function () {
        $view = $this->blade(
            '<x-memories-grid :items="[]" />',
        );

        $view->assertSee('No items to display');
    });

    it('hides lock icons when showLockIcons is false', function () {
        $items = [
            ['id' => '1', 'title' => 'Locked', 'isLocked' => true],
        ];

        $view = $this->blade(
            '<x-memories-grid :items="$items" :show-lock-icons="false" />',
            ['items' => $items],
        );

        // Lock icon SVG should not be rendered in the badge
        $view->assertDontSee('fill-rule="evenodd"', false);
    });
});
