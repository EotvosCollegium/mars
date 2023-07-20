<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessWifiConnections implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $radiusLogFile = config('internet.radius_log_path');
        $leasesFile = config('internet.dhcp_leases_path');

        $radiusEntries = $this->parseRadiusLog($radiusLogFile);

        $leasesEntries = $this->parseLeasesFile($leasesFile);

        $this->processAndStoreData($radiusEntries, $leasesEntries);

        Log::info("Wifi connections processed successfully");
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
                Log::warning("Can not find lease for mac " . $radiusEntry['mac'] . " (" . $radiusEntry['user'].")");
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
