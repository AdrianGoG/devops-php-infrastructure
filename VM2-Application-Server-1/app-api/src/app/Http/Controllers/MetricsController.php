<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\ApplicationRepository;
use App\Repositories\DeploymentRepository;
use App\Repositories\ServerRepository;
use LegacyTimer;

/**
 * Prometheus exposition endpoint.
 *
 * Prometheus scrapes this URL directly, which is what puts the infrastructure
 * registry on the Grafana dashboards alongside the node_exporter metrics.
 *
 * NOTE: the deployment counters come from Database::legacyDeploymentStats(),
 * whose query targets a table that no longer exists. Under PHP 7.4 the failure
 * is silent and the code falls back to counting rows; under PHP 8 the same call
 * raises an uncaught PDOException (MIGRATION.md, incompatibility #5).
 */
class MetricsController extends Controller
{
    /**
     * @param array<string, string> $parameters
     */
    public function show(Request $request, array $parameters = []): Response
    {
        unset($request, $parameters);

        $timer = new LegacyTimer();

        $servers = new ServerRepository($this->database());
        $applications = new ApplicationRepository($this->database());
        $deployments = new DeploymentRepository($this->database());

        $byStatus = $applications->countByStatus();

        // LEGACY: silent failure on 7.4, uncaught PDOException on PHP 8.0+.
        $legacyStats = $this->database()->legacyDeploymentStats();

        $deploymentTotal = $legacyStats !== null ? (int) $legacyStats['total'] : $deployments->count();
        $byResult = $deployments->countByResult();

        $lines = [];

        $lines[] = '# HELP api_up Whether the infrastructure registry API is answering.';
        $lines[] = '# TYPE api_up gauge';
        $lines[] = 'api_up 1';

        $lines[] = '# HELP api_php_version_info The PHP version this instance runs on.';
        $lines[] = '# TYPE api_php_version_info gauge';
        $lines[] = sprintf('api_php_version_info{version="%s"} 1', PHP_VERSION);

        $lines[] = '# HELP api_servers_total Servers registered in the inventory.';
        $lines[] = '# TYPE api_servers_total gauge';
        $lines[] = 'api_servers_total ' . $servers->count();

        $lines[] = '# HELP api_applications_total Applications registered in the inventory.';
        $lines[] = '# TYPE api_applications_total gauge';
        $lines[] = 'api_applications_total ' . $applications->count();

        $lines[] = '# HELP api_applications_by_status Applications grouped by their migration status.';
        $lines[] = '# TYPE api_applications_by_status gauge';

        foreach ($byStatus as $status => $total) {
            $lines[] = sprintf('api_applications_by_status{status="%s"} %d', $status, $total);
        }

        $lines[] = '# HELP api_deployments_total Deployments recorded by the pipeline.';
        $lines[] = '# TYPE api_deployments_total counter';
        $lines[] = 'api_deployments_total ' . $deploymentTotal;

        $lines[] = '# HELP api_deployments_by_result Deployments grouped by outcome.';
        $lines[] = '# TYPE api_deployments_by_result counter';

        foreach (DeploymentController::RESULTS as $result) {
            $lines[] = sprintf(
                'api_deployments_by_result{result="%s"} %d',
                $result,
                isset($byResult[$result]) ? $byResult[$result] : 0
            );
        }

        $lines[] = '# HELP api_deployment_duration_seconds_avg Average pipeline duration.';
        $lines[] = '# TYPE api_deployment_duration_seconds_avg gauge';
        $lines[] = 'api_deployment_duration_seconds_avg ' . $deployments->averageDuration();

        // Exposes the silent PHP 8 breakage of LegacyTimer as a metric, so the
        // regression is visible on the Grafana dashboard and not only in a log.
        $lines[] = '# HELP api_legacy_timer_initialised 1 when the legacy timer constructor ran.';
        $lines[] = '# TYPE api_legacy_timer_initialised gauge';
        $lines[] = 'api_legacy_timer_initialised ' . ($timer->isInitialised() ? '1' : '0');

        $lines[] = '# HELP api_request_duration_seconds Time spent building this response.';
        $lines[] = '# TYPE api_request_duration_seconds gauge';
        $lines[] = 'api_request_duration_seconds ' . $timer->elapsed();

        return Response::text(implode("\n", $lines) . "\n");
    }
}
