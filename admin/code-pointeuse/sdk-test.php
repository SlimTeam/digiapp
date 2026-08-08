<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/zkteco-autoload.php';

use CodingLibs\ZktecoPhp\Libs\ZKTeco;

$config = require __DIR__ . '/devices.php';
$deviceIPs = $config['device_ips'] ?? ['192.168.100.140', '192.168.100.120'];
$devicePort = $config['device_port'] ?? 4370;

echo "<h2>Diagnostic connexion ZKTeco SDK</h2>";
echo "<p>Extension sockets: " . (extension_loaded('sockets') ? 'OK' : 'MANQUANTE') . "</p>";
echo "<p>Date: " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

foreach ($deviceIPs as $ip) {
    echo "<h3>Pointeuse: $ip:$devicePort</h3>";

    $pingCmd = sprintf('ping -n 1 %s', escapeshellarg($ip));
    $pingResult = [];
    $pingReturn = 0;
    exec($pingCmd, $pingResult, $pingReturn);
    $pingStatus = $pingReturn === 0 ? '<span style="color:green;">OK</span>' : '<span style="color:red;">ECHEC</span>';
    echo "<p>Ping: $pingStatus</p>";

    if ($pingReturn !== 0) {
        echo "<p><span style='color:red;'>Impossible de pinger la pointeuse.</span></p><hr>";
        continue;
    }

    try {
        $zk = new ZKTeco($ip, $devicePort, false, 10);

        echo "<p>Connexion en cours...";
        $connected = $zk->connect();

        if ($connected) {
            echo " <span style='color:green;'>REUSSIE</span></p>";
            echo "<p>Session ID: " . $zk->_session_id . "</p>";

            echo "<p>Version: " . htmlspecialchars($zk->version()) . "</p>";
            echo "<p>Vendor: " . htmlspecialchars($zk->vendorName()) . "</p>";
            echo "<p>Platform: " . htmlspecialchars($zk->platform()) . "</p>";
            echo "<p>Serial: " . htmlspecialchars($zk->serialNumber()) . "</p>";

            $users = $zk->getUsers();
            echo "<p>Utilisateurs trouves: " . count($users) . "</p>";
            if (!empty($users)) {
                echo "<table border='1' cellpadding='5'><tr><th>UID</th><th>User ID</th><th>Nom</th><th>Role</th></tr>";
                foreach ($users as $userInfo) {
                    echo "<tr><td>{$userInfo['uid']}</td><td>{$userInfo['user_id']}</td><td>" . htmlspecialchars($userInfo['name']) . "</td><td>{$userInfo['role']}</td></tr>";
                }
                echo "</table>";
            }

            $attendances = $zk->getAttendances();
            echo "<p>Pointages trouves: " . count($attendances) . "</p>";
            if (!empty($attendances)) {
                echo "<table border='1' cellpadding='5'><tr><th>UID</th><th>User ID</th><th>State</th><th>Time</th><th>Type</th></tr>";
                foreach ($attendances as $att) {
                    echo "<tr><td>{$att['uid']}</td><td>{$att['user_id']}</td><td>{$att['state']}</td><td>{$att['record_time']}</td><td>{$att['type']}</td></tr>";
                }
                echo "</table>";
            }

            $zk->disconnect();
            echo "<p><span style='color:green;'>Deconnexion OK</span></p>";
        } else {
            echo " <span style='color:red;'>ECHEC</span></p>";
            echo "<p>La pointeuse ne repond pas. Verifiez le port UDP 4370 et la configuration reseau.</p>";
        }
    } catch (Exception $e) {
        echo "<p><span style='color:red;'>Erreur: " . $e->getMessage() . "</span></p>";
    }

    echo "<hr>";
}

echo "<h3>Test autoloader SDK</h3>";
echo "<p>Classe ZKTeco chargee: " . (class_exists('CodingLibs\ZktecoPhp\Libs\ZKTeco') ? 'OK' : 'ECHEC') . "</p>";
echo "<p>Classe Util chargee: " . (class_exists('CodingLibs\ZktecoPhp\Libs\Services\Util') ? 'OK' : 'ECHEC') . "</p>";
echo "<p>Classe Attendance chargee: " . (class_exists('CodingLibs\ZktecoPhp\Libs\Services\Attendance') ? 'OK' : 'ECHEC') . "</p>";
echo "<p>Classe User chargee: " . (class_exists('CodingLibs\ZktecoPhp\Libs\Services\User') ? 'OK' : 'ECHEC') . "</p>";
echo "<p>Classe Connect chargee: " . (class_exists('CodingLibs\ZktecoPhp\Libs\Services\Connect') ? 'OK' : 'ECHEC') . "</p>";
