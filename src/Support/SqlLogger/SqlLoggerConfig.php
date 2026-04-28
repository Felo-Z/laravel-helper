<?php

namespace FeloZ\LaravelHelper\Support\SqlLogger;

use Illuminate\Contracts\Config\Repository;

class SqlLoggerConfig
{
    public function __construct(
        protected Repository $config
    ) {}

    public function isEnabled(): bool
    {
        return (bool) $this->config->get('felo-helper.sql_logger.enabled', false);
    }

    public function directory(): string
    {
        return rtrim((string) $this->config->get('felo-helper.sql_logger.directory', storage_path('logs/sql')), DIRECTORY_SEPARATOR);
    }

    public function shouldReplaceBindings(): bool
    {
        return (bool) $this->config->get('felo-helper.sql_logger.replace_bindings', true);
    }

    public function shouldCollapseWhitespace(): bool
    {
        return (bool) $this->config->get('felo-helper.sql_logger.collapse_whitespace', true);
    }

    public function ignoredConnections(): array
    {
        return $this->normalizeStringList(
            $this->config->get('felo-helper.sql_logger.ignore_connections', [])
        );
    }

    public function excludedPatterns(): array
    {
        $patterns = $this->normalizeStringList(
            $this->config->get('felo-helper.sql_logger.exclude_patterns', [])
        );

        return array_map($this->normalizePattern(...), $patterns);
    }

    public function onlyMethods(): array
    {
        return array_map('strtoupper', $this->normalizeStringList(
            $this->config->get('felo-helper.sql_logger.only_methods', [])
        ));
    }

    public function excludedMethods(): array
    {
        return array_map('strtoupper', $this->normalizeStringList(
            $this->config->get('felo-helper.sql_logger.exclude_methods', [])
        ));
    }

    public function shouldIncludeRawSql(): bool
    {
        return (bool) $this->config->get('felo-helper.sql_logger.include_raw_sql', true);
    }

    public function shouldIncludeBindings(): bool
    {
        return (bool) $this->config->get('felo-helper.sql_logger.include_bindings', true);
    }

    public function maxQueryLength(): ?int
    {
        $length = (int) $this->config->get('felo-helper.sql_logger.max_query_length', 0);

        return $length > 0 ? $length : null;
    }

    public function maxBindingLength(): ?int
    {
        $length = (int) $this->config->get('felo-helper.sql_logger.max_binding_length', 0);

        return $length > 0 ? $length : null;
    }

    public function shouldGroupByScope(): bool
    {
        return (bool) $this->config->get('felo-helper.sql_logger.group_by_scope', false);
    }

    public function scopeHeaderFormat(): string
    {
        return (string) $this->config->get(
            'felo-helper.sql_logger.scope_header_format',
            "================ SCOPE START ================\n".
            "scope: {scope_type}\n".
            "id: {scope_id}\n".
            "name: {scope_name}\n".
            "time: {scope_started_at}\n".
            "{scope_context}\n"
        );
    }

    public function dateFormat(): string
    {
        return (string) $this->config->get('felo-helper.sql_logger.date_format', 'Y-m-d H:i:s.u');
    }

    public function entryFormat(): string
    {
        return (string) $this->config->get(
            'felo-helper.sql_logger.entry_format',
            "[{datetime}] [{channel}] [{origin}] [{connection}] [{duration}]\n{sql}\n{separator}\n"
        );
    }

    public function separator(): string
    {
        return '/*'.str_repeat('=', 50).'*/';
    }

    public function channel(string $channel): array
    {
        $config = (array) $this->config->get("felo-helper.sql_logger.channels.{$channel}", []);

        return [
            'enabled' => (bool) ($config['enabled'] ?? false),
            'pattern' => $this->normalizePattern($config['pattern'] ?? '/.+/s'),
            'file_name' => (string) ($config['file_name'] ?? "{date:Y-m-d}-{$channel}.sql"),
            'append' => (bool) ($config['append'] ?? true),
            'threshold_ms' => (float) ($config['threshold_ms'] ?? 0.0),
        ];
    }

    public function channels(): array
    {
        return [
            'all' => $this->channel('all'),
            'slow' => $this->channel('slow'),
        ];
    }

    protected function normalizePattern(string $pattern): string
    {
        if (@preg_match($pattern, '') === false) {
            return '/.+/s';
        }

        return $pattern;
    }

    protected function normalizeStringList(mixed $value): array
    {
        if (is_string($value)) {
            $value = array_map('trim', explode(',', $value));
        }

        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map(static fn (mixed $item): string => trim((string) $item), $value)));
    }
}
