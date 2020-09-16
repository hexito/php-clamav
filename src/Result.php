<?php

declare(strict_types=1);

namespace Avasil\ClamAv;

class Result implements ResultInterface
{
    private string $target;
    private array $infected;

    public function __construct(string $target = '', array $infected = [])
    {
        $this->target = $target;
        $this->infected = $infected;
    }

    public function isClean(): bool
    {
        return !$this->isInfected();
    }

    public function isInfected(): bool
    {
        return 0 < count($this->infected);
    }

    public function getInfected(): array
    {
        return $this->infected;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function addInfected(string $file, string $virus): void
    {
        $this->infected[$file] = $virus;
    }

    public function setInfected(array $infected = []): void
    {
        $this->infected = $infected;
    }

    public function __toString(): string
    {
        $str = [];

        foreach ($this->infected as $k => $v) {
            $str[] = $k.': '.$v;
        }

        return implode(PHP_EOL, $str);
    }
}
