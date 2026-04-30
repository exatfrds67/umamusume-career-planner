<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('MigrationWizard Livewire Component', function () {
    it('renders the migration wizard in idle state', function () {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\DataManagement\MigrationWizard::class)
            ->assertSuccessful()
            ->assertSet('step', 'idle')
            ->assertSee('Convert Local Data to Account');
    });

    it('shows the start migration button', function () {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\DataManagement\MigrationWizard::class)
            ->assertSee('Start Migration');
    });

    it('shows data cards for export, import, backup, legacy migration', function () {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\DataManagement\MigrationWizard::class)
            ->assertSee('Export Data')
            ->assertSee('Import Data')
            ->assertSee('Cloud Backup')
            ->assertSee('Legacy Migration');
    });

    it('transitions to validate step on start migration', function () {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\DataManagement\MigrationWizard::class)
            ->call('startMigration')
            ->assertSet('step', 'validate')
            ->assertSee('Validate');
    });

    it('shows validation result with character and career counts', function () {
        Character::factory()->count(3)->create(['user_id' => $this->user->id]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\DataManagement\MigrationWizard::class)
            ->call('startMigration')
            ->assertSee('Characters')
            ->assertSee('Career Runs')
            ->assertSee('Validation passed');
    });

    it('shows keep local copy toggle in validate step', function () {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\DataManagement\MigrationWizard::class)
            ->call('startMigration')
            ->assertSee('Keep local copy')
            ->assertSet('keepLocalCopy', true);
    });

    it('can toggle keep local copy', function () {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\DataManagement\MigrationWizard::class)
            ->call('startMigration')
            ->set('keepLocalCopy', false)
            ->assertSet('keepLocalCopy', false);
    });

    it('transitions to preview step', function () {
        Character::factory()->create(['user_id' => $this->user->id, 'name' => 'Test Uma']);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\DataManagement\MigrationWizard::class)
            ->call('startMigration')
            ->call('goToPreview')
            ->assertSet('step', 'preview')
            ->assertSee('Preview');
    });

    it('shows preview items with ready status', function () {
        Character::factory()->create(['user_id' => $this->user->id, 'name' => 'Preview Uma']);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\DataManagement\MigrationWizard::class)
            ->call('startMigration')
            ->call('goToPreview')
            ->assertSee('Preview Uma')
            ->assertSee('Ready');
    });

    it('runs migration and transitions to done', function () {
        Character::factory()->create(['user_id' => $this->user->id, 'name' => 'Migrated Uma']);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\DataManagement\MigrationWizard::class)
            ->call('startMigration')
            ->call('goToPreview')
            ->call('runMigration')
            ->assertSet('step', 'done')
            ->assertSee('Migration Complete');
    });

    it('shows migrated items in done step', function () {
        Character::factory()->create(['user_id' => $this->user->id, 'name' => 'Done Uma']);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\DataManagement\MigrationWizard::class)
            ->call('startMigration')
            ->call('goToPreview')
            ->call('runMigration')
            ->assertSee('Done Uma');
    });

    it('can reset back to idle from done', function () {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\DataManagement\MigrationWizard::class)
            ->call('startMigration')
            ->call('goToPreview')
            ->call('runMigration')
            ->call('cancelWizard')
            ->assertSet('step', 'idle')
            ->assertSet('migratedCount', 0);
    });

    it('can cancel from validate step', function () {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\DataManagement\MigrationWizard::class)
            ->call('startMigration')
            ->call('cancelWizard')
            ->assertSet('step', 'idle');
    });

    it('shows step indicators in modal header', function () {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\DataManagement\MigrationWizard::class)
            ->call('startMigration')
            ->assertSee('Validate')
            ->assertSee('Preview')
            ->assertSee('Migrate')
            ->assertSee('Done');
    });
});

describe('/data route', function () {
    it('renders the data management page', function () {
        $response = $this->actingAs($this->user)->get(route('data.index'));

        $response->assertSuccessful();
        $response->assertSee('Data Management');
    });

    it('redirects unauthenticated users', function () {
        $response = $this->get(route('data.index'));

        $response->assertRedirect();
    });
});
