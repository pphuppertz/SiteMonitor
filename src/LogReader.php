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
        $ipv4Averages = [];
        $ipv6Averages = [];
        $ipv4Medians = [];
        $ipv6Medians = [];

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
        $ipv4Averages = $this->getAveragesPerDay('ipv4');
        $ipv6Averages = $this->getAveragesPerDay('ipv6');
        // $ipv4Medians = $this->getMediansPerDay('ipv4');
        // $ipv6Medians = $this->getMediansPerDay('ipv6');

        return [
            'ipv4Data' => $ipv4Data,
            'ipv6Data' => $ipv6Data,
            'ipErrorData' => $ipErrorData,
            'ipv4Averages' => $ipv4Averages,
            'ipv6Averages' => $ipv6Averages,
            'ipv4Medians' => $ipv4Medians,
            'ipv6Medians' => $ipv6Medians,
        ];
    }

    public function getAveragesPerDay($protocolToCalculate) {
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

            if ($status === 200 && $error === 0 && $protocol === $protocolToCalculate) {
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