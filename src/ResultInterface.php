<?php

declare(strict_types=1);

namespace Avasil\ClamAv;

interface ResultInterface
{
    public function isClean(): bool;

    public function isInfected(): bool;

    public function getInfected(): array;

    public function getTarget(): string;
}
