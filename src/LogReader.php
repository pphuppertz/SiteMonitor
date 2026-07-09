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

        foreach (file($this->path) as $line) {
            $line = trim($line);
            if ($line === '') continue;

            $parts = explode(';', $line);
            
            if (count($parts) !== 5) continue;

            list($ts, $proto, $status, $rt, $err) = $parts;

            $timestamps[] = $ts;
            $protocols[] = $proto;
            $statusCodes[] = (int)$status;
            $responseTimes[] = (float)$rt;
            $errors[] = (int)$err;
        }

        return [
            'timestamps' => $timestamps,
            'protocols' => $protocols,
            'statusCodes' => $statusCodes,
            'responseTimes' => $responseTimes,
            'errors' => $errors
        ];
    }
}