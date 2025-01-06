<?php

namespace App\Utils;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process as SymfonyProcess;

class Process extends SymfonyProcess
{
    /**
     * Run the process. If the app is in debug mode, the process will not be executed.
     *
     * @param callable|null $callback
     * @param array $env
     * @return int
     */
    public function run(?callable $callback = null, array $env = [], bool $log = true): int
    {
        if (config('app.debug') === false || config('commands.run_in_debug') === true) {
            $return_value = parent::run($callback, $env);
            if($log) {
                if($return_value === 0) {
                    Log::info("Command: " . $this->getCommandLine() . " executed successfully. With output: " . $this->getOutput() . " and error output: " . $this->getErrorOutput());
                } else {
                    Log::error("Command: " . $this->getCommandLine() . " failed with error code: " . $return_value . "\nWith output: " . $this->getOutput() . " and error output: " . $this->getErrorOutput());
                }
            }
            return $return_value;
        }
        Log::info("Process not executed in debug mode.");
        return 0;
    }

    /**
     * Get the output of the process.
     *
     * @param string $debugOutput
     * @return string
     */
    public function getOutput(string $debugOutput = ''): string
    {
        if (config('app.debug') === false || config('commands.run_in_debug') === true) {
            return parent::getOutput();
        }
        Log::info("Process output not available in debug mode.");
        return $debugOutput;
    }

    /**
     * Get the exit code of the process.
     *
     * @param string $debugOutput
     * @return int|null
     */
    public function getExitCode(): ?int
    {
        if (config('app.debug') === false || config('commands.run_in_debug') === true) {
            return parent::getExitCode();
        }
        Log::info("Process exit code not available in debug mode.");
        return 0;
    }
}
