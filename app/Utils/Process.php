<?php

namespace App\Utils;

use Symfony\Component\Process\Process as SymfonyProcess;

class Process extends SymfonyProcess
{
    public function run(?callable $callback = null, array $env = []): int
    {
        if (config('app.debug') === false) {
            return parent::run($callback, $env);
        }
        return 0;
    }

    public function getOutput(string $debugOutput = ''): string
    {
        if (config('app.debug') === false) {
            return parent::getOutput();
        }
        return $debugOutput;
    }

    public function getExitCode(): ?int
    {
        if (config('app.debug') === false) {
            return parent::getExitCode();
        }
        return 0;
    }
}
