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
        // $ipv4Averages = [];
        // $ipv6Averages = [];
        // $ipv4Medians = [];
        // $ipv6Medians = [];
    
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

        return [
            'ipv4Data' => $ipv4Data,
            'ipv6Data' => $ipv6Data,
            'ipErrorData' => $ipErrorData,
            // 'ipv4Averages' => $ipv4Averages,
            // 'ipv6Averages' => $ipv6Averages,
            // 'ipv4Medians' => $ipv4Medians,
            // 'ipv6Medians' => $ipv6Medians,
        ];
    }

    public function getAveragesPerDay($protocolToCalculate) {
        $dailyData = [];

        


        }
    }            
