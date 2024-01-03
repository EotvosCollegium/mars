<?php

namespace App\Console\Commands;

use App\Models\Printer;
use Illuminate\Console\Command;

class CreateDefaultPrinter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'printer:create-default';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up command: creates the default printer according to the env file.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Printer::firstOrCreate([
            'name' => env('PRINTER_NAME'),
            'ip' => env('PRINTER_IP'),
            'port' => env('PRINTER_PORT'),
        ]);

        return Command::SUCCESS;
    }
}
