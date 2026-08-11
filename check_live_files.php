<?php
header('Content-Type: text/plain');
echo "ROOT INDEX.PHP:\n";
echo file_get_contents(__DIR__ . '/index.php');
echo "\n\nPUBLIC/API.PHP:\n";
echo file_get_contents(__DIR__ . '/public/api.php');
