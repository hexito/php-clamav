<?php

declare(strict_types=1);

namespace Avasil\ClamAv\Driver;

use Avasil\ClamAv\Traits\GetOptionTrait;

abstract class AbstractDriver implements DriverInterface
{
    use GetOptionTrait;

    protected array $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }
}
