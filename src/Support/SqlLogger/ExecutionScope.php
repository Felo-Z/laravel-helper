<?php

namespace FeloZ\LaravelHelper\Support\SqlLogger;

use Carbon\CarbonImmutable;

class ExecutionScope
{
    public function __construct(
        public readonly string $key,
        public readonly string $type,
        public readonly string $name,
        public readonly array $context,
        public readonly CarbonImmutable $startedAt
    ) {}
}
