<?php

declare(strict_types=1);

use App\Enums\ConsentType;
use App\Enums\DeletionStatus;
use App\Models\Character;
use App\Models\ConsentRecord;
use App\Models\DeletionRequest;
use App\Models\User;
use App\Notifications\DeletionConfirmationNotification;
use App\Services\Privacy\ConsentManagementService;
use App\Services\Privacy\DataDeletionService;
use App\Services\Privacy\DataExportService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

/*
|--------------------------------------------------------------------------
| DataExportService Tests
|--------------------------------------------------------------------------
*/
describe('DataExportService', function (): void {
    it('generates a complete personal data export', function (): void {
        $character = Character::factory()->create(['user_id' => $this->user->id]);

        $service = new DataExportService;
        $data = $service->generateExport($this->user);

        expect($data)->toHaveKeys([
            'schema_version',
            'exported_at',
            'user',
            'characters',
            'careers',
            'consent_records',
            'deletion_requests',
            'preferences',
            'ai_conversations',
            'metadata',
        ]);

        expect($data['schema_version'])->toBe('1.0.0');
        expect($data['user']['id'])->toBe($this->user->id);
        expect($data['user']['email'])->toBe($this->user->email);
        expect($data['user']['name'])->toBe($this->user->name);
        expect($data['characters'])->toHaveCount(1);
        expect($data['metadata']['total_characters'])->toBe(1);
    });

    it('does not include password hash in export', function (): void {
        $service = new DataExportService;
        $data = $service->generateExport($this->user);
        $json = json_encode($data);

        expect($json)->not->toContain('password');
        expect($json)->not->toContain('$2y$');
    });

    it('generates export file on disk', function (): void {
        Storage::fake('local');

        $service = new DataExportService;
        $path = $service->generateExportFile($this->user);

        expect($path)->toContain('privacy-exports/');
        expect($path)->toContain('.json');
        Storage::assertExists($path);

        $content = json_decode(Storage::get($path), true);
        expect($content['schema_version'])->toBe('1.0.0');
    });

    it('includes consent records in export', function (): void {
        ConsentRecord::factory()->forType(ConsentType::Analytics)->create([
            'user_id' => $this->user->id,
        ]);

        $service = new DataExportService;
        $data = $service->generateExport($this->user);

        expect($data['consent_records'])->toHaveCount(1);
        expect($data['consent_records'][0]['consent_type'])->toBe('analytics');
    });
});

/*
|--------------------------------------------------------------------------
| DataDeletionService Tests
|--------------------------------------------------------------------------
*/
describe('DataDeletionService', function (): void {
    it('creates a deletion request with 30-day grace period', function (): void {
        Notification::fake();

        $service = new DataDeletionService;
        $request = $service->requestDeletion($this->user, 'No longer needed');

        expect($request->status)->toBe(DeletionStatus::Pending);
        expect($request->reason)->toBe('No longer needed');
        expect((int) abs($request->grace_period_ends_at->diffInDays(now())))->toBeGreaterThanOrEqual(29);
        expect($request->user_id)->toBe($this->user->id);

        Notification::assertSentTo($this->user, DeletionConfirmationNotification::class);
    });

    it('does not create duplicate pending requests', function (): void {
        Notification::fake();

        $service = new DataDeletionService;
        $first = $service->requestDeletion($this->user);
        $second = $service->requestDeletion($this->user);

        expect($first->id)->toBe($second->id);
        expect(DeletionRequest::where('user_id', $this->user->id)->count())->toBe(1);
    });

    it('cancels a pending deletion request', function (): void {
        $request = DeletionRequest::factory()->create(['user_id' => $this->user->id]);

        $service = new DataDeletionService;
        $cancelled = $service->cancelDeletion($request);

        expect($cancelled->status)->toBe(DeletionStatus::Cancelled);
        expect($cancelled->cancelled_at)->not->toBeNull();
    });

    it('cannot cancel a completed request', function (): void {
        $request = DeletionRequest::factory()->completed()->create(['user_id' => $this->user->id]);

        $service = new DataDeletionService;

        expect(fn () => $service->cancelDeletion($request))->toThrow(RuntimeException::class);
    });

    it('executes deletion and removes user data', function (): void {
        Character::factory()->count(2)->create(['user_id' => $this->user->id]);
        ConsentRecord::factory()->create(['user_id' => $this->user->id]);

        $request = DeletionRequest::factory()->expired()->create(['user_id' => $this->user->id]);

        $service = new DataDeletionService;
        $service->executeDeletion($request);

        expect($request->fresh()->status)->toBe(DeletionStatus::Completed);
        expect($request->fresh()->completed_at)->not->toBeNull();
        expect(Character::where('user_id', $this->user->id)->count())->toBe(0);
        expect(ConsentRecord::where('user_id', $this->user->id)->count())->toBe(0);
        expect(User::find($this->user->id))->toBeNull();
    });

    it('checks for pending deletion', function (): void {
        $service = new DataDeletionService;

        expect($service->hasPendingDeletion($this->user))->toBeFalse();

        DeletionRequest::factory()->create(['user_id' => $this->user->id]);

        expect($service->hasPendingDeletion($this->user))->toBeTrue();
    });

    it('processes expired deletion requests', function (): void {
        $activeUser = User::factory()->create();
        $expiredUser = User::factory()->create();

        DeletionRequest::factory()->create(['user_id' => $activeUser->id]);
        DeletionRequest::factory()->expired()->create(['user_id' => $expiredUser->id]);

        $service = new DataDeletionService;
        $result = $service->processExpiredRequests();

        expect($result['processed'])->toBe(1);
        expect($result['failed'])->toBe(0);
        expect(User::find($expiredUser->id))->toBeNull();
        expect(User::find($activeUser->id))->not->toBeNull();
    });
});

/*
|--------------------------------------------------------------------------
| ConsentManagementService Tests
|--------------------------------------------------------------------------
*/
describe('ConsentManagementService', function (): void {
    it('grants consent for a type', function (): void {
        $service = new ConsentManagementService;
        $record = $service->grantConsent($this->user, ConsentType::Analytics);

        expect($record->granted)->toBeTrue();
        expect($record->consent_type)->toBe(ConsentType::Analytics);
        expect($record->granted_at)->not->toBeNull();
    });

    it('revokes consent for a type', function (): void {
        $service = new ConsentManagementService;
        $service->grantConsent($this->user, ConsentType::Analytics);
        $revoked = $service->revokeConsent($this->user, ConsentType::Analytics);

        expect($revoked->granted)->toBeFalse();
        expect($revoked->revoked_at)->not->toBeNull();
    });

    it('checks consent status', function (): void {
        $service = new ConsentManagementService;

        expect($service->hasConsent($this->user, ConsentType::Analytics))->toBeFalse();

        $service->grantConsent($this->user, ConsentType::Analytics);
        expect($service->hasConsent($this->user, ConsentType::Analytics))->toBeTrue();

        $service->revokeConsent($this->user, ConsentType::Analytics);
        expect($service->hasConsent($this->user, ConsentType::Analytics))->toBeFalse();
    });

    it('returns consent status for all types', function (): void {
        $service = new ConsentManagementService;
        $service->grantConsent($this->user, ConsentType::Analytics);

        $status = $service->getConsentStatus($this->user);

        expect($status)->toBeArray();
        expect($status['analytics'])->toBeTrue();
        expect($status['cloud_backup'])->toBeFalse();
        expect($status['ai_processing'])->toBeFalse();
    });

    it('bulk updates consent settings', function (): void {
        $service = new ConsentManagementService;
        $results = $service->updateConsents($this->user, [
            'analytics' => true,
            'cloud_backup' => true,
            'ai_processing' => false,
        ]);

        expect($results)->toHaveCount(3);
        expect($service->hasConsent($this->user, ConsentType::Analytics))->toBeTrue();
        expect($service->hasConsent($this->user, ConsentType::CloudBackup))->toBeTrue();
        expect($service->hasConsent($this->user, ConsentType::AiProcessing))->toBeFalse();
    });

    it('ignores invalid consent types in bulk update', function (): void {
        $service = new ConsentManagementService;
        $results = $service->updateConsents($this->user, [
            'analytics' => true,
            'invalid_type' => true,
        ]);

        expect($results)->toHaveCount(1);
        expect($results)->toHaveKey('analytics');
    });
});

/*
|--------------------------------------------------------------------------
| Property 20: Data Deletion Completeness
|--------------------------------------------------------------------------
| Feature: umamusume-career-planner-main-v2.4.0
| Property 20: When account deletion completes, ALL user data must be
| permanently removed from the system.
| Validates: NFR-S-07
*/
describe('Property 20: Data Deletion Completeness', function (): void {
    it('deletion removes all traces of user data', function (): void {
        $user = User::factory()->create();
        $characters = Character::factory()->count(3)->create(['user_id' => $user->id]);
        ConsentRecord::factory()->forType(ConsentType::Analytics)->create(['user_id' => $user->id]);
        ConsentRecord::factory()->forType(ConsentType::CloudBackup)->create(['user_id' => $user->id]);

        $request = DeletionRequest::factory()->expired()->create(['user_id' => $user->id]);
        $userId = $user->id;

        $service = new DataDeletionService;
        $service->executeDeletion($request);

        expect(User::find($userId))->toBeNull();
        expect(Character::where('user_id', $userId)->count())->toBe(0);
        expect(ConsentRecord::where('user_id', $userId)->count())->toBe(0);

        $freshRequest = DeletionRequest::find($request->id);
        expect($freshRequest->status)->toBe(DeletionStatus::Completed);
        expect($freshRequest->deletion_log)->not->toBeEmpty();
    });

    it('grace period prevents premature deletion', function (): void {
        $request = DeletionRequest::factory()->create(['user_id' => $this->user->id]);

        expect($request->isWithinGracePeriod())->toBeTrue();
        expect($request->isGracePeriodExpired())->toBeFalse();
    });

    it('expired grace period allows deletion', function (): void {
        $request = DeletionRequest::factory()->expired()->create(['user_id' => $this->user->id]);

        expect($request->isWithinGracePeriod())->toBeFalse();
        expect($request->isGracePeriodExpired())->toBeTrue();
    });
});

/*
|--------------------------------------------------------------------------
| Privacy Dashboard Integration Tests
|--------------------------------------------------------------------------
*/
describe('Privacy Dashboard', function (): void {
    it('requires authentication', function (): void {
        $response = $this->get(route('privacy.dashboard'));

        $response->assertRedirect('/');
    });

    it('renders for authenticated users', function (): void {
        $this->actingAs($this->user);

        $response = $this->get(route('privacy.dashboard'));

        $response->assertSuccessful();
    });
});
