<?php

declare(strict_types=1);

namespace Avasil\ClamAv\Driver;

interface DriverInterface
{
    public function scan(string $path): array;

    public function scanBuffer(string $buffer): array;

    public function scanResource(string $path): array;

    public function ping(): bool;

    public function version(): string;
}
