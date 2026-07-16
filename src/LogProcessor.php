<?php
require_once __DIR__ . '/../src/LogEntry.php';

class LogProcessor {
    private $path;

    public function __construct($path) {
        $this->path = $path;
    }

    public function readData() {
        $ipv4Data = [];
        $ipv6Data = [];
        $ipErrorData = [];
        $logEntries = [];
        $ipv4DailyStats = [];
        $ipv6DailyStats = [];
        
        foreach (file($this->path) as $line) {
            $line = trim($line);
            if ($line === '') continue;

            $parts = explode(';', $line);
            
            if (count($parts) !== 5) continue;

            $logEntry = new LogEntry(
                $parts[0], // timestamp
                strtolower($parts[1]), // protocol (normalize to lowercase)
                (int)$parts[2], // status code
                (float)$parts[3], // response time
                (int)$parts[4] // error
            );
            $logEntries[] = $logEntry;
        }
        $ipv4Data = array_values(
            array_filter($logEntries, fn($entry) => $entry->protocol === 'ipv4' && $entry->statusCode === 200 && $entry->error === 0)
            );
        $ipv6Data = array_values(
            array_filter($logEntries, fn($entry) => $entry->protocol === 'ipv6' && $entry->statusCode === 200 && $entry->error === 0)
        );
        $ipErrorData = array_values(
            array_filter($logEntries, fn($entry) => $entry->statusCode !== 200 || $entry->error !== 0)
        );

        $ipv4DailyStats = $this->getDataPerDay(array_filter($logEntries, fn($entry) => $entry->protocol === 'ipv4'));
        $ipv6DailyStats = $this->getDataPerDay(array_filter($logEntries, fn($entry) => $entry->protocol === 'ipv6'));

        return [
            'ipv4Data' => $ipv4Data,
            'ipv6Data' => $ipv6Data,
            'ipErrorData' => $ipErrorData,
            'ipv4DailyStats' => $ipv4DailyStats,
            'ipv6DailyStats' => $ipv6DailyStats,            
        ];
    }

    public function getDataPerDay(array $logEntries): array {
        $dailyData = [];
        
        foreach ($logEntries as $entry) {
            if ($entry->statusCode === 200 && $entry->error === 0) {
                $date = substr($entry->timestamp, 0, 10); // Extract the date part (YYYY-MM-DD)
                if (!isset($dailyData[$date])) {
                    $dailyData[$date] = [];
                }
                // $dailyData[$date][] = $entry->responseTime;
            }
        }

        foreach ($dailyData as $date => &$data) {            
            $totaltime = 0;
            if (count($logEntries) > 0) {
                $responseTimes = [];
                foreach ($logEntries as $entry) {
                    if (substr($entry->timestamp, 0, 10) === $date) {
                        $responseTimes[] = $entry->responseTime;
                        $totalTime += $entry->responseTime;
                    }
                }
                sort($responseTimes, SORT_NUMERIC);                    
                $middle = intdiv(count($responseTimes), 2);
                if (count($responseTimes) % 2) { // Odd number of elements
                    $data['medianTime'] = $responseTimes[$middle];
                } else { // Even number of elements
                    echo ($responseTimes[$middle - 1] + $responseTimes[$middle]) / 2;
                    $data['medianTime'] = ($responseTimes[$middle - 1] + $responseTimes[$middle]) / 2;
                }
                $data['averageTime'] = $totaltime / count($responseTimes);
            } else {
                $data['averageTime'] = null;
            }
        }
        return $dailyData;
    }
}           
