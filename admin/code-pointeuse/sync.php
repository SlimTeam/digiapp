<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/zkteco-autoload.php';

use CodingLibs\ZktecoPhp\Libs\ZKTeco;

$config = require __DIR__ . '/devices.php';

$configuredDevices = $config['devices'] ?? [];

if (empty($configuredDevices)) {
    $configuredDevices = [
        ['name' => 'Pointeuse Atelier', 'ip' => '192.168.100.140', 'port' => 4370, 'department' => 'Atelier'],
        ['name' => 'Pointeuse Chantier', 'ip' => '192.168.100.120', 'port' => 4370, 'department' => 'Chantier'],
    ];
}

$devicePort = $config['device_port'] ?? 4370;

if (!extension_loaded('sockets')) {
    echo json_encode([
        "status"  => "error",
        "message" => "L'extension PHP 'sockets' n'est pas activée dans votre fichier php.ini."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$errors = [];
$formattedData = [];
$connectedCount = 0;

foreach ($configuredDevices as $deviceConfig) {
    $deviceIP = $deviceConfig['ip'];
    $devicePortCurrent = $deviceConfig['port'] ?? $devicePort;
    $deviceDept = $deviceConfig['department'] ?? 'General';

    try {
        $zkteco = new ZKTeco($deviceIP, $devicePortCurrent, true, 10);
        if ($zkteco->connect()) {
            $rawUsers = $zkteco->getUsers();
            $rawAttendance = $zkteco->getAttendances();
            $zkteco->disconnect();

            $userMapByUid = [];
            $userMapByUserId = [];
            foreach ($rawUsers as $userInfo) {
                $userMapByUid[$userInfo['uid']] = $userInfo['name'];
                $userMapByUserId[$userInfo['user_id']] = $userInfo['name'];
            }

            foreach ($rawAttendance as $log) {
                $userId = $log['user_id'];
                $name = $userMapByUid[$log['uid']] ?? ($userMapByUserId[$userId] ?? "Employé #{$userId}");
                $typeStr = ($log['type'] == 0) ? 'Entrée' : 'Sortie';

                $formattedData[] = [
                    "user_id"    => $userId,
                    "name"       => $name,
                    "department" => $deviceDept,
                    "check_time" => $log['record_time'],
                    "type"       => $typeStr,
                    "device_ip"  => $deviceIP,
                ];
            }

            $connectedCount++;
        } else {
            $errors[] = "Impossible de contacter la pointeuse {$deviceIP}:{$devicePortCurrent}.";
        }
    } catch (Exception $e) {
        $errors[] = "Erreur avec la pointeuse {$deviceIP} : " . $e->getMessage();
    }
}

if ($connectedCount === 0) {
    echo json_encode([
        "status"  => "error",
        "message" => "Aucune pointeuse accessible. Vérifiez les adresses IP et la connexion réseau. Details : " . implode(' ', $errors)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

usort($formattedData, function($a, $b) {
    return strtotime($b['check_time']) - strtotime($a['check_time']);
});

$jsonPath = __DIR__ . '/data.json';
if (file_put_contents($jsonPath, json_encode([
    "last_sync" => date('Y-m-d H:i:s'),
    "count"     => count($formattedData),
    "data"      => $formattedData
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
    echo json_encode([
        "status"  => "error",
        "message" => "Impossible d'écrire le fichier de cache data.json."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    "status"    => "success",
    "message"   => "Synchronisation réussie ({$connectedCount} pointeuse(s)).",
    "count"     => count($formattedData),
    "last_sync" => date('Y-m-d H:i:s')
], JSON_UNESCAPED_UNICODE);
