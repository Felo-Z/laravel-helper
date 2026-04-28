<?php

namespace FeloZ\LaravelHelper\Support\SqlLogger;

use Carbon\CarbonImmutable;

class SqlLogEntry
{
    public function __construct(
        public readonly int $sequence,
        public readonly string $connectionName,
        public readonly string $origin,
        public readonly string $sourceSql,
        public readonly string $rawSql,
        public readonly string $sql,
        public readonly array $bindings,
        public readonly float $durationMs,
        public readonly CarbonImmutable $executedAt
    ) {}
}
