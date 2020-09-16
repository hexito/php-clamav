<?php

declare(strict_types=1);

namespace Avasil\ClamAv\Driver;

use Avasil\ClamAv\Exception\ConfigurationException;

class DriverFactory
{
    const DRIVERS = [
        'clamscan' => ClamscanDriver::class,
        'clamd_local' => ClamdDriver::class,
        'clamd_remote' => ClamdRemoteDriver::class,
        'default' => ClamscanDriver::class,
    ];

    public static function create(array $config): AbstractDriver
    {
        if (empty($config['driver'])) {
            throw new ConfigurationException('ClamAV driver required, please check your config.');
        }

        if (!array_key_exists($config['driver'], static::DRIVERS)) {
            throw new ConfigurationException(sprintf('Invalid driver "%s" specified. Available options are: %s', $config['driver'], join(', ', array_keys(static::DRIVERS))));
        }

        $driver = static::DRIVERS[$config['driver']];

        return new $driver($config);
    }
}
