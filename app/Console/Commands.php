<?php

namespace App\Console;

use Illuminate\Support\Facades\Log;

/**
 * Collection of exec commands.
 * The commands return values accordingly in deug mode as well.
 */
class Commands
{
    private static function isDebugMode()
    {
        return config('app.debug');
    }

    public static function pingRouter($router)
    {
        if (self::isDebugMode()) {
            $result = rand(1, 10) > 9 ? "error" : '';
        } else {
            // This happens too often to log.
            $command = "ping " . $router->ip . " -c 1 | grep 'error\|unreachable'";
            $result = exec($command);
        }
        return $result;
    }

    public static function latexToPdf($path, $outputDir)
    {
        if (self::isDebugMode()) {
            $result = "ok";
        } else {
            $command = "pdflatex " . "-interaction=nonstopmode -output-dir " . $outputDir . " " . $path . " 2>&1";
            Log::info($command);
            $result = exec($command);
        }
        return $result;
    }
}
