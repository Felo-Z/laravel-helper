<?php

namespace FeloZ\LaravelHelper\Support\Contracts;

use Illuminate\Http\JsonResponse;
use Throwable;

interface ApiResponseContract
{
    public function ok(mixed $data = null, string $message = ''): JsonResponse;

    public function created(mixed $data = null, string $message = '', ?string $location = null): JsonResponse;

    public function accepted(mixed $data = null, string $message = ''): JsonResponse;

    public function nonAuthoritativeInformation(mixed $data = null, string $message = ''): JsonResponse;

    public function noContent(string $message = ''): JsonResponse;

    public function resetContent(mixed $data = null, string $message = ''): JsonResponse;

    public function partialContent(mixed $data = null, string $message = ''): JsonResponse;

    public function multiStatus(mixed $data = null, string $message = ''): JsonResponse;

    public function alreadyReported(mixed $data = null, string $message = ''): JsonResponse;

    public function imUsed(mixed $data = null, string $message = ''): JsonResponse;

    public function success(mixed $data = null, string $message = '', int $code = 200): JsonResponse;

    public function message(string $message, int $code = 200, mixed $data = null): JsonResponse;

    public function failed(string $message = '', int $code = 400, ?array $error = null): JsonResponse;

    public function error(string $message = '', int $code = 400, ?array $error = null): JsonResponse;

    public function badRequest(string $message = '', ?array $error = null): JsonResponse;

    public function unauthorized(string $message = '', ?array $error = null): JsonResponse;

    public function paymentRequired(string $message = '', ?array $error = null): JsonResponse;

    public function forbidden(string $message = '', ?array $error = null): JsonResponse;

    public function notFound(string $message = '', ?array $error = null): JsonResponse;

    public function methodNotAllowed(string $message = '', ?array $error = null): JsonResponse;

    public function notAcceptable(string $message = '', ?array $error = null): JsonResponse;

    public function proxyAuthenticationRequired(string $message = '', ?array $error = null): JsonResponse;

    public function requestTimeout(string $message = '', ?array $error = null): JsonResponse;

    public function conflict(string $message = '', ?array $error = null): JsonResponse;

    public function gone(string $message = '', ?array $error = null): JsonResponse;

    public function lengthRequired(string $message = '', ?array $error = null): JsonResponse;

    public function preconditionFailed(string $message = '', ?array $error = null): JsonResponse;

    public function requestEntityTooLarge(string $message = '', ?array $error = null): JsonResponse;

    public function requestUriTooLong(string $message = '', ?array $error = null): JsonResponse;

    public function unsupportedMediaType(string $message = '', ?array $error = null): JsonResponse;

    public function requestedRangeNotSatisfiable(string $message = '', ?array $error = null): JsonResponse;

    public function expectationFailed(string $message = '', ?array $error = null): JsonResponse;

    public function iAmATeapot(string $message = '', ?array $error = null): JsonResponse;

    public function misdirectedRequest(string $message = '', ?array $error = null): JsonResponse;

    public function unprocessableEntity(string $message = '', ?array $error = null): JsonResponse;

    public function locked(string $message = '', ?array $error = null): JsonResponse;

    public function failedDependency(string $message = '', ?array $error = null): JsonResponse;

    public function tooEarly(string $message = '', ?array $error = null): JsonResponse;

    public function upgradeRequired(string $message = '', ?array $error = null): JsonResponse;

    public function preconditionRequired(string $message = '', ?array $error = null): JsonResponse;

    public function tooManyRequests(string $message = '', ?array $error = null): JsonResponse;

    public function requestHeaderFieldsTooLarge(string $message = '', ?array $error = null): JsonResponse;

    public function unavailableForLegalReasons(string $message = '', ?array $error = null): JsonResponse;

    public function internalServerError(string $message = '', ?array $error = null): JsonResponse;

    public function debug(mixed $payload = null, string $message = '', int $code = 500): JsonResponse;

    public function exception(Throwable $throwable): JsonResponse;

    public function json(
        bool|int|string $status,
        int $code,
        string $message = '',
        mixed $data = null,
        ?array $error = null
    ): JsonResponse;
}
