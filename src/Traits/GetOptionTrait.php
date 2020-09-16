<?php

declare(strict_types=1);

namespace Avasil\ClamAv\Traits;

trait GetOptionTrait
{
    protected function getOption(string $key, $default = null)
    {
        return !empty($this->options[$key]) ? $this->options[$key] : $default;
    }
}
