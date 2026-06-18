<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

class OpenApiController extends Controller
{
    public function __invoke()
    {
        $paths = [];
        foreach (Route::getRoutes() as $route) {
            if (! str_starts_with($route->uri(), 'api/')) {
                continue;
            }

            $methods = array_values(array_diff($route->methods(), ['HEAD']));
            if ($methods === []) {
                continue;
            }

            $paths['/'.ltrim($route->uri(), '/')] = [
                strtolower($methods[0]) => [
                    'summary' => $route->getName() ?? $route->uri(),
                    'tags' => [explode('/', $route->uri())[1] ?? 'api'],
                    'security' => str_contains($route->uri(), 'login') || str_contains($route->uri(), 'webhooks/') ? [] : [['sanctum' => []]],
                ],
            ];
        }

        return response()->json([
            'openapi' => '3.0.3',
            'info' => ['title' => 'FlowCRM API', 'version' => '1.0.0', 'description' => 'API REST do FlowCRM'],
            'servers' => [['url' => url('/api')]],
            'components' => [
                'securitySchemes' => [
                    'sanctum' => ['type' => 'http', 'scheme' => 'bearer', 'bearerFormat' => 'Sanctum'],
                ],
            ],
            'paths' => $paths,
        ]);
    }
}
