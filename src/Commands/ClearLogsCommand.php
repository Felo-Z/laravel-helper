<?php

declare(strict_types=1);

namespace FeloZ\LaravelHelper\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'felo:clear-logs')]
class ClearLogsCommand extends Command
{
    protected $signature = 'felo:clear-logs';

    protected $description = 'Clear log files from configured directories';

    public function handle()
    {
        clear_logs();

        $this->info('Log files cleared successfully.');

        return self::SUCCESS;
    }
}
