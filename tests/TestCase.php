<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * @property mixed $parser Parser instance used in various parsing tests
 * @property mixed $service Service instance used in service tests
 * @property mixed $detector Detector instance used in detection tests
 * @property mixed $analysisService Analysis service instance
 * @property mixed $character Character model instance used in character-related tests
 * @property mixed $hintService Hint service instance used in skill hint tests
 * @property mixed $evolutionService Evolution service instance used in skill evolution tests
 * @property mixed $skillService Skill service instance used in skill-related tests
 * @property mixed $synergyScorer Synergy scorer instance used in synergy calculation tests
 * @property mixed $user User model instance used in authentication and authorization tests
 * @property mixed $imageProcessor Image processor instance used in OCR and image processing tests
 * @property mixed $predictionService Prediction service instance used in prediction tests
 * @property mixed $mcpClient MCP client instance used in MCP integration tests
 *
 * @method \Illuminate\Testing\TestResponse<\Illuminate\Http\Response> get(string $uri, array<string, string> $headers = [])
 * @method \Illuminate\Testing\TestResponse<\Illuminate\Http\JsonResponse> getJson(string $uri, array<string, string> $headers = [])
 * @method \Illuminate\Testing\TestResponse<\Illuminate\Http\Response> post(string $uri, array<string, mixed> $data = [], array<string, string> $headers = [])
 * @method \Illuminate\Testing\TestResponse<\Illuminate\Http\JsonResponse> postJson(string $uri, array<string, mixed> $data = [], array<string, string> $headers = [])
 * @method \Illuminate\Testing\TestResponse<\Illuminate\Http\Response> put(string $uri, array<string, mixed> $data = [], array<string, string> $headers = [])
 * @method \Illuminate\Testing\TestResponse<\Illuminate\Http\JsonResponse> putJson(string $uri, array<string, mixed> $data = [], array<string, string> $headers = [])
 * @method \Illuminate\Testing\TestResponse<\Illuminate\Http\Response> patch(string $uri, array<string, mixed> $data = [], array<string, string> $headers = [])
 * @method \Illuminate\Testing\TestResponse<\Illuminate\Http\JsonResponse> patchJson(string $uri, array<string, mixed> $data = [], array<string, string> $headers = [])
 * @method \Illuminate\Testing\TestResponse<\Illuminate\Http\Response> delete(string $uri, array<string, mixed> $data = [], array<string, string> $headers = [])
 * @method \Illuminate\Testing\TestResponse<\Illuminate\Http\JsonResponse> deleteJson(string $uri, array<string, mixed> $data = [], array<string, string> $headers = [])
 * @method $this withHeader(string $name, string $value)
 * @method $this withHeaders(array<string, string> $headers)
 * @method $this withSession(array<string, mixed> $data)
 * @method $this actingAs(\Illuminate\Contracts\Auth\Authenticatable $user, string $guard = null)
 * @method void assertDatabaseHas(string $table, array<string, mixed> $data, string $connection = null)
 * @method void assertDatabaseMissing(string $table, array<string, mixed> $data, string $connection = null)
 * @method \Illuminate\Testing\PendingCommand artisan(string $command, array<string, mixed> $parameters = [])
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}
