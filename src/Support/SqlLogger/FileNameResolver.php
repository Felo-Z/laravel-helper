<?php

namespace FeloZ\LaravelHelper\Support\SqlLogger;

use Illuminate\Support\Carbon;

class FileNameResolver
{
    public function resolve(string $template, string $channel, SqlLogEntry $entry): string
    {
        $resolved = preg_replace_callback('/\{date:([^}]+)\}/', static function (array $matches) use ($entry): string {
            return Carbon::instance($entry->executedAt)->format($matches[1]);
        }, $template) ?? $template;

        $replacements = [
            '{channel}' => $this->sanitizeSegment($channel),
            '{origin}' => $this->sanitizeSegment($entry->origin),
            '{connection}' => $this->sanitizeSegment($entry->connectionName),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $resolved);
    }

    protected function sanitizeSegment(string $value): string
    {
        $sanitized = preg_replace('/[^A-Za-z0-9._-]+/', '-', $value) ?? $value;

        return trim($sanitized, '-');
    }
}
