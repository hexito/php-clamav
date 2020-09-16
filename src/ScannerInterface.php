<?php

declare(strict_types=1);

namespace Avasil\ClamAv;

interface ScannerInterface
{
    public function scan(string $path): ResultInterface;

    public function scanBuffer(string $buffer): ResultInterface;

    public function scanResource(string $path): ResultInterface;

    public function ping(): bool;

    public function version(): string;
}
