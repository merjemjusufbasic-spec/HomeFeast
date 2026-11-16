<?php

class Config
{
    public static function DB_HOST()     { return self::get_env('DB_HOST', '127.0.0.1'); }
    public static function DB_PORT()     { return self::get_env('DB_PORT', 3306); }
    public static function DB_NAME()     { return self::get_env('DB_NAME', 'homefeast'); }
    public static function DB_USER()     { return self::get_env('DB_USER', 'root'); }
    public static function DB_PASSWORD() { return self::get_env('DB_PASSWORD', ''); }

    // External API: TheMealDB (https://www.themealdb.com/api.php). "1" is the free public test key.
    public static function MEALDB_BASE_URL()      { return self::get_env('MEALDB_BASE_URL', 'https://www.themealdb.com/api/json/v1'); }
    public static function MEALDB_API_KEY()       { return self::get_env('MEALDB_API_KEY', '1'); }
    // Seconds to wait for any external API before giving up.
    public static function EXTERNAL_API_TIMEOUT() { return (int) self::get_env('EXTERNAL_API_TIMEOUT', 5); }

    /**
     * Environment variables (set on the server) win over the default values above.
     */
    public static function get_env($name, $default)
    {
        $value = getenv($name);
        return ($value !== false && trim($value) !== '') ? $value : $default;
    }
}
