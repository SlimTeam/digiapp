<?php
echo json_encode(['PHP' => PHP_VERSION, 'sockets' => extension_loaded('sockets'), 'pdo' => extension_loaded('pdo')]);
