<?php

/**
 * Minimal file logger. Writes backend/logs/info.log and backend/logs/error.log
 * in the format:  [2026-12-01 14:05:12] INFO message
 * (Milestone 4 will add request logging on top of this.)
 */
class Logger
{
    public static function info(string $message): void
    {
        self::write('info.log', 'INFO', $message);
    }

    public static function error(string $message): void
    {
        self::write('error.log', 'ERROR', $message);
    }

    private static function write(string $file, string $level, string $message): void
    {
        $dir = __DIR__ . '/../logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $message = preg_replace('/\s+/', ' ', $message);   // one log entry = one line
        $line = sprintf("[%s] %s %s\n", date('Y-m-d H:i:s'), $level, $message);
        @file_put_contents($dir . '/' . $file, $line, FILE_APPEND | LOCK_EX);
    }
}
