<?php

namespace FeloZ\LaravelHelper\Support\ExceptionPipes;

use Closure;
use Illuminate\Validation\ValidationException;
use Throwable;

class ValidationExceptionPipe
{
    public function handle(Throwable $throwable, Closure $next): array
    {
        $structure = $next($throwable);
        if (! $throwable instanceof ValidationException) {
            return $structure;
        }

        return [
            'code' => $throwable->status,
            'message' => $throwable->validator->errors()->first(),
            'error' => $throwable->errors(),
        ] + $structure;
    }
}
