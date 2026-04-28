<?php

namespace FeloZ\LaravelHelper\Support\SqlLogger;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Queue\Jobs\Job;
use Throwable;

class QueryLogger
{
    protected int $sequence = 0;

    protected array $writtenScopeHeaders = [];

    public function __construct(
        protected Application $app,
        protected SqlLoggerConfig $config,
        protected BindingInterpolator $bindingInterpolator,
        protected SqlLogFormatter $formatter,
        protected SqlLogWriter $writer
    ) {}

    public function handle(QueryExecuted $event): void
    {
        if (! $this->config->isEnabled()) {
            return;
        }

        if ($this->shouldIgnoreEvent($event)) {
            return;
        }

        try {
            $entry = $this->makeEntry($event);
            $scope = $this->currentScope();

            foreach ($this->channelsFor($entry) as $channel) {
                $content = $this->scopeHeaderFor($channel, $scope).$this->formatter->format($entry, $channel);
                $this->writer->write($channel, $entry, $content);
            }
        } catch (Throwable $throwable) {
            $this->reportFailure($throwable);
        }
    }

    protected function shouldIgnoreEvent(QueryExecuted $event): bool
    {
        if ($this->shouldIgnoreRequestMethod()) {
            return true;
        }

        if (in_array($event->connectionName, $this->config->ignoredConnections(), true)) {
            return true;
        }

        foreach ($this->config->excludedPatterns() as $pattern) {
            if (preg_match($pattern, $event->sql)) {
                return true;
            }
        }

        return false;
    }

    protected function shouldIgnoreRequestMethod(): bool
    {
        $request = $this->currentRequest();
        if (! $request instanceof Request || ! $request->server('REQUEST_METHOD')) {
            return false;
        }

        $method = strtoupper($request->method());
        $onlyMethods = $this->config->onlyMethods();
        if ($onlyMethods !== [] && ! in_array($method, $onlyMethods, true)) {
            return true;
        }

        $excludedMethods = $this->config->excludedMethods();

        return in_array($method, $excludedMethods, true);
    }

    protected function makeEntry(QueryExecuted $event): SqlLogEntry
    {
        $this->sequence++;

        $sourceSql = $this->normalizeSqlForMatching($event->sql);
        $rawSql = $this->truncateSql($sourceSql);
        $sql = $this->config->shouldReplaceBindings()
            ? $this->normalizeSql($this->bindingInterpolator->interpolate($event))
            : $rawSql;

        return new SqlLogEntry(
            sequence: $this->sequence,
            connectionName: $event->connectionName,
            origin: $this->detectOrigin(),
            sourceSql: $sourceSql,
            rawSql: $rawSql,
            sql: $sql,
            bindings: $this->bindingInterpolator->normalizeBindings($event),
            durationMs: (float) $event->time,
            executedAt: CarbonImmutable::now()
        );
    }

    protected function channelsFor(SqlLogEntry $entry): array
    {
        $channels = [];

        foreach ($this->config->channels() as $channel => $channelConfig) {
            if (! $channelConfig['enabled']) {
                continue;
            }

            if (! preg_match($channelConfig['pattern'], $entry->sourceSql)) {
                continue;
            }

            if ($channel === 'slow' && $entry->durationMs < $channelConfig['threshold_ms']) {
                continue;
            }

            $channels[] = $channel;
        }

        return $channels;
    }

    protected function detectOrigin(): string
    {
        $request = $this->currentRequest();
        if ($request instanceof Request && $request->server('REQUEST_METHOD')) {
            return sprintf('http: %s %s', $request->method(), $request->fullUrl());
        }

        if ($this->app->runningInConsole()) {
            $argv = $_SERVER['argv'] ?? [];
            $command = is_array($argv) ? implode(' ', $argv) : (string) $argv;

            return 'console: '.trim($command);
        }

        return 'unknown';
    }

    protected function normalizeSql(string $sql): string
    {
        return $this->truncateSql($this->normalizeSqlForMatching($sql));
    }

    protected function normalizeSqlForMatching(string $sql): string
    {
        if (! $this->config->shouldCollapseWhitespace()) {
            return $sql;
        }

        return trim((string) preg_replace('/\s+/u', ' ', $sql));
    }

    protected function truncateSql(string $sql): string
    {
        $maxLength = $this->config->maxQueryLength();
        if ($maxLength === null || mb_strlen($sql) <= $maxLength) {
            return $sql;
        }

        return rtrim(mb_substr($sql, 0, $maxLength)).' ...[truncated]';
    }

    protected function currentRequest(): ?Request
    {
        if (! $this->app->bound('request')) {
            return null;
        }

        $request = $this->app->make('request');

        return $request instanceof Request ? $request : null;
    }

    protected function currentScope(): ExecutionScope
    {
        if ($request = $this->currentRequest()) {
            if ($request->server('REQUEST_METHOD')) {
                $scopeId = $request->headers->get('X-Request-Id')
                    ?: 'http:'.spl_object_id($request);

                return new ExecutionScope(
                    key: $scopeId,
                    type: 'http',
                    name: sprintf('%s %s', $request->method(), $request->fullUrl()),
                    context: [
                        'method' => $request->method(),
                        'url' => $request->fullUrl(),
                    ],
                    startedAt: CarbonImmutable::now()
                );
            }
        }

        if ($this->app->bound('queue.job')) {
            $job = $this->app->make('queue.job');

            if ($job instanceof Job) {
                $scopeId = method_exists($job, 'uuid') && filled($job->uuid())
                    ? 'job:'.$job->uuid()
                    : 'job:'.spl_object_id($job);

                return new ExecutionScope(
                    key: $scopeId,
                    type: 'job',
                    name: $job->resolveName(),
                    context: [
                        'job' => $job->resolveName(),
                        'queue' => method_exists($job, 'getQueue') ? $job->getQueue() : null,
                        'attempts' => method_exists($job, 'attempts') ? $job->attempts() : null,
                    ],
                    startedAt: CarbonImmutable::now()
                );
            }
        }

        if ($this->app->runningInConsole()) {
            $argv = $_SERVER['argv'] ?? [];
            $command = trim(is_array($argv) ? implode(' ', $argv) : (string) $argv);
            $scopeId = 'command:'.getmypid().':'.md5($command);

            return new ExecutionScope(
                key: $scopeId,
                type: 'command',
                name: $command !== '' ? $command : 'artisan',
                context: [
                    'command' => $command !== '' ? $command : 'artisan',
                    'pid' => getmypid(),
                ],
                startedAt: CarbonImmutable::now()
            );
        }

        return new ExecutionScope(
            key: 'unknown:'.getmypid(),
            type: 'unknown',
            name: 'unknown',
            context: [],
            startedAt: CarbonImmutable::now()
        );
    }

    protected function scopeHeaderFor(string $channel, ExecutionScope $scope): string
    {
        if (! $this->config->shouldGroupByScope()) {
            return '';
        }

        if (($this->writtenScopeHeaders[$channel] ?? null) === $scope->key) {
            return '';
        }

        $this->writtenScopeHeaders[$channel] = $scope->key;

        return $this->formatScopeHeader($scope);
    }

    protected function formatScopeHeader(ExecutionScope $scope): string
    {
        $contextLines = [];
        foreach ($scope->context as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $contextLines[] = sprintf('%s: %s', $key, $value);
        }

        $context = implode(PHP_EOL, $contextLines);
        if ($context !== '') {
            $context .= PHP_EOL;
        }

        $replacements = [
            '{scope_type}' => $scope->type,
            '{scope_id}' => $scope->key,
            '{scope_name}' => $scope->name,
            '{scope_started_at}' => $scope->startedAt->format($this->config->dateFormat()),
            '{scope_context}' => $context,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $this->config->scopeHeaderFormat());
    }

    protected function reportFailure(Throwable $throwable): void
    {
        if (! $this->app->bound('log')) {
            return;
        }

        $this->app->make('log')->warning('SQL logger failed to record query.', [
            'exception' => $throwable,
        ]);
    }
}
