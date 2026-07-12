<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../src/LogProcessor.php';

$site = $_GET['site'] ?? null;

if (!$site) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing site parameter']);
    exit;
}

$processor = new LogProcessor("/var/log/sitemonitor/$site/sitetest.log");
echo json_encode($processor->readData());