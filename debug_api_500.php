<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://trishautsav.in/api/products");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $http_code\n\n";
echo "Output: \n$output";
