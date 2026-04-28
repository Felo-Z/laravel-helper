<?php

namespace FeloZ\LaravelHelper\Support\Pipes;

use Closure;
use Illuminate\Http\JsonResponse;

class ErrorPipe
{
    public function handle(array $structure, Closure $next): JsonResponse
    {
        $hideError = ! app()->hasDebugModeEnabled()
            && (bool) config('felo-helper.api_response.hide_error_when_not_debug', true);

        if ($hideError) {
            unset($structure['error']);
        } elseif (! array_key_exists('error', $structure) || $structure['error'] === null) {
            $structure['error'] = (object) [];
        }

        return $next($structure);
    }
}
