<?php

/**
 * Lets the frontend (another port/domain, e.g. Live Server on :5502) call the API.
 * Browsers first send an OPTIONS "preflight" request; we answer it and stop.
 */
class CorsMiddleware
{
    public static function handle(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}
