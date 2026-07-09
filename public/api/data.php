<?php
require_once __DIR__ . '/../../src/LogReader.php';

$site = $_GET['site'] ?? null;

if (!$site) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing site parameter']);
    exit;
}

$reader = new LogReader(__DIR__ . "/../../log/sitemonitor/$site/sitetest.log");
echo json_encode($reader->getData());