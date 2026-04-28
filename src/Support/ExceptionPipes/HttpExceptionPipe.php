<?php

namespace FeloZ\LaravelHelper\Support\ExceptionPipes;

use Closure;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class HttpExceptionPipe
{
    public function handle(Throwable $throwable, Closure $next): array
    {
        $structure = $next($throwable);
        if (! $throwable instanceof HttpExceptionInterface) {
            return $structure;
        }

        return [
            'code' => $throwable->getStatusCode(),
            'message' => $throwable->getMessage(),
            'headers' => $throwable->getHeaders(),
        ] + $structure;
    }
}
