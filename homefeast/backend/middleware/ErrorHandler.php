<?php
require_once __DIR__ . '/../services/ApiException.php';
require_once __DIR__ . '/../services/Logger.php';

/**
 * Turns every exception / unknown URL into a JSON response with the right status.
 *  - ApiException        -> its own status (400, 404, 503...) and message
 *  - database constraint -> 400 (e.g. duplicate or invalid foreign key)
 *  - anything else       -> 500 with a generic message; the real error goes to logs/error.log
 * The client never sees stack traces, SQL or file paths.
 */
class ErrorHandler
{
    public static function register(): void
    {
        Flight::map('error', [self::class, 'handleException']);
        Flight::map('notFound', [self::class, 'handleNotFound']);
    }

    public static function handleException(Throwable $e): void
    {
        if ($e instanceof ApiException) {
            $payload = ['error' => $e->getMessage()];
            if ($e->getErrors()) {
                $payload['details'] = $e->getErrors();
            }
            self::send($e->getStatus(), $payload);
            return;
        }

        $where = basename($e->getFile()) . ':' . $e->getLine();
        Logger::error(get_class($e) . ': ' . $e->getMessage() . " ($where)");

        if ($e instanceof PDOException && (string) $e->getCode() === '23000') {
            self::send(400, ['error' => 'The request conflicts with existing data (duplicate or invalid reference).']);
            return;
        }
        self::send(500, ['error' => 'Internal server error.']);
    }

    public static function handleNotFound(): void
    {
        self::send(404, ['error' => 'Route not found.']);
    }

    private static function send(int $status, array $payload): void
    {
        Flight::response()
            ->clear()
            ->status($status)
            ->header('Content-Type', 'application/json; charset=utf-8')
            ->write(json_encode($payload))
            ->send();
    }
}
