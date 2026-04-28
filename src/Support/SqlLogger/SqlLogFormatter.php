<?php

namespace FeloZ\LaravelHelper\Support\SqlLogger;

class SqlLogFormatter
{
    public function __construct(
        protected SqlLoggerConfig $config
    ) {}

    public function format(SqlLogEntry $entry, string $channel): string
    {
        $bindings = json_encode($entry->bindings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[]';

        $replacements = [
            '{sequence}' => (string) $entry->sequence,
            '{datetime}' => $entry->executedAt->format($this->config->dateFormat()),
            '{channel}' => $channel,
            '{origin}' => $entry->origin,
            '{connection}' => $entry->connectionName,
            '{duration}' => $this->formatDuration($entry->durationMs),
            '{duration_ms}' => $this->stringifyFloat($entry->durationMs),
            '{sql}' => $entry->sql,
            '{raw_sql}' => $this->config->shouldIncludeRawSql() ? $entry->rawSql : '',
            '{bindings}' => $this->config->shouldIncludeBindings()
                ? $this->truncateBindings($bindings)
                : '',
            '{separator}' => $this->config->separator(),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $this->config->entryFormat());
    }

    protected function formatDuration(float $durationMs): string
    {
        return $this->stringifyFloat($durationMs).' ms';
    }

    protected function stringifyFloat(float $value): string
    {
        $formatted = number_format($value, 3, '.', '');

        return rtrim(rtrim($formatted, '0'), '.');
    }

    protected function truncateBindings(string $bindings): string
    {
        $maxLength = $this->config->maxBindingLength();
        if ($maxLength === null || mb_strlen($bindings) <= $maxLength) {
            return $bindings;
        }

        return rtrim(mb_substr($bindings, 0, $maxLength)).' ...[truncated]';
    }
}
