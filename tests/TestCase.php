<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
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
