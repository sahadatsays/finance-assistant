<?php

test('swagger ui is available in local environment', function () {
    config(['swagger.ui_enabled' => true]);

    $this->artisan('l5-swagger:generate', ['--all' => true])->assertSuccessful();

    $this->get('/api/documentation')->assertSuccessful();
    $this->get('/api/documentation/public')->assertSuccessful();
    $this->get('/api/documentation/admin')->assertSuccessful();
    $this->get('/api/documentation/docs')->assertSuccessful();
});

test('swagger ui is disabled when ui_enabled is false', function () {
    config(['swagger.ui_enabled' => false]);

    $this->get('/api/documentation')->assertNotFound();
    $this->get('/api/documentation/public')->assertNotFound();
    $this->get('/api/documentation/admin')->assertNotFound();
});

test('swagger docs can be generated for all documentations', function () {
    $this->artisan('l5-swagger:generate', ['--all' => true])->assertSuccessful();

    expect(file_exists(storage_path('api-docs/public-api-docs.json')))->toBeTrue()
        ->and(file_exists(storage_path('api-docs/authenticated-api-docs.json')))->toBeTrue()
        ->and(file_exists(storage_path('api-docs/admin-api-docs.json')))->toBeTrue();
});

test('authenticated api docs define sanctum bearer security with global protection', function () {
    $this->artisan('l5-swagger:generate', ['documentation' => 'authenticated'])->assertSuccessful();

    $spec = json_decode(file_get_contents(storage_path('api-docs/authenticated-api-docs.json')), true);

    expect($spec['openapi'])->toStartWith('3.0')
        ->and($spec['components']['securitySchemes']['sanctum'])->toMatchArray([
            'type' => 'http',
            'scheme' => 'bearer',
            'bearerFormat' => 'Sanctum',
        ])
        ->and($spec['security'])->toBe([['sanctum' => []]])
        ->and($spec['paths']['/categories']['get']['security'])->toBe([['sanctum' => []], ['tenant' => []]])
        ->and($spec['paths']['/auth/logout']['post']['security'])->toBe([['sanctum' => []]])
        ->and($spec['paths']['/auth/profile']['get']['security'])->toBe([['sanctum' => []]]);
});

test('public api docs expose login without security and include sanctum scheme', function () {
    $this->artisan('l5-swagger:generate', ['documentation' => 'public'])->assertSuccessful();

    $spec = json_decode(file_get_contents(storage_path('api-docs/public-api-docs.json')), true);

    expect($spec['components']['securitySchemes'])->toHaveKey('sanctum')
        ->and($spec['paths']['/auth/login']['post']['security'])->toBe([])
        ->and($spec['paths']['/health']['get']['security'])->toBe([]);
});

test('admin api docs require sanctum bearer authentication globally', function () {
    $this->artisan('l5-swagger:generate', ['documentation' => 'admin'])->assertSuccessful();

    $spec = json_decode(file_get_contents(storage_path('api-docs/admin-api-docs.json')), true);

    expect($spec['security'])->toBe([['sanctum' => []]])
        ->and($spec['components']['securitySchemes']['sanctum']['scheme'])->toBe('bearer');
});
