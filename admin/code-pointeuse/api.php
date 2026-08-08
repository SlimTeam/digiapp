<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$jsonFile = __DIR__ . '/data.json';

if (!file_exists($jsonFile)) {
    require_once __DIR__ . '/sync.php';
    exit;
}

$content = file_get_contents($jsonFile);
$data = json_decode($content, true);

if (!$data || !isset($data['data'])) {
    require_once __DIR__ . '/sync.php';
    exit;
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
