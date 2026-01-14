<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * @method \Illuminate\Testing\TestResponse get(string $uri, array<string, string> $headers = [])
 * @method \Illuminate\Testing\TestResponse getJson(string $uri, array<string, string> $headers = [])
 * @method \Illuminate\Testing\TestResponse post(string $uri, array<string, mixed> $data = [], array<string, string> $headers = [])
 * @method \Illuminate\Testing\TestResponse postJson(string $uri, array<string, mixed> $data = [], array<string, string> $headers = [])
 * @method \Illuminate\Testing\TestResponse put(string $uri, array<string, mixed> $data = [], array<string, string> $headers = [])
 * @method \Illuminate\Testing\TestResponse putJson(string $uri, array<string, mixed> $data = [], array<string, string> $headers = [])
 * @method \Illuminate\Testing\TestResponse patch(string $uri, array<string, mixed> $data = [], array<string, string> $headers = [])
 * @method \Illuminate\Testing\TestResponse patchJson(string $uri, array<string, mixed> $data = [], array<string, string> $headers = [])
 * @method \Illuminate\Testing\TestResponse delete(string $uri, array<string, mixed> $data = [], array<string, string> $headers = [])
 * @method \Illuminate\Testing\TestResponse deleteJson(string $uri, array<string, mixed> $data = [], array<string, string> $headers = [])
 * @method $this withHeader(string $name, string $value)
 * @method $this withHeaders(array<string, string> $headers)
 * @method $this withSession(array<string, mixed> $data)
 * @method $this actingAs(\Illuminate\Contracts\Auth\Authenticatable $user, string $guard = null)
 * @method void assertDatabaseHas(string $table, array<string, mixed> $data, string $connection = null)
 * @method void assertDatabaseMissing(string $table, array<string, mixed> $data, string $connection = null)
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}
