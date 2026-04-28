<?php

namespace FeloZ\LaravelHelper\Support\RenderUsings;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ShouldReturnJsonRenderUsing
{
    public function __invoke(Throwable $throwable, Request $request): ?JsonResponse
    {
        $apiPaths = (array) config('felo-helper.api_response.render_api_paths', ['api/*']);
        $shouldRender = $request->expectsJson() || $request->is($apiPaths);

        if (! $shouldRender) {
            return null;
        }

        return ap()->exception($throwable);
    }
}
