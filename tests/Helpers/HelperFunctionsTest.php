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

    // -------------------------------------------------------------------------
    // Property 1: clear_logs 按配置精确清理文件
    // -------------------------------------------------------------------------

    /**
     * DataProvider for property 1: generates ≥ 100 deterministic test cases.
     *
     * Each case is: [extensions[], exclude[], toDelete[], toKeep[]]
     * - toDelete: files that SHOULD be removed (ext matches, not excluded)
     * - toKeep:   files that SHOULD remain  (ext doesn't match OR filename excluded)
     */
    public static function clearLogsConfigProvider(): array
    {
        // Predefined base scenarios covering edge cases
        $base = [
            // [extensions, exclude, toDelete, toKeep]
            [['log'], [], ['app.log', 'error.log'], ['app.txt', 'data.json']],
            [['log', 'json'], [], ['app.log', 'data.json'], ['app.txt', 'image.png']],
            [['log'], ['important.log'], ['error.log'], ['important.log', 'app.txt']],
            [['log', 'json'], ['keep.json'], ['app.log', 'data.json'], ['keep.json', 'app.txt']],
            [['sql'], [], ['query.sql'], ['app.log', 'data.json']],
            [['log'], ['a.log', 'b.log'], ['c.log'], ['a.log', 'b.log', 'app.txt']],
            [['txt'], [], ['readme.txt'], ['app.log', 'data.json']],
            [['log', 'txt', 'sql'], [], ['a.log', 'b.txt', 'c.sql'], ['d.json', 'e.png']],
            [['log'], ['only.log'], [], ['only.log', 'app.txt']],
            [['log', 'json'], ['x.log', 'y.json'], ['z.log', 'w.json'], ['x.log', 'y.json']],
        ];

        $cases = [];

        $extPool = ['log', 'json', 'sql', 'txt', 'csv', 'xml'];
        $prefixes = ['app', 'error', 'debug', 'query', 'data', 'cache', 'system', 'access', 'audit', 'trace'];

        foreach ($base as $idx => $scenario) {
            $cases["base_{$idx}"] = $scenario;
        }

        // Generate 95 deterministic variants (total ≥ 105 cases)
        $seed = 42;
        for ($i = 0; $i < 95; $i++) {
            // Deterministic LCG pseudo-random
            $seed = ($seed * 1103515245 + 12345) & 0x7fffffff;
            $extCount = ($seed % 3) + 1;
            $seed = ($seed * 1103515245 + 12345) & 0x7fffffff;
            $excludeCount = $seed % 3;

            $extensions = [];
            for ($e = 0; $e < $extCount; $e++) {
                $seed = ($seed * 1103515245 + 12345) & 0x7fffffff;
                $extensions[] = $extPool[$seed % count($extPool)];
            }
            $extensions = array_values(array_unique($extensions));

            // Pick a non-matching extension for "keep" files
            $nonMatchExt = 'png';
            foreach ($extPool as $candidate) {
                if (! in_array($candidate, $extensions)) {
                    $nonMatchExt = $candidate;
                    break;
                }
            }

            $toDelete = [];
            $toKeep = [];
            $exclude = [];

            // Files to delete: matching ext, not excluded
            $seed = ($seed * 1103515245 + 12345) & 0x7fffffff;
            $deleteCount = ($seed % 3) + 1;
            for ($d = 0; $d < $deleteCount; $d++) {
                $seed = ($seed * 1103515245 + 12345) & 0x7fffffff;
                $prefix = $prefixes[$seed % count($prefixes)];
                $ext = $extensions[$seed % count($extensions)];
                $toDelete[] = "{$prefix}-del{$d}-{$i}.{$ext}";
            }

            // Files to keep via exclusion
            for ($x = 0; $x < $excludeCount; $x++) {
                $seed = ($seed * 1103515245 + 12345) & 0x7fffffff;
                $prefix = $prefixes[$seed % count($prefixes)];
                $ext = $extensions[$seed % count($extensions)];
                $name = "{$prefix}-excl{$x}-{$i}.{$ext}";
                $exclude[] = $name;
                $toKeep[] = $name;
            }

            // Files to keep via non-matching extension
            $seed = ($seed * 1103515245 + 12345) & 0x7fffffff;
            $prefix = $prefixes[$seed % count($prefixes)];
            $toKeep[] = "{$prefix}-keep-{$i}.{$nonMatchExt}";

            $cases["gen_{$i}"] = [$extensions, $exclude, $toDelete, $toKeep];
        }

        return $cases;
    }

    /**
     * **Validates: Requirements 4.1**
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('clearLogsConfigProvider')]
    public function test_clear_logs_respects_config_property(
        array $extensions,
        array $exclude,
        array $toDelete,
        array $toKeep
    ): void {
        // Feature: laravel-13-upgrade, Property 1: clear_logs 按配置精确清理文件

        // Create an isolated temp directory for this test run
        $tmpDir = sys_get_temp_dir().'/felo-helper-prop1-'.uniqid('', true);
        mkdir($tmpDir, 0755, true);

        try {
            // Create all test files
            foreach (array_merge($toDelete, $toKeep) as $filename) {
                file_put_contents($tmpDir.'/'.$filename, 'content');
            }

            // Configure clear_logs to use our temp directory
            Config::set('felo-helper.clear_logs', [
                'directories' => [$tmpDir],
                'extensions' => $extensions,
                'exclude' => $exclude,
                'recursive' => false,
            ]);

            clear_logs();

            // Files that should have been deleted
            foreach ($toDelete as $filename) {
                $this->assertFileDoesNotExist(
                    $tmpDir.'/'.$filename,
                    "File '{$filename}' should have been deleted (ext matches, not excluded)"
                );
            }

            // Files that should have been kept
            foreach ($toKeep as $filename) {
                $this->assertFileExists(
                    $tmpDir.'/'.$filename,
                    "File '{$filename}' should have been kept (ext doesn't match or is excluded)"
                );
            }
        } finally {
            // Clean up temp directory
            foreach (scandir($tmpDir) as $entry) {
                if ($entry !== '.' && $entry !== '..') {
                    @unlink($tmpDir.'/'.$entry);
                }
            }
            @rmdir($tmpDir);
        }
    }
}
