<?php

namespace FeloZ\LaravelHelper\Support;

use FeloZ\LaravelHelper\Support\Contracts\ApiResponseContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Traits\Macroable;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ApiResponse implements ApiResponseContract
{
    use Macroable;

    public function ok(mixed $data = null, string $message = ''): JsonResponse
    {
        return $this->success($data, $message, Response::HTTP_OK);
    }

    public function created(mixed $data = null, string $message = '', ?string $location = null): JsonResponse
    {
        $response = $this->success($data, $message, Response::HTTP_CREATED);
        if ($location) {
            $response->headers->set('Location', $location);
        }

        return $response;
    }

    public function accepted(mixed $data = null, string $message = ''): JsonResponse
    {
        return $this->success($data, $message, Response::HTTP_ACCEPTED);
    }

    public function nonAuthoritativeInformation(mixed $data = null, string $message = ''): JsonResponse
    {
        return $this->success($data, $message, Response::HTTP_NON_AUTHORITATIVE_INFORMATION);
    }

    public function noContent(string $message = ''): JsonResponse
    {
        return $this->success(null, $message, Response::HTTP_NO_CONTENT);
    }

    public function resetContent(mixed $data = null, string $message = ''): JsonResponse
    {
        return $this->success($data, $message, Response::HTTP_RESET_CONTENT);
    }

    public function partialContent(mixed $data = null, string $message = ''): JsonResponse
    {
        return $this->success($data, $message, Response::HTTP_PARTIAL_CONTENT);
    }

    public function multiStatus(mixed $data = null, string $message = ''): JsonResponse
    {
        return $this->success($data, $message, Response::HTTP_MULTI_STATUS);
    }

    public function alreadyReported(mixed $data = null, string $message = ''): JsonResponse
    {
        return $this->success($data, $message, Response::HTTP_ALREADY_REPORTED);
    }

    public function imUsed(mixed $data = null, string $message = ''): JsonResponse
    {
        return $this->success($data, $message, Response::HTTP_IM_USED);
    }

    public function success(mixed $data = null, string $message = '', int $code = Response::HTTP_OK): JsonResponse
    {
        return $this->json(true, $code, $message, $data);
    }

    public function message(string $message, int $code = Response::HTTP_OK, mixed $data = null): JsonResponse
    {
        return $this->success($data, $message, $code);
    }

    public function failed(string $message = '', int $code = Response::HTTP_BAD_REQUEST, ?array $error = null): JsonResponse
    {
        return $this->json(false, $code, $message, null, $error);
    }

    public function error(string $message = '', int $code = Response::HTTP_BAD_REQUEST, ?array $error = null): JsonResponse
    {
        return $this->failed($message, $code, $error);
    }

    public function badRequest(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_BAD_REQUEST, $error);
    }

    public function unauthorized(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_UNAUTHORIZED, $error);
    }

    public function paymentRequired(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_PAYMENT_REQUIRED, $error);
    }

    public function forbidden(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_FORBIDDEN, $error);
    }

    public function notFound(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_NOT_FOUND, $error);
    }

    public function methodNotAllowed(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_METHOD_NOT_ALLOWED, $error);
    }

    public function notAcceptable(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_NOT_ACCEPTABLE, $error);
    }

    public function proxyAuthenticationRequired(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_PROXY_AUTHENTICATION_REQUIRED, $error);
    }

    public function requestTimeout(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_REQUEST_TIMEOUT, $error);
    }

    public function conflict(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_CONFLICT, $error);
    }

    public function gone(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_GONE, $error);
    }

    public function lengthRequired(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_LENGTH_REQUIRED, $error);
    }

    public function preconditionFailed(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_PRECONDITION_FAILED, $error);
    }

    public function requestEntityTooLarge(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_REQUEST_ENTITY_TOO_LARGE, $error);
    }

    public function requestUriTooLong(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_REQUEST_URI_TOO_LONG, $error);
    }

    public function unsupportedMediaType(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_UNSUPPORTED_MEDIA_TYPE, $error);
    }

    public function requestedRangeNotSatisfiable(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE, $error);
    }

    public function expectationFailed(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_EXPECTATION_FAILED, $error);
    }

    public function iAmATeapot(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_I_AM_A_TEAPOT, $error);
    }

    public function misdirectedRequest(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_MISDIRECTED_REQUEST, $error);
    }

    public function unprocessableEntity(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_UNPROCESSABLE_ENTITY, $error);
    }

    public function locked(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_LOCKED, $error);
    }

    public function failedDependency(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_FAILED_DEPENDENCY, $error);
    }

    public function tooEarly(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_TOO_EARLY, $error);
    }

    public function upgradeRequired(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_UPGRADE_REQUIRED, $error);
    }

    public function preconditionRequired(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_PRECONDITION_REQUIRED, $error);
    }

    public function tooManyRequests(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_TOO_MANY_REQUESTS, $error);
    }

    public function requestHeaderFieldsTooLarge(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE, $error);
    }

    public function unavailableForLegalReasons(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS, $error);
    }

    public function internalServerError(string $message = '', ?array $error = null): JsonResponse
    {
        return $this->failed($message, Response::HTTP_INTERNAL_SERVER_ERROR, $error);
    }

    public function debug(mixed $payload = null, string $message = '', int $code = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        if ($payload instanceof Throwable) {
            return $this->exception($payload);
        }

        $error = app()->hasDebugModeEnabled() ? [
            'debug' => $payload,
        ] : null;

        return $this->failed($message ?: 'Debug Failed', $code, $error);
    }

    public function exception(Throwable $throwable): JsonResponse
    {
        $structure = $this->newPipeline()
            ->send($throwable)
            ->through($this->exceptionPipes())
            ->then($this->exceptionDestination());

        return $this->failed(
            (string) ($structure['message'] ?? ''),
            (int) ($structure['code'] ?? Response::HTTP_INTERNAL_SERVER_ERROR),
            $structure['error'] ?? null
        )->withHeaders((array) ($structure['headers'] ?? []));
    }

    public function json(
        bool|int|string $status,
        int $code,
        string $message = '',
        mixed $data = null,
        ?array $error = null
    ): JsonResponse {
        return $this->newPipeline()
            ->send([
                'status' => $status,
                'code' => $code,
                'message' => $message,
                'data' => $data,
                'error' => $error,
            ])
            ->through($this->pipes())
            ->then($this->destination());
    }

    protected function newPipeline(): Pipeline
    {
        return new Pipeline(app());
    }

    protected function pipes(): array
    {
        return (array) config('felo-helper.api_response.pipes', []);
    }

    protected function exceptionPipes(): array
    {
        return (array) config('felo-helper.api_response.exception_pipes', []);
    }

    protected function exceptionDestination(): \Closure
    {
        return static function (Throwable $throwable): array {
            $code = $throwable->getCode();
            $errorCode = is_int($code) && $code >= 100 && $code <= 599
                ? $code
                : Response::HTTP_INTERNAL_SERVER_ERROR;

            $debug = app()->hasDebugModeEnabled();

            return [
                'code' => $errorCode,
                'message' => $debug ? $throwable->getMessage() : '',
                'error' => $debug ? [
                    'type' => $throwable::class,
                    'message' => $throwable->getMessage(),
                    'file' => $throwable->getFile(),
                    'line' => $throwable->getLine(),
                    'trace' => $throwable->getTrace(),
                ] : null,
                'headers' => [],
            ];
        };
    }

    protected function destination(): \Closure
    {
        return static function (array $structure): JsonResponse {
            $options = JSON_UNESCAPED_UNICODE;
            if (! $structure['status']) {
                $options |= JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES;
            }

            return new JsonResponse($structure, Response::HTTP_OK, [], $options);
        };
    }
}
