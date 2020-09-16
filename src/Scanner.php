<?php

declare(strict_types=1);

namespace Avasil\ClamAv;

use Avasil\ClamAv\Driver\DriverFactory;
use Avasil\ClamAv\Driver\DriverInterface;
use Avasil\ClamAv\Exception\RuntimeException;

class Scanner implements ScannerInterface
{
    protected ?DriverInterface $driver;
    protected array $options = [
        'driver' => 'default',
    ];

    public function __construct(array $options = [])
    {
        $this->driver = null;
        $this->options = array_merge($this->options, $options);
    }

    public function ping(): bool
    {
        return $this->getDriver()->ping();
    }

    public function version(): string
    {
        return $this->getDriver()->version();
    }

    public function scan(string $path): ResultInterface
    {
        if (!is_readable($path)) {
            throw new RuntimeException(sprintf('"%s" does not exist or is not readable.', $path));
        }

        $real_path = realpath($path);

        return $this->parseResults(
            $path,
            $this->getDriver()->scan($real_path)
        );
    }

    public function scanBuffer(string $buffer): ResultInterface
    {
        return $this->parseResults(
            'buffer',
            $this->getDriver()->scanBuffer($buffer)
        );
    }

    public function scanResource(string $path): ResultInterface
    {
        if (!is_readable($path)) {
            throw new RuntimeException(sprintf('"%s" does not exist or is not readable.', $path));
        }

        return $this->parseResults(
            $path,
            $this->getDriver()->scanResource($path)
        );
    }

    public function getDriver(): DriverInterface
    {
        if (null === $this->driver) {
            $this->driver = DriverFactory::create($this->options);
        }

        return $this->driver;
    }

    public function setDriver(DriverInterface $driver): void
    {
        $this->driver = $driver;
    }

    protected function parseResults($path, array $infected): ResultInterface
    {
        $result = new Result($path);

        foreach ($infected as $line) {
            list($file, $virus) = explode(':', $line);
            $result->addInfected($file, preg_replace('/ FOUND$/', '', $virus));
        }

        return $result;
    }
}
