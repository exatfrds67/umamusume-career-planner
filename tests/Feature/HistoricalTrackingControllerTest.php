<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

describe('Historical Tracking Page', function (): void {
    it('shows insufficient-data progress state for users without completed careers', function (): void {
        $response = $this->actingAs($this->user)->get(route('historical.index'));

        $response->assertSuccessful();
        $response->assertSee('Historical Tracking & Benchmarking');
        $response->assertSee('Insufficient Data');
        $response->assertSee('Progress: 0 / 5 completed careers');
        $response->assertSee('Refresh Data');
    });
});
