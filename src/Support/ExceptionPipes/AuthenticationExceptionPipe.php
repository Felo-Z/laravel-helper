<?php

namespace FeloZ\LaravelHelper\Support\ExceptionPipes;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuthenticationExceptionPipe
{
    public function handle(Throwable $throwable, Closure $next): array
    {
        $structure = $next($throwable);
        if (! $throwable instanceof AuthenticationException) {
            return $structure;
        }

        return [
            'code' => Response::HTTP_UNAUTHORIZED,
            'message' => $throwable->getMessage() ?: 'Unauthenticated.',
        ] + $structure;
    }
}
