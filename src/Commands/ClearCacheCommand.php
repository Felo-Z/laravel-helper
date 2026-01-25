<?php

declare(strict_types=1);

namespace FeloZ\LaravelHelper\Commands;

use Illuminate\Console\Command;

class ClearCacheCommand extends Command
{
    protected $signature = 'felo:clear-cache';

    protected $description = 'Clear cache and Redis databases';

    public function handle()
    {
        clear_cache();

        $this->info('Cache cleared successfully.');

        return self::SUCCESS;
    }
}
