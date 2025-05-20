<?php

namespace App\Utils;

use App\Enums\PrintJobStatus;
use App\Models\PrinterConfiguration;
use App\Models\PrintJob;
use App\Utils\Process;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;
use Illuminate\Support\Facades\Log;

class PrinterHelper
{
    /**
     * Returns the number of pages in the PDF document at the given path.
     * @param string $path
     * @return int
     */
    public static function getDocumentPageNumber(string $path): int
    {
        $process = new Process([config('commands.pdfinfo'), $path]);
        $process->run();
        $pdfinfo = $process->getOutput("Title:           TEST
Author:          TEST
Creator:         TEST
Producer:        TEST
CreationDate:    TEST
ModDate:         TEST
Custom Metadata: TEST
Metadata Stream: TEST
Tagged:          TEST
UserProperties:  TEST
Suspects:        TEST
Form:            TEST
JavaScript:      TEST
Pages:           " . strval(rand(1, 10)) . "
Encrypted:       TEST
Page size:       TEST
Page rot:        TEST
File size:       TEST
Optimized:       no
PDF version:     1.4");
        if (preg_match('/Pages:\s+(\d+)/', $pdfinfo, $needle)) {
            return intval($needle[1]);
        } else {
            throw new \Exception("Could not determine number of pages");
        }
    }

    /**
     * Returns an array with the number of one-sided and two-sided pages needed to print the given number of pages.
     * @param int $pages
     * @param bool $twoSided
     * @return array
     */
    public static function getPageTypesNeeded(int $pages, bool $twoSided)
    {
        $oneSidedPages = 0;
        $twoSidedPages = 0;
        if (!$twoSided) {
            $oneSidedPages = $pages;
        } else {
            $oneSidedPages = $pages % 2;
            $twoSidedPages = floor($pages / 2);
        }

        return [
            'one_sided' => $oneSidedPages,
            'two_sided' => $twoSidedPages,
        ];
    }
}
