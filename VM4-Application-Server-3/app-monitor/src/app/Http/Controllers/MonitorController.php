<?php

namespace App\Http\Controllers;

use App\Services\EstateProbe;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * The monitoring dashboard and the Prometheus exposition endpoint.
 */
class MonitorController extends Controller
{
    public function index(EstateProbe $probe): View
    {
        $results = $probe->all();

        return view('monitor.index', [
            'results' => $results,
            'summary' => $probe->summarise($results),
        ]);
    }

    /**
     * Prometheus scrapes this URL, which is what puts the availability of the
     * whole estate on the Grafana dashboards.
     */
    public function metrics(EstateProbe $probe): Response
    {
        $results = $probe->all();
        $summary = $probe->summarise($results);

        $lines = [];

        $lines[] = '# HELP estate_applications_total Applications probed by app-monitor.';
        $lines[] = '# TYPE estate_applications_total gauge';
        $lines[] = 'estate_applications_total '.$summary['total'];

        $lines[] = '# HELP estate_applications_healthy Applications answering with a healthy status.';
        $lines[] = '# TYPE estate_applications_healthy gauge';
        $lines[] = 'estate_applications_healthy '.$summary['healthy'];

        $lines[] = '# HELP estate_applications_down Applications not answering, or answering with an error.';
        $lines[] = '# TYPE estate_applications_down gauge';
        $lines[] = 'estate_applications_down '.$summary['down'];

        $lines[] = '# HELP estate_application_up 1 when the application answers a healthy status, 0 otherwise.';
        $lines[] = '# TYPE estate_application_up gauge';

        foreach ($results as $result) {
            $lines[] = sprintf(
                'estate_application_up{application="%s",server="%s"} %d',
                $result['name'],
                $result['server'],
                $result['status'] === 'ok' ? 1 : 0
            );
        }

        $lines[] = '# HELP estate_application_response_milliseconds Time the health endpoint took to answer.';
        $lines[] = '# TYPE estate_application_response_milliseconds gauge';

        foreach ($results as $result) {
            $lines[] = sprintf(
                'estate_application_response_milliseconds{application="%s",server="%s"} %d',
                $result['name'],
                $result['server'],
                (int) $result['response_ms']
            );
        }

        $lines[] = '# HELP estate_application_php_version_info The PHP version each application reports.';
        $lines[] = '# TYPE estate_application_php_version_info gauge';

        foreach ($results as $result) {
            if ($result['php'] === null) {
                continue;
            }

            $lines[] = sprintf(
                'estate_application_php_version_info{application="%s",version="%s"} 1',
                $result['name'],
                $result['php']
            );
        }

        return response(implode("\n", $lines)."\n", 200)
            ->header('Content-Type', 'text/plain; version=0.0.4; charset=utf-8');
    }
}