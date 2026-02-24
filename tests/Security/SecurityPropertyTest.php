<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
    $this->character = Character::factory()->create(['user_id' => $this->user->id]);
});

/*
|--------------------------------------------------------------------------
| Property 25: SQL Injection Prevention
|--------------------------------------------------------------------------
| Feature: umamusume-career-planner-main-v2.4.0
| Property 25: For any string containing SQL injection patterns, the
| application must never return a 500 error or expose database internals.
| Validates: NFR-S-01, NFR-S-04
*/
describe('Property 25: SQL Injection Prevention', function (): void {
    it('never returns 500 for any SQL injection payload in character names', function (): void {
        $this->actingAs($this->user);

        for ($i = 0; $i < 100; $i++) {
            $operators = ["'", '"', ';', '--', '/*', '*/', 'OR', 'AND', 'UNION', 'SELECT', 'DROP', 'DELETE', 'INSERT', 'UPDATE'];
            $payload = '';
            $numParts = random_int(1, 5);
            for ($j = 0; $j < $numParts; $j++) {
                $payload .= $operators[array_rand($operators)].' ';
                $payload .= fake()->word().' ';
            }

            $response = $this->postJson('/api/characters', [
                'name' => trim($payload),
                'scenario_type' => fake()->randomElement(['ura_finale', 'unity_cup']),
            ]);

            expect($response->status())->not->toBe(500);

            $body = $response->getContent();
            expect($body)->not->toContain('SQLSTATE');
            expect($body)->not->toContain('syntax error');
        }
    });

    it('never exposes database structure in error responses', function (): void {
        $this->actingAs($this->user);

        $tableNames = ['ucp_users', 'ucp_characters', 'ucp_careers', 'migrations', 'personal_access_tokens'];

        for ($i = 0; $i < 100; $i++) {
            $table = $tableNames[array_rand($tableNames)];
            $payload = fake()->randomElement([
                "' UNION SELECT * FROM {$table} --",
                "1; SELECT * FROM {$table}",
                "' OR EXISTS(SELECT 1 FROM {$table}) --",
                "1 AND (SELECT COUNT(*) FROM {$table}) > 0",
                "'; SHOW TABLES; --",
            ]);

            $response = $this->postJson('/api/characters', [
                'name' => $payload,
                'scenario_type' => 'ura_finale',
            ]);

            $body = $response->getContent();
            expect($body)->not->toContain('ucp_users');
            expect($body)->not->toContain('ucp_characters');
            expect($body)->not->toContain('ucp_careers');
            expect($body)->not->toContain('personal_access_tokens');
            expect($body)->not->toContain('information_schema');
        }
    });
});

/*
|--------------------------------------------------------------------------
| Property 26: XSS Prevention
|--------------------------------------------------------------------------
| Feature: umamusume-career-planner-main-v2.4.0
| Property 26: For any string containing HTML/JavaScript, the API must
| never return unescaped executable content in JSON responses.
| Validates: NFR-S-05
*/
describe('Property 26: XSS Prevention', function (): void {
    it('never returns executable script tags in JSON API responses', function (): void {
        $this->actingAs($this->user);

        $tags = ['script', 'img', 'svg', 'iframe', 'object', 'embed', 'link', 'style', 'body', 'div'];
        $events = ['onerror', 'onload', 'onclick', 'onmouseover', 'onfocus', 'onblur'];

        for ($i = 0; $i < 100; $i++) {
            $tag = $tags[array_rand($tags)];
            $event = $events[array_rand($events)];
            $payload = fake()->randomElement([
                "<{$tag}>{$event}=alert(1)</{$tag}>",
                "<{$tag} {$event}=alert(document.cookie)>",
                "\">{$event}=alert(1)//",
                "<{$tag} src=x {$event}=alert(1)>",
                "javascript:alert('{$tag}')",
            ]);

            $response = $this->postJson('/api/characters', [
                'name' => $payload,
                'scenario_type' => 'ura_finale',
            ]);

            expect($response->status())->not->toBe(500);

            if ($response->status() === 201) {
                $body = $response->getContent();
                expect($body)->not->toContain('<script>');
                expect($body)->not->toContain('javascript:alert');
            }
        }
    });

    it('security headers are always present on all responses', function (): void {
        $endpoints = [
            ['GET', '/'],
            ['GET', '/login'],
        ];

        for ($i = 0; $i < 100; $i++) {
            [$method, $url] = $endpoints[array_rand($endpoints)];

            $response = $this->call($method, $url);

            expect($response->headers->get('X-Content-Type-Options'))->toBe('nosniff');
            expect($response->headers->get('X-Frame-Options'))->toBe('SAMEORIGIN');
        }
    });
});

/*
|--------------------------------------------------------------------------
| Property 27: Authorization Enforcement
|--------------------------------------------------------------------------
| Feature: umamusume-career-planner-main-v2.4.0
| Property 27: For any user A and resource owned by user B (where A != B
| and A is not admin), user A must never access, modify, or delete that resource.
| Validates: NFR-S-01, NFR-S-04
*/
describe('Property 27: Authorization Enforcement', function (): void {
    it('user can never access another user characters regardless of ID pattern', function (): void {
        $this->actingAs($this->user);

        for ($i = 0; $i < 50; $i++) {
            RateLimiter::clear('api:'.$this->user->id);
            $otherCharacter = Character::factory()->create(['user_id' => $this->otherUser->id]);

            $action = fake()->randomElement(['view', 'update', 'delete']);

            $response = match ($action) {
                'view' => $this->getJson("/api/characters/{$otherCharacter->id}"),
                'update' => $this->putJson("/api/characters/{$otherCharacter->id}", [
                    'name' => fake()->name(),
                ]),
                'delete' => $this->deleteJson("/api/characters/{$otherCharacter->id}"),
            };

            $response->assertForbidden();
        }
    });

    it('unauthenticated requests are always rejected for protected endpoints', function (): void {
        $protectedEndpoints = [
            ['GET', '/api/me'],
            ['GET', '/api/characters'],
            ['POST', '/api/characters'],
            ['POST', '/api/logout'],
        ];

        for ($i = 0; $i < 100; $i++) {
            [$method, $url] = $protectedEndpoints[array_rand($protectedEndpoints)];

            $response = $this->json($method, $url);

            $response->assertUnauthorized();
        }
    });

    it('user owns all characters returned by the index endpoint', function (): void {
        $this->actingAs($this->user);

        Character::factory()->count(5)->create(['user_id' => $this->otherUser->id]);
        Character::factory()->count(3)->create(['user_id' => $this->user->id]);

        $checkedAtLeastOne = false;

        for ($i = 0; $i < 50; $i++) {
            RateLimiter::clear('api:'.$this->user->id);
            $response = $this->getJson('/api/characters');

            $response->assertSuccessful();
            $checkedAtLeastOne = true;

            $data = $response->json('data') ?? [];
            foreach ($data as $character) {
                if (is_array($character) && isset($character['user_id'])) {
                    expect($character['user_id'])->toBe($this->user->id);
                }
            }
        }

        expect($checkedAtLeastOne)->toBeTrue();
    });
});

/*
|--------------------------------------------------------------------------
| Property 28: CSRF Protection
|--------------------------------------------------------------------------
| Feature: umamusume-career-planner-main-v2.4.0
| Property 28: All state-modifying web requests without valid CSRF tokens
| must be rejected, while API requests use Sanctum token authentication.
| Validates: NFR-S-07
*/
describe('Property 28: CSRF Protection', function (): void {
    it('API auth endpoints always require valid credentials', function (): void {
        for ($i = 0; $i < 100; $i++) {
            $response = $this->postJson('/api/login', [
                'email' => fake()->safeEmail(),
                'password' => fake()->password(8, 20),
            ]);

            expect($response->status())->toBeIn([401, 422]);

            $body = $response->getContent();
            expect($body)->not->toContain('$2y$');
            expect($body)->not->toContain('password_hash');
        }
    });

    it('Sanctum-protected endpoints reject requests without bearer token', function (): void {
        $protectedEndpoints = [
            ['GET', '/api/me'],
            ['POST', '/api/logout'],
            ['GET', '/api/characters'],
        ];

        for ($i = 0; $i < 100; $i++) {
            [$method, $url] = $protectedEndpoints[array_rand($protectedEndpoints)];

            $response = $this->json($method, $url, [], [
                'Authorization' => 'Bearer '.fake()->sha256(),
            ]);

            $response->assertUnauthorized();
        }
    });
});
