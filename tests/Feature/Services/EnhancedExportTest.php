<?php

use App\Models\Career;
use App\Models\Character;
use App\Models\TrainingSession;
use App\Models\User;
use App\Services\Export\ExcelExportService;
use App\Services\Export\PDFExportService;
use App\Services\Share\ShareLinkService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

describe('PDFExportService', function () {
    it('exports a career as HTML content', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id, 'name' => 'Special Week']);
        $career = Career::factory()->create([
            'user_id' => $user->id,
            'character_id' => $character->id,
            'career_name' => 'Test Run',
            'scenario_type' => 'ura_finale',
            'final_speed' => 800,
            'final_stamina' => 700,
            'final_power' => 600,
            'final_guts' => 500,
            'final_wit' => 400,
        ]);

        $service = new PDFExportService;
        $result = $service->exportCareer($career);

        expect($result)->toHaveKeys(['content', 'filename', 'metadata'])
            ->and($result['content'])->toContain('Career Report')
            ->and($result['content'])->toContain('800')
            ->and($result['metadata']['career_id'])->toBe($career->id)
            ->and($result['metadata']['character_name'])->toBe('Special Week')
            ->and($result['filename'])->toContain('.html');
    });

    it('includes training history in export', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);
        $career = Career::factory()->create(['user_id' => $user->id, 'character_id' => $character->id]);

        TrainingSession::factory()->create([
            'career_id' => $career->id,
            'character_id' => $character->id,
            'turn_number' => 1,
            'training_type' => 'speed',
            'speed_gain' => 15,
        ]);

        $service = new PDFExportService;
        $result = $service->exportCareer($career);

        expect($result['content'])->toContain('Training History')
            ->and($result['content'])->toContain('speed');
    });

    it('exports character summary', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id, 'name' => 'Silence Suzuka']);
        Career::factory()->count(3)->create(['user_id' => $user->id, 'character_id' => $character->id, 'status' => 'completed']);

        $service = new PDFExportService;
        $result = $service->exportCharacterSummary($character);

        expect($result)->toHaveKeys(['content', 'filename', 'metadata'])
            ->and($result['content'])->toContain('Character Summary')
            ->and($result['content'])->toContain('Silence Suzuka');
    });

    it('generates valid HTML with proper structure', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);
        $career = Career::factory()->create(['user_id' => $user->id, 'character_id' => $character->id]);

        $service = new PDFExportService;
        $result = $service->exportCareer($career);

        expect($result['content'])->toContain('<!DOCTYPE html>')
            ->and($result['content'])->toContain('</html>')
            ->and($result['content'])->toContain('<body>')
            ->and($result['content'])->toContain('</body>');
    });
});

describe('ExcelExportService', function () {
    it('exports career with multiple sheets', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);
        $career = Career::factory()->create([
            'user_id' => $user->id,
            'character_id' => $character->id,
            'career_name' => 'Excel Test Run',
            'final_speed' => 1000,
            'final_stamina' => 900,
            'final_power' => 800,
            'final_guts' => 700,
            'final_wit' => 600,
        ]);

        $service = new ExcelExportService;
        $result = $service->exportCareer($career);

        expect($result)->toHaveKeys(['sheets', 'filename', 'metadata'])
            ->and($result['sheets'])->toHaveKeys(['Overview', 'Stats', 'Training', 'Races', 'Skills'])
            ->and($result['filename'])->toContain('.xlsx');
    });

    it('builds overview sheet with career details', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id, 'name' => 'Tokai Teio']);
        $career = Career::factory()->create([
            'user_id' => $user->id,
            'character_id' => $character->id,
            'career_name' => 'Overview Test',
            'scenario_type' => 'unity_cup',
        ]);

        $service = new ExcelExportService;
        $result = $service->exportCareer($career);
        $overview = $result['sheets']['Overview'];

        expect($overview['headers'])->toBe(['Field', 'Value'])
            ->and($overview['rows'][0])->toBe(['Career Name', 'Overview Test'])
            ->and($overview['rows'][1])->toBe(['Character', 'Tokai Teio']);
    });

    it('builds stats sheet with percentages', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);
        $career = Career::factory()->create([
            'user_id' => $user->id,
            'character_id' => $character->id,
            'final_speed' => 500,
            'final_stamina' => 500,
            'final_power' => 500,
            'final_guts' => 500,
            'final_wit' => 500,
        ]);

        $service = new ExcelExportService;
        $result = $service->exportCareer($career);
        $stats = $result['sheets']['Stats'];

        expect($stats['headers'])->toContain('Percentage')
            ->and($stats['rows'][0][2])->toBe('20%');
    });

    it('exports career as CSV', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);
        $career = Career::factory()->create(['user_id' => $user->id, 'character_id' => $character->id]);

        TrainingSession::factory()->create([
            'career_id' => $career->id,
            'character_id' => $character->id,
            'turn_number' => 1,
            'training_type' => 'speed',
            'speed_gain' => 10,
            'stamina_gain' => 2,
            'power_gain' => 3,
            'guts_gain' => 1,
            'wit_gain' => 0,
            'sp_gain' => 5,
        ]);

        $service = new ExcelExportService;
        $result = $service->exportAsCsv($career);

        expect($result)->toHaveKeys(['content', 'filename'])
            ->and($result['content'])->toContain('Turn,Training Type')
            ->and($result['content'])->toContain('"speed"')
            ->and($result['filename'])->toContain('.csv');
    });

    it('handles career with no training sessions', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);
        $career = Career::factory()->create(['user_id' => $user->id, 'character_id' => $character->id]);

        $service = new ExcelExportService;
        $result = $service->exportCareer($career);

        expect($result['sheets']['Training']['rows'])->toBeEmpty();
    });
});

describe('ShareLinkService', function () {
    it('creates a share link with default options', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);
        $career = Career::factory()->create(['user_id' => $user->id, 'character_id' => $character->id]);

        $service = new ShareLinkService;
        $result = $service->createShareLink($career);

        expect($result)->toHaveKeys(['token', 'url', 'privacy', 'expires_at', 'created_at'])
            ->and($result['privacy'])->toBe('unlisted')
            ->and($result['url'])->toContain('/share/')
            ->and($result['token'])->not->toBeEmpty();
    });

    it('creates a share link with password protection', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);
        $career = Career::factory()->create(['user_id' => $user->id, 'character_id' => $character->id]);

        $service = new ShareLinkService;
        $result = $service->createShareLink($career, [
            'privacy' => 'password',
            'password' => 'secret123',
        ]);

        expect($result['privacy'])->toBe('password');

        $access = $service->accessShareLink($result['token'], 'secret123');
        expect($access['success'])->toBeTrue();

        $badAccess = $service->accessShareLink($result['token'], 'wrong');
        expect($badAccess['success'])->toBeFalse()
            ->and($badAccess['error'])->toBe('Invalid password.');
    });

    it('accesses a valid share link', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id, 'name' => 'Gold Ship']);
        $career = Career::factory()->create([
            'user_id' => $user->id,
            'character_id' => $character->id,
            'career_name' => 'Shared Run',
            'final_speed' => 1000,
        ]);

        $service = new ShareLinkService;
        $link = $service->createShareLink($career);

        $result = $service->accessShareLink($link['token']);

        expect($result['success'])->toBeTrue()
            ->and($result['data']['career_name'])->toBe('Shared Run')
            ->and($result['data']['character_name'])->toBe('Gold Ship')
            ->and($result['data']['stats']['speed'])->toBe(1000);
    });

    it('returns error for invalid share link', function () {
        $service = new ShareLinkService;
        $result = $service->accessShareLink('nonexistent-token');

        expect($result['success'])->toBeFalse()
            ->and($result['error'])->toContain('not found');
    });

    it('revokes a share link', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);
        $career = Career::factory()->create(['user_id' => $user->id, 'character_id' => $character->id]);

        $service = new ShareLinkService;
        $link = $service->createShareLink($career);

        expect($service->isValidShareLink($link['token']))->toBeTrue();

        $revoked = $service->revokeShareLink($link['token']);
        expect($revoked)->toBeTrue();

        expect($service->isValidShareLink($link['token']))->toBeFalse();
    });

    it('tracks view count', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);
        $career = Career::factory()->create(['user_id' => $user->id, 'character_id' => $character->id]);

        $service = new ShareLinkService;
        $link = $service->createShareLink($career);

        expect($service->getViewCount($link['token']))->toBe(0);

        $service->accessShareLink($link['token']);
        expect($service->getViewCount($link['token']))->toBe(1);

        $service->accessShareLink($link['token']);
        expect($service->getViewCount($link['token']))->toBe(2);
    });

    it('returns share link info without accessing content', function () {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);
        $career = Career::factory()->create(['user_id' => $user->id, 'character_id' => $character->id]);

        $service = new ShareLinkService;
        $link = $service->createShareLink($career, ['privacy' => 'public']);

        $info = $service->getShareLinkInfo($link['token']);

        expect($info['exists'])->toBeTrue()
            ->and($info['privacy'])->toBe('public')
            ->and($info['requires_password'])->toBeFalse()
            ->and($info['view_count'])->toBe(0);
    });

    it('returns not found for revoked link', function () {
        $service = new ShareLinkService;

        $info = $service->getShareLinkInfo('invalid');
        expect($info['exists'])->toBeFalse();
    });

    it('supports different privacy levels', function (string $privacy) {
        $user = User::factory()->create();
        $character = Character::factory()->create(['user_id' => $user->id]);
        $career = Career::factory()->create(['user_id' => $user->id, 'character_id' => $character->id]);

        $service = new ShareLinkService;
        $link = $service->createShareLink($career, ['privacy' => $privacy]);

        expect($link['privacy'])->toBe($privacy);
    })->with(['public', 'unlisted', 'password']);

    it('cannot revoke non-existent link', function () {
        $service = new ShareLinkService;

        expect($service->revokeShareLink('nonexistent'))->toBeFalse();
    });
});
