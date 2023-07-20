<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessWifiConnections extends Command
{
    protected $signature = 'internet:process_wifi_connections';

    protected $description = 'Process the radius log and leases file to store wifi connections in the database';

    public function handle()
    {
        $radiusLogFile = config('internet.radius_log_path');
        $leasesFile = config('internet.dhcp_leases_path');

        $this->info('Processing radius log...');
        $radiusEntries = $this->parseRadiusLog($radiusLogFile);

        $this->info('Processing leases file...');
        $leasesEntries = $this->parseLeasesFile($leasesFile);

        $this->info('Storing data in the database...');
        $this->processAndStoreData($radiusEntries, $leasesEntries);

        $this->info('Processing completed successfully!');
    }

    private function parseRadiusLog($filePath)
    {
        $pattern = '/(Mon|Tue|Wed|Thu|Fri|Sat|Sun) (\w{3}) (\d{1,2}) (\d{2}):(\d{2}):(\d{2}) (\d{4}) : Auth: \(\d+\) Login OK: \[([^]]+)\] \(from client [^ ]+ port \d+ cli (\S+)\)/';
        $logContent = file_get_contents($filePath);
        preg_match_all($pattern, $logContent, $matches, PREG_SET_ORDER);

        $radiusEntries = [];
        foreach ($matches as $match) {
            $dateTimeString = $match[1] . ' ' . $match[2] . ' ' . $match[3] . ' ' . $match[4] . ':' . $match[5] . ':' . $match[6] . ' ' . $match[7];
            $mac = $this->formatMacAddress($match[9]);
            $radiusEntries[] = [
                'user' => $match[8],
                'mac' => $mac,
                'timestamp' => Carbon::parse($dateTimeString)
            ];
            $this->info($mac . " => " . $match[8] . " (".$dateTimeString .")");

        }

        return $radiusEntries;
    }

    private function parseLeasesFile($filePath)
    {
        // Read the leases file and extract relevant information
        $leasesContent = file_get_contents($filePath);
        $leasesEntries = [];

        $pattern = '/lease (\d+\.\d+\.\d+\.\d+) \{[^}]*\n\s*starts \d+ (\d{4})\/(\d{2})\/(\d{2}) (\d{2}):(\d{2}):(\d{2});\n\s*ends \d+ (\d{4})\/(\d{2})\/(\d{2}) (\d{2}):(\d{2}):(\d{2});[^}]*\n\s*hardware ethernet ((?:[0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2})(?:[^}]*\n\s*client-hostname "([^"]+)")?;/';
        preg_match_all($pattern, $leasesContent, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $leaseStartDateTime = $match[2] . '-' . $match[3] . '-' . $match[4] . ' ' . $match[5] . ':' . $match[6] . ':' . $match[7];
            $leaseEndDateTime = $match[8] . '-' . $match[9] . '-' . $match[10] . ' ' . $match[11] . ':' . $match[12] . ':' . $match[13];
            $mac = $this->formatMacAddress($match[14]);

            $leasesEntries[] = [
                'ip' => $match[1],
                'mac' => $mac,
                'start' => Carbon::parse($leaseStartDateTime),
                'end' => Carbon::parse($leaseEndDateTime),
                'note' => $match[15] ?? "",
            ];
            $this->info($mac . " => " . $match[1] . " (".$leaseStartDateTime . " - ". $leaseEndDateTime .")");
        }

        return $leasesEntries;
    }

    private function formatMacAddress($macAddress)
    {
        // Filter out all non-hexadecimal characters
        $cleanedMac = preg_replace('/[^A-F0-9a-f]/', '', $macAddress);

        // Split the MAC address into parts of 2 characters each and format with colons
        $formattedMac = implode(':', str_split($cleanedMac, 2));

        // Convert the MAC address to uppercase
        return strtoupper($formattedMac);
    }

    private function processAndStoreData($radiusEntries, $leasesEntries)
    {
        foreach ($radiusEntries as $radiusEntry) {

            $lease = $this->findLeaseByMAC($radiusEntry['mac'], $leasesEntries);
            if($lease){
                $this->info($radiusEntry['user'] . " => " . $lease['ip'] . " (" .$radiusEntry['mac'] . ")");

                DB::table('wifi_connections')->insert([
                    'ip' => $lease['ip'],
                    'mac_address' => $radiusEntry['mac'],
                    'wifi_username' => $radiusEntry['user'],
                    'lease_start' => $lease['start'],
                    'lease_end' => $lease['end'],
                    'radius_timestamp' => $radiusEntry['timestamp'],
                    'note' => $lease['note']
                ]);
            } else {
                $this->warn("Can not find lease for mac " . $radiusEntry['mac'] . " (" . $radiusEntry['user'].")");
            }

        }
    }

    private function findLeaseByMAC($macAddress, $leasesEntries)
    {
        foreach ($leasesEntries as $leasesEntry) {
            if ($leasesEntry['mac'] === $macAddress) {
                return $leasesEntry;
            }
        }

        return null;
    }
}
