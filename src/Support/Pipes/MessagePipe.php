<?php

namespace FeloZ\LaravelHelper\Support\Pipes;

use Closure;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class MessagePipe
{
    public function handle(array $structure, Closure $next): JsonResponse
    {
        if ($structure['message'] === '') {
            $code = (int) ($structure['code'] ?? Response::HTTP_OK);
            $structure['message'] = Response::$statusTexts[$code]
                ?? ($structure['status'] ? 'OK' : 'Error');
        }

        return $next($structure);
    }
}
