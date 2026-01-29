<?php

use App\View\Components\Breadcrumb;

it('returns all items with home prepended when showHome is true', function () {
    $component = new Breadcrumb(
        items: [
            ['label' => 'Characters', 'url' => '/characters'],
            ['label' => 'Special Week'],
        ],
        showHome: true
    );

    $allItems = $component->allItems();

    expect($allItems)->toHaveCount(3)
        ->and($allItems[0]['label'])->toBe('Home')
        ->and($allItems[1]['label'])->toBe('Characters')
        ->and($allItems[2]['label'])->toBe('Special Week');
});

it('returns only provided items when showHome is false', function () {
    $component = new Breadcrumb(
        items: [
            ['label' => 'Characters', 'url' => '/characters'],
            ['label' => 'Special Week'],
        ],
        showHome: false
    );

    $allItems = $component->allItems();

    expect($allItems)->toHaveCount(2)
        ->and($allItems[0]['label'])->toBe('Characters')
        ->and($allItems[1]['label'])->toBe('Special Week');
});

it('generates valid json-ld schema', function () {
    $component = new Breadcrumb(
        items: [
            ['label' => 'Characters', 'url' => '/characters'],
            ['label' => 'Special Week'],
        ],
        showHome: true
    );

    $jsonLd = $component->jsonLd();
    $schema = json_decode($jsonLd, true);

    expect($schema)->toHaveKey('@context')
        ->and($schema['@context'])->toBe('https://schema.org')
        ->and($schema)->toHaveKey('@type')
        ->and($schema['@type'])->toBe('BreadcrumbList')
        ->and($schema)->toHaveKey('itemListElement')
        ->and($schema['itemListElement'])->toHaveCount(3)
        ->and($schema['itemListElement'][0])->toMatchArray([
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Home',
        ])
        ->and($schema['itemListElement'][2]['position'])->toBe(3);
});

it('renders breadcrumb component view successfully', function () {
    $view = $this->blade('<x-breadcrumb :items="$items" />', [
        'items' => [
            ['label' => 'Races', 'url' => '/races'],
            ['label' => 'Japan Cup'],
        ],
    ]);

    $view->assertSee('Home')
        ->assertSee('Races')
        ->assertSee('Japan Cup')
        ->assertSee('application/ld+json', false);
});
