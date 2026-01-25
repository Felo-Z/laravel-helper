<?php

namespace Tests\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class HelperFunctionsTest extends TestCase
{
    public function test_clear_logs_function()
    {
        $logDir = storage_path('logs');

        if (! is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        file_put_contents($logDir.'/test1.log', 'test content 1');
        file_put_contents($logDir.'/test2.log', 'test content 2');
        file_put_contents($logDir.'/test3.json', 'test content 3');

        $this->assertFileExists($logDir.'/test1.log');
        $this->assertFileExists($logDir.'/test2.log');
        $this->assertFileExists($logDir.'/test3.json');

        clear_logs();

        $this->assertFileDoesNotExist($logDir.'/test1.log');
        $this->assertFileDoesNotExist($logDir.'/test2.log');
        $this->assertFileDoesNotExist($logDir.'/test3.json');
    }

    public function test_clear_logs_with_exclude_files()
    {
        Config::set('felo-helper.clear_logs', [
            'directories' => [storage_path('logs')],
            'extensions' => ['log', 'json'],
            'exclude' => ['important.log'],
        ]);

        $logDir = storage_path('logs');

        if (! is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        file_put_contents($logDir.'/important.log', 'important content');
        file_put_contents($logDir.'/normal.log', 'normal content');

        $this->assertFileExists($logDir.'/important.log');
        $this->assertFileExists($logDir.'/normal.log');

        clear_logs();

        $this->assertFileExists($logDir.'/important.log');
        $this->assertFileDoesNotExist($logDir.'/normal.log');

        @unlink($logDir.'/important.log');
    }

    public function test_clear_cache_function()
    {
        Cache::put('test_key', 'test_value');
        $this->assertEquals('test_value', Cache::get('test_key'));

        clear_cache();

        $this->assertNull(Cache::get('test_key'));
    }

    public function test_las_function()
    {
        $logDir = storage_path('logs');

        if (! is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $testData = ['key' => 'value', 'number' => 123];

        las($testData);

        $files = glob($logDir.'/*.json');
        $this->assertNotEmpty($files);

        $lastFile = end($files);
        $content = file_get_contents($lastFile);
        $decoded = json_decode($content, true);

        $this->assertEquals($testData, $decoded);

        @unlink($lastFile);
    }

    public function test_las_function_with_multiple_vars()
    {
        $logDir = storage_path('logs');

        if (! is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $testData1 = ['key1' => 'value1'];
        $testData2 = ['key2' => 'value2'];

        las($testData1, $testData2);

        $files = glob($logDir.'/*.json');
        $this->assertNotEmpty($files);

        $lastFile = end($files);
        $content = file_get_contents($lastFile);
        $decoded = json_decode($content, true);

        $this->assertEquals([$testData1, $testData2], $decoded);

        @unlink($lastFile);
    }

    public function test_zas_function_with_custom_filename()
    {
        $logDir = storage_path('logs');

        if (! is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $testData = ['key' => 'value', 'number' => 123];
        $fileName = 'custom-log';

        zas($fileName, $testData);

        $filePath = $logDir.'/'.$fileName.'.json';
        $this->assertFileExists($filePath);

        $content = file_get_contents($filePath);
        $decoded = json_decode($content, true);

        $this->assertEquals($testData, $decoded);

        @unlink($filePath);
    }

    public function test_zas_function_without_filename()
    {
        $logDir = storage_path('logs');

        if (! is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $testData = ['key' => 'value', 'number' => 123];

        zas(null, $testData);

        $files = glob($logDir.'/*.json');
        $this->assertNotEmpty($files);

        $lastFile = end($files);
        $content = file_get_contents($lastFile);
        $decoded = json_decode($content, true);

        $this->assertEquals($testData, $decoded);

        @unlink($lastFile);
    }

    public function test_zas_function_with_multiple_vars()
    {
        $logDir = storage_path('logs');

        if (! is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $testData1 = ['key1' => 'value1'];
        $testData2 = ['key2' => 'value2'];
        $fileName = 'multi-log';

        zas($fileName, $testData1, $testData2);

        $filePath = $logDir.'/'.$fileName.'.json';
        $this->assertFileExists($filePath);

        $content = file_get_contents($filePath);
        $decoded = json_decode($content, true);

        $this->assertEquals([$testData1, $testData2], $decoded);

        @unlink($filePath);
    }
}
