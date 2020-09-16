<?php

declare(strict_types=1);

namespace Avasil\ClamAv\Driver;

use Avasil\ClamAv\Exception\RuntimeException;

class ClamdRemoteDriver extends ClamdDriver
{
    public const SOCKET_PATH = '';

    public function __construct(array $options = [])
    {
        unset($options['socket']);
        parent::__construct($options);
    }

    public function scan(string $path): array
    {
        if (!is_file($path)) {
            throw new RuntimeException('Remote scan of directory is not supported');
        }

        $this->sendCommand('INSTREAM');

        $resource = fopen($path, 'rb');

        $this->getSocket()->streamResource($resource);

        fclose($resource);

        $result = $this->getResponse();

        if (false !== ($filtered = $this->filterScanResult($result))) {
            $filtered[0] = preg_replace('/^stream:/', $path.':', $filtered[0]);
        }

        return $filtered;
    }
}
