<?php

$autoload = function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/src/';
    $prefixLength = strlen($prefix);

    if (strncmp($prefix, $class, $prefixLength) !== 0) {
        return;
    }

    $relativeClass = substr($class, $prefixLength);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
};

spl_autoload_register($autoload);

$config = require __DIR__ . '/config.php';
