<?php

namespace FeloZ\LaravelHelper\Support\SqlLogger;

use DateTimeInterface;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Str;

class BindingInterpolator
{
    public function interpolate(QueryExecuted $event): string
    {
        $sql = $event->sql;
        $bindings = $event->connection->prepareBindings($event->bindings);
        $positionalBindings = [];
        $namedBindings = [];

        foreach ($bindings as $key => $binding) {
            if (is_string($key)) {
                $parameter = Str::startsWith($key, ':') ? $key : ':'.$key;
                $namedBindings[$parameter] = $this->stringifyBinding($binding);

                continue;
            }

            $positionalBindings[] = $this->stringifyBinding($binding);
        }

        return $this->replacePlaceholders($sql, $positionalBindings, $namedBindings);
    }

    public function normalizeBindings(QueryExecuted $event): array
    {
        $bindings = $event->connection->prepareBindings($event->bindings);

        return array_map(fn (mixed $binding): mixed => $this->normalizeBinding($binding), $bindings);
    }

    protected function normalizeBinding(mixed $binding): mixed
    {
        if ($binding instanceof DateTimeInterface) {
            return $binding->format('Y-m-d H:i:s');
        }

        if (is_resource($binding)) {
            return '[resource]';
        }

        if (is_string($binding) && ! mb_check_encoding($binding, 'UTF-8')) {
            return '[binary-string]';
        }

        if (is_array($binding) || is_object($binding)) {
            $encoded = json_encode($binding, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return $encoded === false ? '[unserializable]' : $encoded;
        }

        return $binding;
    }

    protected function stringifyBinding(mixed $binding): string
    {
        $binding = $this->normalizeBinding($binding);

        return match (true) {
            $binding === null => 'null',
            is_bool($binding) => $binding ? '1' : '0',
            is_int($binding), is_float($binding) => (string) $binding,
            default => "'".$this->escapeString((string) $binding)."'",
        };
    }

    protected function escapeString(string $value): string
    {
        return str_replace("'", "''", $value);
    }

    protected function replacePlaceholders(string $sql, array $positionalBindings, array $namedBindings): string
    {
        $result = '';
        $length = strlen($sql);
        $state = null;
        $position = 0;

        for ($index = 0; $index < $length; $index++) {
            $char = $sql[$index];
            $next = $index + 1 < $length ? $sql[$index + 1] : null;

            if ($state !== null) {
                $result .= $char;

                if ($char === '\\' && $next !== null) {
                    $result .= $next;
                    $index++;

                    continue;
                }

                if ($char === $state) {
                    if ($next === $state) {
                        $result .= $next;
                        $index++;

                        continue;
                    }

                    $state = null;
                }

                continue;
            }

            if ($char === "'" || $char === '"') {
                $state = $char;
                $result .= $char;

                continue;
            }

            if ($char === '?') {
                $result .= array_shift($positionalBindings) ?? '?';

                continue;
            }

            if ($char === ':') {
                $parameter = $this->consumeNamedParameter($sql, $index);

                if ($parameter !== null && array_key_exists($parameter, $namedBindings)) {
                    $result .= $namedBindings[$parameter];
                    $index += strlen($parameter) - 1;

                    continue;
                }
            }

            $result .= $char;
        }

        return $result;
    }

    protected function consumeNamedParameter(string $sql, int $offset): ?string
    {
        if (! preg_match('/^:\w+/', substr($sql, $offset), $matches)) {
            return null;
        }

        return $matches[0];
    }
}
