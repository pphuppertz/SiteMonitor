<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../../src/LogReader.php';

$reader = new LogReader(__DIR__ . '/../../log/peterhuppertz.net/sitetest.log');
echo json_encode($reader->getData());