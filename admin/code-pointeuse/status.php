<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/zkteco-autoload.php';

use CodingLibs\ZktecoPhp\Libs\ZKTeco;

$config = require __DIR__ . '/devices.php';

$devicePort = $config['device_port'] ?? 4370;
$configuredDevices = $config['devices'] ?? [];

if (empty($configuredDevices)) {
    $configuredDevices = [
        ['name' => 'Pointeuse Atelier', 'ip' => '192.168.100.140', 'port' => 4370, 'department' => 'Atelier'],
        ['name' => 'Pointeuse Chantier', 'ip' => '192.168.100.120', 'port' => 4370, 'department' => 'Chantier'],
    ];
}

if (!extension_loaded('sockets')) {
    echo json_encode([
        'status'  => 'error',
        'message' => "L'extension PHP 'sockets' n'est pas activee dans votre fichier php.ini."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function isPingable(string $ip): bool {
    $command = sprintf('ping -n 1 %s', escapeshellarg($ip));
    exec($command, $output, $returnVar);
    return $returnVar === 0;
}

$devices = [];

foreach ($configuredDevices as $deviceConfig) {
    $deviceIP = $deviceConfig['ip'];
    $devicePort = $deviceConfig['port'] ?? $devicePort;
    $reachable = false;
    $message = 'Aucune reponse. Verifiez la connexion et l\'adresse IP.';

    $pingOk = isPingable($deviceIP);
    if (!$pingOk) {
        $message = 'Ping echoue. Verifiez le reseau et l\'adresse IP de la pointeuse.';
    } else {
        try {
            $zk = new ZKTeco($deviceIP, $devicePort, false, 10);
            if ($zk->connect()) {
                $reachable = true;
                $message = 'Pointeuse connectee avec succes.';
                $zk->disconnect();
            } else {
                $message = 'Ping OK, mais la pointeuse ne repond pas sur le port UDP 4370.';
            }
        } catch (Exception $e) {
            $message = 'Erreur interne : ' . $e->getMessage();
        }
    }

    $devices[] = [
        'name'      => $deviceConfig['name'] ?? $deviceIP,
        'ip'        => $deviceIP,
        'port'      => $devicePort,
        'department'=> $deviceConfig['department'] ?? 'General',
        'reachable' => $reachable,
        'message'   => $message,
    ];
}

echo json_encode([
    'status'  => 'success',
    'devices' => $devices,
], JSON_UNESCAPED_UNICODE);
