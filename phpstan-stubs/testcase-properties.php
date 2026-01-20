<?php

namespace PHPUnit\Framework;

/**
 * @method void assertDatabaseHas(string $table, array<string, mixed> $data, string $connection = null)
 * @method void assertDatabaseMissing(string $table, array<string, mixed> $data, string $connection = null)
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
 * @method \PHPUnit\Framework\MockObject\MockObject createMock(string $originalClassName)
 * @method \PHPUnit\Framework\MockObject\Rule\InvocationOrder once()
 * @method \PHPUnit\Framework\MockObject\Rule\InvocationOrder exactly(int $count)
 * @method void expectException(string $exception)
 * @method void expectExceptionMessage(string $message)
 *
 * @property mixed $user
 * @property mixed $character
 * @property mixed $service
 * @property mixed $mcpClient
 * @property mixed $costManager
 * @property mixed $pricingService
 * @property mixed $knowledgeService
 * @property mixed $apiService
 * @property mixed $deckService
 * @property mixed $optimizationService
 * @property mixed $friendshipService
 * @property mixed $supportCards
 * @property mixed $supportCard
 * @property mixed $characterSupportCard
 * @property mixed $hintService
 * @property mixed $evolutionService
 * @property mixed $targetSkills
 * @property mixed $currentSkills
 * @property mixed $trainingAgent
 * @property mixed $agent
 * @property mixed $careerAgent
 * @property mixed $raceAgent
 * @property mixed $skillAgent
 * @property mixed $orchestration
 * @property mixed $deck
 * @property mixed $normalSkill
 * @property mixed $rareSkill
 * @property mixed $skill
 * @property mixed $skills
 */
abstract class TestCase extends Assert
{
    /**
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->$name ?? null;
    }
}

namespace Pest\PendingCalls;

/**
 * @property mixed $user
 * @property mixed $character
 * @property mixed $service
 * @property mixed $mcpClient
 * @property mixed $trainingAgent
 */
class TestCall
{
    /**
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->$name ?? null;
    }
}
