<?php
spl_autoload_register(function ($class) {
    $prefix = 'CodingLibs\\ZktecoPhp\\';
    $baseDir = __DIR__ . '/src/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relative  = substr($class, strlen($prefix));
    $file      = $baseDir . str_replace('\\', '/', $relative) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
