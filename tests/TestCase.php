<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * @method \Illuminate\Testing\TestResponse get(string $uri, array $headers = [])
 * @method \Illuminate\Testing\TestResponse getJson(string $uri, array $headers = [])
 * @method \Illuminate\Testing\TestResponse post(string $uri, array $data = [], array $headers = [])
 * @method \Illuminate\Testing\TestResponse postJson(string $uri, array $data = [], array $headers = [])
 * @method \Illuminate\Testing\TestResponse put(string $uri, array $data = [], array $headers = [])
 * @method \Illuminate\Testing\TestResponse putJson(string $uri, array $data = [], array $headers = [])
 * @method \Illuminate\Testing\TestResponse patch(string $uri, array $data = [], array $headers = [])
 * @method \Illuminate\Testing\TestResponse patchJson(string $uri, array $data = [], array $headers = [])
 * @method \Illuminate\Testing\TestResponse delete(string $uri, array $data = [], array $headers = [])
 * @method \Illuminate\Testing\TestResponse deleteJson(string $uri, array $data = [], array $headers = [])
 * @method $this withHeader(string $name, string $value)
 * @method $this withHeaders(array $headers)
 * @method $this withSession(array $data)
 * @method $this actingAs(\Illuminate\Contracts\Auth\Authenticatable $user, string $guard = null)
 * @method void assertDatabaseHas(string $table, array $data, string $connection = null)
 * @method void assertDatabaseMissing(string $table, array $data, string $connection = null)
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}
