<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/ApiException.php';
require_once __DIR__ . '/Logger.php';

/**
 * External API integration: TheMealDB (https://www.themealdb.com/api.php).
 *
 * - Every call has a connect timeout and a total timeout (Config::EXTERNAL_API_TIMEOUT).
 * - Every call is written to logs/info.log; every failure to logs/error.log.
 * - Failures never crash the app: the caller gets a friendly 503 message
 *   and the rest of HomeFeast keeps working with its own database.
 * - The API key lives in config.php and is NEVER written to the logs.
 */
class MealDbService
{
    private const UNAVAILABLE = 'Recipe suggestions are temporarily unavailable. Please try again later.';

    /** Search meals by name. Returns [] when nothing matches. */
    public function search(string $query): array
    {
        $query = trim($query);
        if ($query === '' || mb_strlen($query) > 100) {
            throw new ApiException('Query parameter "q" is required (max 100 characters).', 400);
        }
        $data = $this->request('search.php', ['s' => $query]);
        return array_map([$this, 'normalize'], $data['meals'] ?? []);
    }

    /** One random meal ("Meal of the day"). */
    public function random(): array
    {
        $data = $this->request('random.php');
        if (empty($data['meals'][0])) {
            Logger::error('External API TheMealDB random.php returned no meal');
            throw new ApiException(self::UNAVAILABLE, 503);
        }
        return $this->normalize($data['meals'][0]);
    }

    /** One meal by its TheMealDB id. */
    public function getById(string $id): array
    {
        if (!ctype_digit($id)) {
            throw new ApiException('Meal id must be a number.', 400);
        }
        $data = $this->request('lookup.php', ['i' => $id]);
        if (empty($data['meals'][0])) {
            throw new ApiException('Meal not found.', 404);
        }
        return $this->normalize($data['meals'][0]);
    }

    /**
     * Calls TheMealDB with cURL and returns the decoded JSON.
     * @throws ApiException 503 when the API cannot be reached or answers badly.
     */
    private function request(string $endpoint, array $params = []): array
    {
        $url = rtrim(Config::MEALDB_BASE_URL(), '/') . '/' . rawurlencode(Config::MEALDB_API_KEY())
             . '/' . $endpoint . ($params ? '?' . http_build_query($params) : '');

        $started = microtime(true);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT        => Config::EXTERNAL_API_TIMEOUT(),
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);
        $body    = curl_exec($ch);
        $status  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno   = curl_errno($ch);
        $error   = curl_error($ch);
        curl_close($ch);
        $ms = (int) round((microtime(true) - $started) * 1000);

        // 1) network problem or timeout
        if ($body === false) {
            Logger::error("External API TheMealDB GET $endpoint failed: curl error $errno ($error) after {$ms}ms");
            throw new ApiException(self::UNAVAILABLE, 503);
        }
        // 2) the API answered with an error status
        if ($status !== 200) {
            Logger::error("External API TheMealDB GET $endpoint failed: HTTP $status after {$ms}ms");
            throw new ApiException(self::UNAVAILABLE, 503);
        }
        // 3) the API answered with something that is not JSON
        $data = json_decode($body, true);
        if (!is_array($data)) {
            Logger::error("External API TheMealDB GET $endpoint failed: response is not valid JSON ({$ms}ms)");
            throw new ApiException(self::UNAVAILABLE, 503);
        }

        Logger::info("External API TheMealDB GET $endpoint status=$status {$ms}ms");
        return $data;
    }

    /**
     * TheMealDB returns strIngredient1..20 / strMeasure1..20 as flat fields.
     * Convert one meal into a clean shape the frontend can use directly.
     */
    private function normalize(array $meal): array
    {
        $ingredients = [];
        for ($i = 1; $i <= 20; $i++) {
            $name = trim((string) ($meal["strIngredient$i"] ?? ''));
            if ($name === '') {
                continue;
            }
            $ingredients[] = [
                'name'    => $name,
                'measure' => trim((string) ($meal["strMeasure$i"] ?? '')),
            ];
        }
        return [
            'id'           => (string) ($meal['idMeal'] ?? ''),
            'title'        => $meal['strMeal'] ?? '',
            'category'     => $meal['strCategory'] ?? null,
            'area'         => $meal['strArea'] ?? null,
            'instructions' => $meal['strInstructions'] ?? null,
            'thumbnail'    => $meal['strMealThumb'] ?? null,
            'youtube'      => !empty($meal['strYoutube']) ? $meal['strYoutube'] : null,
            'source'       => !empty($meal['strSource']) ? $meal['strSource'] : null,
            'ingredients'  => $ingredients,
        ];
    }
}
