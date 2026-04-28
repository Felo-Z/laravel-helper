<?php

namespace FeloZ\LaravelHelper\Facades;

use FeloZ\LaravelHelper\Support\Contracts\ApiResponseContract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Http\JsonResponse ok(mixed $data = null, string $message = '')
 * @method static \Illuminate\Http\JsonResponse created(mixed $data = null, string $message = '', ?string $location = null)
 * @method static \Illuminate\Http\JsonResponse accepted(mixed $data = null, string $message = '')
 * @method static \Illuminate\Http\JsonResponse nonAuthoritativeInformation(mixed $data = null, string $message = '')
 * @method static \Illuminate\Http\JsonResponse noContent(string $message = '')
 * @method static \Illuminate\Http\JsonResponse resetContent(mixed $data = null, string $message = '')
 * @method static \Illuminate\Http\JsonResponse partialContent(mixed $data = null, string $message = '')
 * @method static \Illuminate\Http\JsonResponse multiStatus(mixed $data = null, string $message = '')
 * @method static \Illuminate\Http\JsonResponse alreadyReported(mixed $data = null, string $message = '')
 * @method static \Illuminate\Http\JsonResponse imUsed(mixed $data = null, string $message = '')
 * @method static \Illuminate\Http\JsonResponse success(mixed $data = null, string $message = '', int $code = 200)
 * @method static \Illuminate\Http\JsonResponse message(string $message, int $code = 200, mixed $data = null)
 * @method static \Illuminate\Http\JsonResponse failed(string $message = '', int $code = 400, ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse error(string $message = '', int $code = 400, ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse badRequest(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse unauthorized(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse paymentRequired(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse forbidden(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse notFound(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse methodNotAllowed(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse notAcceptable(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse proxyAuthenticationRequired(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse requestTimeout(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse conflict(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse gone(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse lengthRequired(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse preconditionFailed(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse requestEntityTooLarge(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse requestUriTooLong(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse unsupportedMediaType(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse requestedRangeNotSatisfiable(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse expectationFailed(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse iAmATeapot(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse misdirectedRequest(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse unprocessableEntity(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse locked(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse failedDependency(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse tooEarly(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse upgradeRequired(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse preconditionRequired(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse tooManyRequests(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse requestHeaderFieldsTooLarge(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse unavailableForLegalReasons(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse internalServerError(string $message = '', ?array $error = null)
 * @method static \Illuminate\Http\JsonResponse debug(mixed $payload = null, string $message = '', int $code = 500)
 * @method static \Illuminate\Http\JsonResponse exception(\Throwable $throwable)
 */
class Ap extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ApiResponseContract::class;
    }
}
