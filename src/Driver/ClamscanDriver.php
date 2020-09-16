<?php

declare(strict_types=1);

namespace Avasil\ClamAv\Driver;

use Avasil\ClamAv\Exception\ConfigurationException;
use Avasil\ClamAv\Exception\RuntimeException;

class ClamscanDriver extends AbstractDriver
{
    public const EXECUTABLE = '/usr/bin/clamscan';
    public const COMMAND = '--infected --no-summary --recursive %s';
    public const CLEAN = 0;
    public const INFECTED = 1;

    public function __construct(array $options = [])
    {
        parent::__construct($options);

        if (!is_executable($this->getExecutable())) {
            throw new ConfigurationException($this->getExecutable() ? sprintf('%s is not valid executable file', $this->getExecutable()) : 'Executable required, please check your config.');
        }
    }

    public function ping(): bool
    {
        return (bool) $this->version();
    }

    public function version(): string
    {
        exec($this->getExecutable().' -V', $out, $return);

        if (!$return) {
            return $out[0];
        }

        return '';
    }

    public function scan(string $path): array
    {
        $safe_path = escapeshellarg($path);

        $return = -1;

        $cmd = $this->getExecutable().' '.sprintf($this->getCommand(), $safe_path);

        exec($cmd, $out, $return);

        return $this->parseResults($return, $out);
    }

    public function scanBuffer(string $buffer): array
    {
        $descriptorSpec = [
            0 => ['pipe', 'r'],  // stdin is a pipe that clamscan will read from
            1 => ['pipe', 'w'],  // stdout is a pipe that clamscan will write to
        ];

        $cmd = $this->getExecutable().' '.sprintf($this->getCommand(), '-');

        $process = @proc_open($cmd, $descriptorSpec, $pipes);

        if (!is_resource($process)) {
            throw new RuntimeException('Failed to open a process file pointer');
        }

        fwrite($pipes[0], $buffer);
        fclose($pipes[0]);

        $out = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $return = proc_close($process);

        if (false !== ($parsed = $this->parseResults($return, explode("\n", $out)))) {
            $parsed[0] = preg_replace('/^stream:/', 'buffer:', $parsed[0]);
        }

        return $parsed;
    }

    public function scanResource(string $path): array
    {
        // Todo implement

        return [];
    }

    protected function getExecutable(): string
    {
        return $this->getOption('executable', static::EXECUTABLE);
    }

    protected function getCommand(): string
    {
        return $this->getOption('command', static::COMMAND);
    }

    protected function getInfected(): int
    {
        return $this->getOption('infected', static::INFECTED);
    }

    protected function getClean(): int
    {
        return $this->getOption('clean', static::CLEAN);
    }

    private function parseResults(int $return, array $out): array
    {
        $result = [];

        if ($return === $this->getInfected()) {
            foreach ($out as $infected) {
                if (empty($infected)) {
                    break;
                }
                $result[] = $infected;
            }
        }

        return $result;
    }
}
