<?php
require_once __DIR__ . '/../services/ApiException.php';

/**
 * API documentation:
 *   GET /openapi.json  the OpenAPI spec, generated from the annotations in routes/*.php
 *   GET /docs          Swagger UI page that renders that spec
 *   GET /              small JSON "hello" with links
 */
class DocsRoutes
{
    public static function register(): void
    {
        Flight::route('GET /',             [self::class, 'index']);
        Flight::route('GET /openapi.json', [self::class, 'spec']);
        Flight::route('GET /docs',         [self::class, 'swagger']);
    }

    public static function index(): void
    {
        $base = self::baseUrl();
        Flight::json([
            'name'    => 'HomeFeast API',
            'version' => '1.0.0',
            'docs'    => $base . '/docs',
            'openapi' => $base . '/openapi.json',
        ]);
    }

    public static function spec(): void
    {
        $generator = new \OpenApi\Generator();
        // swagger-php reports problems as PHP warnings; keep them out of the JSON output.
        if (class_exists('\Psr\Log\NullLogger') && method_exists($generator, 'setLogger')) {
            $generator->setLogger(new \Psr\Log\NullLogger());
        }
        $openapi = $generator->generate([__DIR__], null, false);   // scan this routes/ folder
        if ($openapi === null) {
            // swagger-php found no annotations: it cannot read @OA docblocks without doctrine/annotations
            throw new ApiException('OpenAPI spec could not be generated. Run "composer require doctrine/annotations" in the backend folder.', 500);
        }
        $spec = json_decode($openapi->toJson(), true);

        // Point "Try it out" at whatever address the API is running on.
        $spec['servers'] = [['url' => self::baseUrl(), 'description' => 'This server']];
        Flight::json($spec);
    }

    public static function swagger(): void
    {
        $specUrl = self::baseUrl() . '/openapi.json';
        echo '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>HomeFeast API docs</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.17.14/swagger-ui.css">
</head>
<body>
  <div id="swagger-ui"></div>
  <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.17.14/swagger-ui-bundle.js"></script>
  <script>
    window.onload = function () {
      window.ui = SwaggerUIBundle({ url: ' . json_encode($specUrl) . ', dom_id: "#swagger-ui" });
    };
  </script>
</body>
</html>';
    }

    /** e.g. http://localhost:8000 or https://example.com/homefeast/backend */
    private static function baseUrl(): string
    {
        $https  = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $folder = rtrim(Flight::request()->base, '/\\');
        return ($https ? 'https' : 'http') . '://' . $host . $folder;
    }
}
