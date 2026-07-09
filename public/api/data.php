<?php
require_once __DIR__ . '/../../src/LogReader.php';

$reader = new LogReader(__DIR__ . '/../../log/peterhuppertz.net/sitetest.log');
echo json_encode($reader->getData());