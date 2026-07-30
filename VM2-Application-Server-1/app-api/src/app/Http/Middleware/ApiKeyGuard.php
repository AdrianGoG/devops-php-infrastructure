<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Core\Config;
use App\Core\Request;
use App\Exceptions\HttpException;

/**
 * Protects the write endpoints of the API.
 *
 * Reads are public - the Python monitor, Prometheus and the dashboard all need
 * them - but writing into the deployment log requires the shared key that
 * Jenkins holds as a credential.
 */
class ApiKeyGuard
{
    const HEADER = 'X-API-Key';

    /**
     * @throws HttpException 401 when the key is missing or wrong,
     *                       503 when the server has no key configured.
     */
    public static function check(Request $request, Config $config): void
    {
        $expected = (string) $config->get('API_KEY', '');

        if ($expected === '') {
            throw new HttpException('The API key is not configured on this instance.', 503);
        }

        $provided = (string) $request->header(self::HEADER, '');

        if ($provided === '') {
            throw new HttpException('The ' . self::HEADER . ' header is required for this endpoint.', 401);
        }

        // hash_equals keeps the comparison constant time.
        if (!hash_equals($expected, $provided)) {
            throw new HttpException('The provided API key is not valid.', 401);
        }
    }
}
