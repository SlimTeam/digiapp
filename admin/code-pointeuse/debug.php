<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/zkteco/autoload.php';

use CodingLibs\ZktecoPhp\Libs\ZKTeco;

$config     = require __DIR__ . '/devices.php';
$deviceList = $config['devices'] ?? [];
$devicePort = $config['device_port'] ?? 4370;

function isPingable(string $ip): bool {
    $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    $cmd = $isWindows
        ? 'ping -n 1 -w 1000 ' . escapeshellarg($ip)
        : 'ping -c 1 -W 1 ' . escapeshellarg($ip);
    exec($cmd, $out, $rc);
    return $rc === 0;
}

echo "PHP: " . PHP_VERSION . "\n";
echo "OS: " . PHP_OS . "\n";
echo "Sockets: " . (extension_loaded('sockets') ? 'OK' : 'MANQUANT') . "\n";
echo "Devices: " . count($deviceList) . " configurée(s)\n";
echo str_repeat('=', 60) . "\n\n";

foreach ($deviceList as $device) {
    $ip   = $device['ip'];
    $name = $device['name'] ?? $ip;
    $port = (int) ($device['port'] ?? $devicePort);

    echo "=== {$name} ({$ip}:{$port}) ===\n";

    if (!isPingable($ip)) {
        echo "  Ping: ÉCHEC\n";
        echo "  Connect: ÉCHEC (machine injoignable)\n\n";
        continue;
    }
    echo "  Ping: OK\n";

    $zk = new ZKTeco($ip, $port, false, 5);

    if (!$zk->connect()) {
        echo "  Connect: ÉCHEC\n";
        echo "  Astuce : vérifiez que le port UDP {$port} est OUVERT dans le pare-feu,\n";
        echo "          que le protocole UDP est activé sur la pointeuse,\n";
        echo "          et que l'adresse IP est correcte.\n\n";
        continue;
    }
    echo "  Connect: OK\n";

    echo "  Version: " . json_encode($zk->version()) . "\n";
    echo "  DeviceName: " . json_encode($zk->deviceName()) . "\n";

    $users = $zk->getUsers();
    echo "  Users: " . count($users) . " trouvé(s)\n";
    foreach (array_slice($users, 0, 5, true) as $u) {
        echo "    - PIN={$u['user_id']} Nom={$u['name']}\n";
    }

    $attendance = $zk->getAttendances();
    echo "  Attendances: " . count($attendance) . " trouvé(s)\n";
    foreach (array_slice($attendance, 0, 5) as $a) {
        echo "    - PIN={$a['user_id']} Time={$a['record_time']} State={$a['state']} Type={$a['type']}\n";
    }

    echo "  Time: " . json_encode($zk->getTime()) . "\n";
    $zk->disconnect();
    echo "  Disconnect: OK\n\n";
}
