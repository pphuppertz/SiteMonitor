<?php

class LogReader {
    private $path;

    public function __construct($path) {
        $this->path = $path;
    }
    public function getData() {
        $timestamps = [];
        $protocols = [];
        $statusCodes = [];
        $responseTimes = [];
        $errors = [];        

        $ipv4Data = [];
        $ipv6Data = [];
        $ipErrorData = [];
        $averagesPerDay = [];

        foreach (file($this->path) as $line) {
            $line = trim($line);
            if ($line === '') continue;

            $parts = explode(';', $line);
            
            if (count($parts) !== 5) continue;  

            $timestamp = $parts[0];
            $protocol  = strtolower($parts[1]);   // normalize
            $status    = (int)$parts[2];
            $time      = (float)$parts[3];
            $error     = (int)$parts[4];
            
            if ($status !== 200 || $error !== 0) {
                $ipErrorData[] = [
                    'timestamp' => $timestamp,
                    'protocol' => $protocol,
                    'statusCode' => $status,
                    'responseTime' => $time,
                    'error' => $error
                ];
            } elseif ($protocol === 'ipv4') {
                $ipv4Data[] = [
                    'timestamp' => $timestamp,
                    'protocol' => $protocol,
                    'statusCode' => $status,
                    'responseTime' => $time,
                    'error' => $error
                ];
            } else {
                $ipv6Data[] = [
                    'timestamp' => $timestamp,
                    'protocol' => $protocol,
                    'statusCode' => $status,
                    'responseTime' => $time,
                    'error' => $error
                ];                
            }
        }
        $averagesPerDay = $this->getAveragesPerDay(); 
        
        return [
            'ipv4Data' => $ipv4Data,
            'ipv6Data' => $ipv6Data,
            'ipErrorData' => $ipErrorData,
            'averagesPerDay' => $averagesPerDay,
        ];
    }

    public function getAveragesPerDay() {
        $dailyData = [];

        foreach (file($this->path) as $line) {
            $line = trim($line);
            if ($line === '') continue;

            $parts = explode(';', $line);
            if (count($parts) !== 5) continue;

            $timestamp = $parts[0];
            $protocol  = strtolower($parts[1]);
            $status    = (int)$parts[2];
            $time      = (float)$parts[3];
            $error     = (int)$parts[4];

            if ($status === 200 && $error === 0) {
                $date = substr($timestamp, 0, 10); // Extract date part
                if (!isset($dailyData[$date])) {
                    $dailyData[$date] = ['totalTime' => 0, 'count' => 0];
                }
                $dailyData[$date]['totalTime'] += $time;
                $dailyData[$date]['count']++;
            }
        }

        // Calculate averages
        foreach ($dailyData as $date => &$data) {
            if ($data['count'] > 0) {
                $data['averageTime'] = $data['totalTime'] / $data['count'];
            } else {
                $data['averageTime'] = null;
            }
        }        
        return $dailyData;
    }
}