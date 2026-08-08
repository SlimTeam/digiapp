<?php
$configFile = __DIR__ . '/config/devices.json';

if (!file_exists($configFile)) {
    return [
        'device_ips'   => [],
        'device_port'  => 4370,
        'devices'      => [],
    ];
}

$config = json_decode((string) file_get_contents($configFile), true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($config)) {
    return [
        'device_ips'   => [],
        'device_port'  => 4370,
        'devices'      => [],
    ];
}

$devices  = $config['devices'] ?? [];
$port     = $config['default_port'] ?? 4370;

return [
    'device_ips'  => array_column($devices, 'ip'),
    'device_port' => $port,
    'devices'     => $devices,
];
