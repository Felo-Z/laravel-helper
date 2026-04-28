<?php

namespace FeloZ\LaravelHelper\Support\Pipes;

use Closure;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class StatusCodePipe
{
    public function handle(array $structure, Closure $next): JsonResponse
    {
        $response = $next($structure);
        $code = (int) ($structure['code'] ?? 0);
        $strategy = (string) config('felo-helper.api_response.status_code_strategy', 'smart');

        if ($code >= 100 && $code <= 599) {
            return $response->setStatusCode($code);
        }

        if ($strategy === 'legacy') {
            return $response->setStatusCode($structure['status'] ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // smart 模式下，业务码（非 HTTP）默认映射为 200/400，避免误判为 500 系统异常
        return $response->setStatusCode($structure['status'] ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
    }
}
