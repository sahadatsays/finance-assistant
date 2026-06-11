<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;

class VersionController extends ApiController
{
    public function __invoke(): JsonResponse
    {
        $version = config('api.default_version', 'v1');
        $versionConfig = config("api.versions.{$version}", []);

        return $this->success(
            data: [
                'version' => $version,
                'status' => $versionConfig['status'] ?? 'stable',
                'name' => config('app.name').' API',
                'documentation' => $versionConfig['documentation'] ?? null,
                'supported_versions' => array_keys(config('api.versions', [])),
            ],
            message: 'Finance Assistant API '.$version,
        );
    }
}
