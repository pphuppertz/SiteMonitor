<?php

class LogEntry {
    public string $timestamp;
    public string $protocol;
    public int $statusCode;
    public float $responseTime;
    public int $error;

    public function __construct(string $timestamp, string $protocol, int $statusCode, float $responseTime, int $error) {
        $this->timestamp = $timestamp;
        $this->protocol = $protocol;
        $this->statusCode = $statusCode;
        $this->responseTime = $responseTime;
        $this->error = $error;
    }
}