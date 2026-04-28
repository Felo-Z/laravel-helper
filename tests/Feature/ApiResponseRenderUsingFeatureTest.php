<?php

namespace Tests\Feature;

use Exception;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiResponseRenderUsingFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/api/__test_exception__', static function () {
            throw new Exception('feature boom');
        });
    }

    public function test_render_using_converts_exception_to_json_response_for_api_request(): void
    {
        config(['app.debug' => true]);

        $response = $this
            ->withHeader('Accept', 'application/json')
            ->getJson('/api/__test_exception__');

        $response
            ->assertStatus(500)
            ->assertJsonPath('status', false)
            ->assertJsonPath('message', 'feature boom');
    }
}
