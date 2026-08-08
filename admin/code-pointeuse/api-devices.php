<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

$configFile = __DIR__ . '/config/devices.json';

function loadConfig(): array {
    global $configFile;
    if (!file_exists($configFile)) {
        return ['default_port' => 4370, 'devices' => []];
    }
    $config = json_decode((string) file_get_contents($configFile), true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($config)) {
        return ['default_port' => 4370, 'devices' => []];
    }
    $config['default_port'] = $config['default_port'] ?? 4370;
    $config['devices'] = $config['devices'] ?? [];
    return $config;
}

function saveConfig(array $config): bool {
    global $configFile;
    return file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

function errorResponse(string $message): void {
    echo json_encode([
        'status'  => 'error',
        'message' => $message,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function successResponse(string $message, $data = null): void {
    $response = ['status' => 'success', 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// GET: list devices or get single device
if ($method === 'GET') {
    $config = loadConfig();

    if (isset($_GET['id'])) {
        $id     = (int) $_GET['id'];
        $device = null;
        foreach ($config['devices'] as $d) {
            if ((int) $d['id'] === $id) {
                $device = $d;
                break;
            }
        }
        if ($device === null) {
            errorResponse('Pointeuse introuvable.');
        }
        successResponse('Pointeuse trouvée.', $device);
    }

    successResponse('Liste des pointeuses.', $config['devices']);
}

// POST: add, edit, or delete
if ($method === 'POST') {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $config = loadConfig();

    // --- Add ---
    if ($action === 'add') {
        $name       = trim($_POST['name'] ?? '');
        $ip         = trim($_POST['ip'] ?? '');
        $port       = (int) ($_POST['port'] ?? 4370);
        $department = trim($_POST['department'] ?? '');

        if ($name === '' || $ip === '') {
            errorResponse('Le nom et l\'adresse IP sont obligatoires.');
        }
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            errorResponse('Adresse IP invalide.');
        }
        if ($port < 1 || $port > 65535) {
            errorResponse('Port invalide.');
        }

        foreach ($config['devices'] as $d) {
            if ($d['ip'] === $ip && (int) $d['port'] === $port) {
                errorResponse('Une pointeuse avec cette IP et ce port existe déjà.');
            }
        }

        $maxId   = 0;
        foreach ($config['devices'] as $d) {
            $maxId = max($maxId, (int) $d['id']);
        }
        $newId   = $maxId + 1;

        $config['devices'][] = [
            'id'         => $newId,
            'name'       => $name,
            'ip'         => $ip,
            'port'       => $port,
            'department' => $department !== '' ? $department : 'Général',
        ];

        if (!saveConfig($config)) {
            errorResponse('Impossible d\'enregistrer la configuration.');
        }
        successResponse('Pointeuse ajoutée.', $config['devices']);
    }

    // --- Edit ---
    if ($action === 'edit') {
        $id         = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
        $name       = trim($_POST['name'] ?? '');
        $ip         = trim($_POST['ip'] ?? '');
        $port       = (int) ($_POST['port'] ?? 4370);
        $department = trim($_POST['department'] ?? '');

        if ($name === '' || $ip === '') {
            errorResponse('Le nom et l\'adresse IP sont obligatoires.');
        }
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            errorResponse('Adresse IP invalide.');
        }
        if ($port < 1 || $port > 65535) {
            errorResponse('Port invalide.');
        }

        $found = false;
        foreach ($config['devices'] as $i => $d) {
            if ((int) $d['id'] === $id) {
                if ($d['ip'] !== $ip || (int) $d['port'] !== $port) {
                    foreach ($config['devices'] as $j => $other) {
                        if ($j !== $i && $other['ip'] === $ip && (int) $other['port'] === $port) {
                            errorResponse('Une autre pointeuse utilise déjà cette IP et ce port.');
                        }
                    }
                }
                $config['devices'][$i]['name']       = $name;
                $config['devices'][$i]['ip']         = $ip;
                $config['devices'][$i]['port']       = $port;
                $config['devices'][$i]['department'] = $department !== '' ? $department : 'Général';
                $found = true;
                break;
            }
        }

        if (!$found) {
            errorResponse('Pointeuse introuvable.');
        }

        if (!saveConfig($config)) {
            errorResponse('Impossible d\'enregistrer la configuration.');
        }
        successResponse('Pointeuse modifiée.', $config['devices']);
    }

    // --- Delete ---
    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
        $found = false;
        foreach ($config['devices'] as $i => $d) {
            if ((int) $d['id'] === $id) {
                array_splice($config['devices'], $i, 1);
                $found = true;
                break;
            }
        }

        if (!$found) {
            errorResponse('Pointeuse introuvable.');
        }

        if (!saveConfig($config)) {
            errorResponse('Impossible d\'enregistrer la configuration.');
        }
        successResponse('Pointeuse supprimée.', $config['devices']);
    }

    errorResponse('Action non reconnue.');
}

errorResponse('Méthode non autorisée.');
