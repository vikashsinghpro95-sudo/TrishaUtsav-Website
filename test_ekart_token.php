<?php
require_once __DIR__ . '/includes/EkartService.php';

$service = new EkartService();

$reflection = new ReflectionClass($service);
$tokenMethod = $reflection->getMethod('getAccessToken');
$tokenMethod->setAccessible(true);
$token = $tokenMethod->invoke($service);

echo "Token: $token\n";
