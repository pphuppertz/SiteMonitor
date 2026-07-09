<?php
// $base = __DIR__ . "/../../log/sitemonitor"; // for development
$base = __DIR__ . "/var/log/sitemonitor"; // for production
$dirs = array_filter(glob($base . '/*'), 'is_dir');
$sites = array_map('basename', $dirs);
echo json_encode($sites);