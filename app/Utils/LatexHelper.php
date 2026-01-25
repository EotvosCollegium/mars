<?php

namespace App\Utils;

use Illuminate\Support\Facades\Storage;
use App\Console\Commands;

class LatexHelper
{
    /**
     * Converts a single character to a \symbol{...} command
     * @param  string  $char
     * @return string
     */
    private static function convertCharToSymbol(string $char): string
    {
        if ($char == ' ') {
            return ' ';
        }
        return "\\symbol{" . mb_ord($char) . "}";
    }

    /**
     * Converts string to a relatively safe Latex code by putting every character into a seperate \symbol{...}
     * @param  string  $data
     * @return string
     */
    public static function sanitizeLatex(string|null $data): string
    {
        if ($data == null) {
            return "";
        }
        $len = mb_strlen($data);
        $result = [];
        for ($i = 0; $i < $len; $i++) {
            $result[] = LatexHelper::convertCharToSymbol(mb_substr($data, $i, 1));
        }
        return implode($result);
    }

    /**
     * Generates a LaTeX file by inserting data into a view consisting of LaTeX code,
     * then runs the LaTeX compiler on the code.
     * 
     * Returns the path of the PDF file
     * (or in debug mode, the path of the .tex file instead).
     * 
     * Beware: sanitizing has to be done in the template itself!
     */
    public static function generatePDF($path, $data)
    {
        $renderedLatex = view($path)->with($data)->render();

        $filename =  md5(rand(0, 100000) . date('c'));
        Storage::disk('latex')->put($filename . '.tex', $renderedLatex);

        $outputDir = Storage::disk('latex')->path('/');

        $pathTex = Storage::disk('latex')->path($filename . ".tex");
        $pathPdf = Storage::disk('latex')->path($filename . ".pdf");

        // TODO: figure out result
        Commands::latexToPdf($pathTex, $outputDir);

        if (config('app.debug') && !config('commands.run_in_debug')) {
            return $pathTex;
        } else {
            return $pathPdf;
        }
    }
}
