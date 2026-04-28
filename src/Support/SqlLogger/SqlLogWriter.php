<?php

namespace FeloZ\LaravelHelper\Support\SqlLogger;

use Illuminate\Filesystem\Filesystem;

class SqlLogWriter
{
    public function __construct(
        protected Filesystem $files,
        protected SqlLoggerConfig $config,
        protected FileNameResolver $fileNameResolver
    ) {}

    public function write(string $channel, SqlLogEntry $entry, string $content): void
    {
        $channelConfig = $this->config->channel($channel);
        $directory = $this->config->directory();

        $this->files->ensureDirectoryExists($directory);

        $path = $directory.DIRECTORY_SEPARATOR.$this->fileNameResolver->resolve(
            $channelConfig['file_name'],
            $channel,
            $entry
        );

        $flags = LOCK_EX;
        if ($channelConfig['append']) {
            $flags |= FILE_APPEND;
        }

        file_put_contents($path, $content, $flags);
    }
}
