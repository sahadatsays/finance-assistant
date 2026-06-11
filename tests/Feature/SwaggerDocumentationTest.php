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

test('authenticated api docs contain sanctum security scheme', function () {
    $this->artisan('l5-swagger:generate', ['documentation' => 'authenticated'])->assertSuccessful();

    $spec = json_decode(file_get_contents(storage_path('api-docs/authenticated-api-docs.json')), true);

    expect($spec['openapi'])->toStartWith('3.0')
        ->and($spec['components']['securitySchemes'])->toHaveKey('sanctum')
        ->and($spec['paths'])->toHaveKey('/categories');
});
