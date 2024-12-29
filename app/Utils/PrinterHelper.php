<?php

namespace App\Utils;

use App\Enums\PrintJobStatus;
use App\Models\Printer;
use App\Models\PrintJob;
use App\Utils\Process;

class PrinterHelper
{
    /**
     * Returns the number of pages in the PDF document at the given path.
     * @param string $path
     * @return int
     */
    public static function getDocumentPageNumber(string $path): int
    {
        $process = Process::fromShellCommandline(config('commands.pdfinfo') . " $path | grep '^Pages' | awk '{print $2}'");
        $process->run();
        $result = intval($process->getOutput(strval(rand(1, 10))));
        return $result;
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

    /**
     * Returns the number of free pages needed to print with given configuration.
     * @param int $pages
     * @param mixed $copies
     * @param mixed $twoSided
     * @return int|float
     */
    public static function getFreePagesNeeded(int $pages, int $copies, bool $twoSided)
    {
        return $pages * $copies; //We are charging for each of the printed sides of paper
    }

    /**
     * Returns the amount of money needed to print with given configuration.
     * @param int $pages
     * @param int $copies
     * @param bool $twoSided
     * @return mixed
     * @throws BindingResolutionException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public static function getBalanceNeeded(int $pages, int $copies, bool $twoSided)
    {
        $pageTypesNeeded = self::getPageTypesNeeded($pages, $twoSided);

        return $pageTypesNeeded['one_sided'] * config('print.one_sided_cost') * $copies +
            $pageTypesNeeded['two_sided'] * config('print.two_sided_cost') * $copies;
    }

    /**
     * Gets the printjob-status with every printer, updates the status of the completed printjobs.
     */
    public static function updateCompletedPrintJobs()
    {
        foreach(PrintJob::where('state', PrintJobStatus::QUEUED)->whereNotNull('printer_id')->pluck('printer_id')->unique() as $printer_id) {
            Printer::find($printer_id)->updateCompletedPrintJobs();
        }
    }
}
