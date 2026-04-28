<?php

namespace Tests\Helpers;

use Exception;
use FeloZ\LaravelHelper\Facades\Ap;
use FeloZ\LaravelHelper\Support\ApiResponse;
use FeloZ\LaravelHelper\Support\RenderUsings\ShouldReturnJsonRenderUsing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ApiResponseHelperTest extends TestCase
{
    public function test_ok_response(): void
    {
        $response = ap()->ok(['id' => 1], 'done');

        $this->assertSame(200, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertTrue($data['status']);
        $this->assertSame(200, $data['code']);
        $this->assertSame('done', $data['message']);
        $this->assertSame(['id' => 1], $data['data']);
    }

    public function test_message_response(): void
    {
        $response = ap()->message('created', 201, ['name' => 'demo']);
        $data = $response->getData(true);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertTrue($data['status']);
        $this->assertSame('created', $data['message']);
    }

    public function test_created_response_with_location_header(): void
    {
        $response = ap()->created(['id' => 1], 'created', '/api/users/1');
        $data = $response->getData(true);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertTrue($data['status']);
        $this->assertSame('created', $data['message']);
        $this->assertSame('/api/users/1', $response->headers->get('Location'));
    }

    public function test_accepted_and_no_content_responses(): void
    {
        $accepted = ap()->accepted(['job' => 'queued'], 'accepted');
        $acceptedData = $accepted->getData(true);
        $this->assertSame(202, $accepted->getStatusCode());
        $this->assertTrue($acceptedData['status']);

        $noContent = ap()->noContent();
        $noContentData = $noContent->getData(true);
        $this->assertSame(204, $noContent->getStatusCode());
        $this->assertNull($noContentData['data']);
    }

    public function test_failed_response(): void
    {
        Config::set('app.debug', true);

        $response = ap()->failed('bad request', 400, ['field' => 'name']);
        $data = $response->getData(true);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertFalse($data['status']);
        $this->assertSame('bad request', $data['message']);
        $this->assertSame(['field' => 'name'], $data['error']);
    }

    public function test_error_is_alias_of_failed(): void
    {
        $response = ap()->error('bad', 400, ['k' => 'v']);
        $data = $response->getData(true);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertFalse($data['status']);
        $this->assertSame('bad', $data['message']);
    }

    public function test_common_error_shortcuts(): void
    {
        Config::set('app.debug', true);

        $unauthorized = ap()->unauthorized('unauthorized');
        $forbidden = ap()->forbidden('forbidden');
        $notFound = ap()->notFound('not found');
        $unprocessable = ap()->unprocessableEntity('invalid');
        $internal = ap()->internalServerError('server error');

        $this->assertSame(401, $unauthorized->getStatusCode());
        $this->assertSame(403, $forbidden->getStatusCode());
        $this->assertSame(404, $notFound->getStatusCode());
        $this->assertSame(422, $unprocessable->getStatusCode());
        $this->assertSame(500, $internal->getStatusCode());
    }

    public function test_all_http_shortcuts_status_codes(): void
    {
        Config::set('app.debug', true);

        $successMethods = [
            'ok' => 200,
            'created' => 201,
            'accepted' => 202,
            'nonAuthoritativeInformation' => 203,
            'noContent' => 204,
            'resetContent' => 205,
            'partialContent' => 206,
            'multiStatus' => 207,
            'alreadyReported' => 208,
            'imUsed' => 226,
        ];

        foreach ($successMethods as $method => $code) {
            $response = $method === 'noContent'
                ? ap()->{$method}()
                : ap()->{$method}(['demo' => true], $method);
            $this->assertSame($code, $response->getStatusCode(), "Failed success shortcut {$method}");
        }

        $errorMethods = [
            'badRequest' => 400,
            'unauthorized' => 401,
            'paymentRequired' => 402,
            'forbidden' => 403,
            'notFound' => 404,
            'methodNotAllowed' => 405,
            'notAcceptable' => 406,
            'proxyAuthenticationRequired' => 407,
            'requestTimeout' => 408,
            'conflict' => 409,
            'gone' => 410,
            'lengthRequired' => 411,
            'preconditionFailed' => 412,
            'requestEntityTooLarge' => 413,
            'requestUriTooLong' => 414,
            'unsupportedMediaType' => 415,
            'requestedRangeNotSatisfiable' => 416,
            'expectationFailed' => 417,
            'iAmATeapot' => 418,
            'misdirectedRequest' => 421,
            'unprocessableEntity' => 422,
            'locked' => 423,
            'failedDependency' => 424,
            'tooEarly' => 425,
            'upgradeRequired' => 426,
            'preconditionRequired' => 428,
            'tooManyRequests' => 429,
            'requestHeaderFieldsTooLarge' => 431,
            'unavailableForLegalReasons' => 451,
            'internalServerError' => 500,
        ];

        foreach ($errorMethods as $method => $code) {
            $response = ap()->{$method}($method);
            $this->assertSame($code, $response->getStatusCode(), "Failed error shortcut {$method}");
        }
    }

    public function test_debug_response_hides_error_when_not_debug(): void
    {
        Config::set('app.debug', false);

        $response = ap()->debug(['x' => 1], 'debug message');
        $data = $response->getData(true);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertFalse($data['status']);
        $this->assertArrayNotHasKey('error', $data);
    }

    public function test_exception_response_from_http_exception_pipe(): void
    {
        Config::set('app.debug', true);

        $response = ap()->exception(new HttpException(404, 'not found', null, ['X-Test' => 'ok']));
        $data = $response->getData(true);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertFalse($data['status']);
        $this->assertSame('not found', $data['message']);
        $this->assertSame('ok', $response->headers->get('X-Test'));
    }

    public function test_debug_with_throwable_uses_exception_flow(): void
    {
        Config::set('app.debug', true);

        $response = ap()->debug(new Exception('boom'));
        $data = $response->getData(true);

        $this->assertSame(500, $response->getStatusCode());
        $this->assertFalse($data['status']);
        $this->assertSame('boom', $data['message']);
        $this->assertIsArray($data['error']);
        $this->assertSame(Exception::class, $data['error']['type']);
    }

    public function test_facade_call(): void
    {
        $response = Ap::ok(['id' => 2], 'ok');
        $data = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($data['status']);
        $this->assertSame(['id' => 2], $data['data']);
    }

    public function test_render_using_for_json_request(): void
    {
        Config::set('app.debug', true);
        $renderUsing = new ShouldReturnJsonRenderUsing;
        $request = Request::create('/api/users', 'GET');
        $request->headers->set('Accept', 'application/json');

        $response = $renderUsing(new Exception('json-fail'), $request);

        $this->assertNotNull($response);
        $this->assertSame(500, $response->getStatusCode());
    }

    public function test_render_using_skips_non_json_request(): void
    {
        $renderUsing = new ShouldReturnJsonRenderUsing;
        $request = Request::create('/web/page', 'GET');
        $request->headers->set('Accept', 'text/html');

        $response = $renderUsing(new Exception('html-fail'), $request);

        $this->assertNull($response);
    }

    public function test_project_can_extend_api_response_by_macro(): void
    {
        ApiResponse::macro('userNotFound', function () {
            /** @var ApiResponse $this */
            return $this->failed('用户不存在', 200404, ['type' => 'biz_error']);
        });

        $response = ap()->__call('userNotFound', []);
        $data = $response->getData(true);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertFalse($data['status']);
        $this->assertSame(200404, $data['code']);
        $this->assertSame('用户不存在', $data['message']);
    }

    public function test_business_code_in_legacy_strategy_maps_to_500(): void
    {
        Config::set('felo-helper.api_response.status_code_strategy', 'legacy');
        $response = ap()->failed('biz fail', 200404);

        $this->assertSame(500, $response->getStatusCode());
    }
}
