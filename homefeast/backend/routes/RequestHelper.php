<?php
require_once __DIR__ . '/../services/ApiException.php';

/** Small helpers so route handlers stay short. */
class RequestHelper
{
    /** JSON body of the request as an array. Invalid JSON -> 400. */
    public static function body(): array
    {
        $raw = Flight::request()->getBody();
        if (trim((string) $raw) === '') {
            return [];
        }
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            throw new ApiException('Request body must be a valid JSON object.', 400);
        }
        return $data;
    }

    /** Query-string value (?q=pasta) or null when missing/empty. */
    public static function query(string $name): ?string
    {
        $value = $_GET[$name] ?? null;
        return (is_string($value) && trim($value) !== '') ? trim($value) : null;
    }

    /** Integer query-string value (?user_id=2) or null. Non-numeric -> 400. */
    public static function intQuery(string $name): ?int
    {
        $value = self::query($name);
        if ($value === null) {
            return null;
        }
        if (!ctype_digit($value)) {
            throw new ApiException("Query parameter \"$name\" must be a positive number.", 400);
        }
        return (int) $value;
    }
}
