<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Request;
use App\Core\Response;
use LegacyTimer;
use Throwable;

/**
 * Health check of the service.
 *
 * The contract mirrors the one exposed by app-company-website, so the Python
 * monitoring utility can treat every application in the estate the same way.
 *
 * Two details matter for the migration story:
 *   - the endpoint reports the PHP version the container actually runs on;
 *   - it reports whether LegacyTimer initialised itself, which is the only
 *     visible symptom of the silent PHP 8 breakage documented as
 *     incompatibility #4 in MIGRATION.md.
 */
class HealthController extends Controller
{
    /**
     * @param array<string, string> $parameters
     */
    public function show(Request $request, array $parameters = []): Response
    {
        unset($request, $parameters);

        $timer = new LegacyTimer();

        $database = 'ok';
        $applications = null;

        try {
            if ($this->database()->isReady()) {
                $applications = $this->database()->scalar('SELECT COUNT(*) FROM applications', [], -1);
            } else {
                // MySQL answers but the registry has never been migrated.
                $database = 'degraded';
            }
        } catch (Throwable $exception) {
            $database = 'unavailable';

            $this->logger()->error('health check could not reach the database', [
                'error' => $exception->getMessage(),
            ]);
        }

        $healthy = $database === 'ok';

        return Response::json([
            'status' => $healthy ? 'ok' : 'degraded',
            'application' => 'app-api',
            'server' => 'VM2-Application-Server-1',
            'php' => PHP_VERSION,
            'environment' => (string) $this->config()->get('APP_ENV', 'production'),
            'database' => $database,
            'applications_registered' => $applications !== null && $applications >= 0 ? $applications : null,
            'legacy_timer_initialised' => $timer->isInitialised(),
            'checked_at' => date('c'),
        ], $healthy ? 200 : 503);
    }
}
