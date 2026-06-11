<?php

use Illuminate\Support\Facades\Log;

it('returns version info with standard envelope', function () {
    $response = $this->getJson('/api/v1');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'version',
                'status',
                'name',
                'documentation',
                'supported_versions',
            ],
            'meta',
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'version' => 'v1',
                'status' => 'stable',
            ],
        ]);
});

it('returns health check with standard envelope', function () {
    $response = $this->getJson('/api/v1/health');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'status',
                'timestamp',
            ],
            'meta',
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'status' => 'ok',
            ],
        ]);
});

it('returns standardized not found error envelope', function () {
    $response = $this->getJson('/api/v1/non-existent-endpoint');

    $response->assertNotFound()
        ->assertJson([
            'success' => false,
            'message' => 'Endpoint not found.',
            'data' => [],
            'meta' => [],
        ]);
});

it('returns standardized unauthenticated error envelope', function () {
    $response = $this->getJson('/api/v1/auth/profile');

    $response->assertUnauthorized()
        ->assertJson([
            'success' => false,
            'message' => 'Unauthenticated.',
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data',
            'meta',
        ]);
});

it('returns standardized validation error envelope', function () {
    $response = $this->postJson('/api/v1/auth/login', []);

    $response->assertUnprocessable()
        ->assertJson([
            'success' => false,
            'message' => 'The given data was invalid.',
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'errors' => [
                    'email',
                    'password',
                ],
            ],
            'meta',
        ]);
});

it('logs api requests to the api channel', function () {
    Log::spy();

    $this->getJson('/api/v1/health');

    Log::shouldHaveReceived('channel')
        ->with('api')
        ->once();
});

it('applies api rate limiting headers', function () {
    $response = $this->getJson('/api/v1/health');

    $response->assertSuccessful();
    expect($response->headers->has('X-RateLimit-Limit'))->toBeTrue();
});
