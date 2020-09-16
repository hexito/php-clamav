<?php

declare(strict_types=1);

namespace Avasil\ClamAv\Driver;

use Avasil\ClamAv\Exception\RuntimeException;
use Avasil\ClamAv\Socket\SocketFactory;
use Avasil\ClamAv\Socket\SocketInterface;

class ClamdDriver extends AbstractDriver
{
    public const HOST = '127.0.0.1';
    public const PORT = 3310;
    public const SOCKET_PATH = '/var/run/clamav/clamd.ctl';
    public const COMMAND = "n%s\n";
    private ?SocketInterface $socket;

    public function __construct()
    {
        parent::__construct();
        $this->socket = null;
    }

    public function ping(): bool
    {
        $this->sendCommand('PING');

        return 'PONG' === trim($this->getResponse());
    }

    public function version(): string
    {
        $this->sendCommand('VERSION');

        return trim($this->getResponse());
    }

    public function scan(string $path): array
    {
        if (is_dir($path)) {
            $command = 'CONTSCAN';
        } else {
            $command = 'SCAN';
        }

        $this->sendCommand($command.' '.$path);

        $result = $this->getResponse();

        return $this->filterScanResult($result);
    }

    public function scanBuffer(string $buffer): array
    {
        $this->sendCommand('INSTREAM');

        $this->getSocket()->streamData($buffer);

        $result = $this->getResponse();

        if (false !== ($filtered = $this->filterScanResult($result))) {
            $filtered[0] = preg_replace('/^stream:/', 'buffer:', $filtered[0]);
        }

        return $filtered;
    }

    public function scanResource(string $path): array
    {
        $this->sendCommand('INSTREAM');

        $resource = fopen($path, 'rb+');

        $this->getSocket()->streamResource($resource);

        fclose($resource);

        $result = $this->getResponse();

        if (false !== ($filtered = $this->filterScanResult($result))) {
            $filtered[0] = preg_replace('/^stream:/', $path.':', $filtered[0]);
        }

        return $filtered;
    }

    protected function sendCommand(string $command): ?int
    {
        $response = $this->sendRequest(sprintf(static::COMMAND, $command));

        return false === $response ? null : $response;
    }

    public function setSocket(SocketInterface $socket): ClamdDriver
    {
        $this->socket = $socket;

        return $this;
    }

    protected function getSocket(): SocketInterface
    {
        if (null !== $this->socket) {
            return $this->socket;
        }

        if ($this->getOption('socket')) { // socket set in config
            $options = [
                'socket' => $this->getOption('socket'),
            ];
        } elseif ($this->getOption('host')) { // host set in config
            $options = [
                'host' => $this->getOption('host'),
                'port' => $this->getOption('port', static::PORT),
            ];
        } else { // use defaults
            $options = [
                'socket' => $this->getOption('socket', static::SOCKET_PATH),
                'host' => $this->getOption('host', static::HOST),
                'port' => $this->getOption('port', static::PORT),
            ];
        }
        $this->socket = SocketFactory::create($options);

        return $this->socket;
    }

    protected function sendRequest(string $data, int $flags = 0): ?int
    {
        if (false === ($bytes = $this->getSocket()->send($data, $flags))) {
            throw new RuntimeException('Cannot write to socket');
        }

        return false === $bytes ? null : $bytes;
    }

    protected function getResponse(int $flags = MSG_WAITALL): ?string
    {
        $data = $this->getSocket()->receive($flags);
        $this->getSocket()->close();

        return false === $data ? null : $data;
    }

    protected function filterScanResult(string $result, string $filter = 'FOUND'): array
    {
        $explodedResult = explode("\n", $result);
        $explodedResult = array_filter($explodedResult);

        $list = [];

        foreach ($explodedResult as $line) {
            if (substr($line, -5) === $filter) {
                $list[] = $line;
            }
        }

        return $list;
    }
}
