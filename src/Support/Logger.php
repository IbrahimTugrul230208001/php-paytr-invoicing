<?php

namespace App\Support;

use DateTimeImmutable;
use RuntimeException;

class Logger
{
    private string $logDirectory;

    public function __construct(string $logDirectory)
    {
        $this->logDirectory = rtrim($logDirectory, '/');
    }

    public function info(string $message, array $context = []): void
    {
        $this->writeLog('INFO', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->writeLog('ERROR', $message, $context);
    }

    private function writeLog(string $level, string $message, array $context): void
    {
        if (!is_dir($this->logDirectory) && !mkdir($concurrentDirectory = $this->logDirectory, 0777, true) && !is_dir($concurrentDirectory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }

        $timestamp = (new DateTimeImmutable())->format(DateTimeImmutable::ATOM);
        $contextJson = $context ? json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';

        $line = sprintf('[%s] %s: %s %s%s', $timestamp, $level, $message, $contextJson, PHP_EOL);
        file_put_contents($this->logDirectory . '/app.log', $line, FILE_APPEND);
    }
}