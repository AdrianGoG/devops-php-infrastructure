<?php

namespace App\Http\Controllers;

use App\Services\RegistryClient;
use Illuminate\View\View;

/**
 * The single page of the dashboard: the state of the infrastructure, as
 * reported by app-api.
 */
class DashboardController extends Controller
{
    public function __invoke(RegistryClient $registry): View
    {
        $applications = $registry->applications();
        $servers = $registry->servers();
        $deployments = $registry->deployments();

        return view('dashboard', [
            'registryReachable' => $registry->isReachable(),
            'registryUrl' => $registry->baseUrl(),
            'servers' => $servers,
            'applications' => $applications,
            'deployments' => $deployments,
            'stats' => $this->stats($servers, $applications, $deployments),
        ]);
    }

    /**
     * The four numbers shown at the top of the page.
     *
     * @param array<int, array<string, mixed>> $servers
     * @param array<int, array<string, mixed>> $applications
     * @param array<int, array<string, mixed>> $deployments
     * @return array<string, int>
     */
    private function stats(array $servers, array $applications, array $deployments): array
    {
        $legacy = array_filter($applications, static fn (array $app): bool => ($app['status'] ?? null) === 'legacy');

        $failed = array_filter(
            $deployments,
            static fn (array $deployment): bool => in_array($deployment['result'] ?? null, ['failed', 'rolled_back'], true)
        );

        return [
            'servers' => count($servers),
            'applications' => count($applications),
            'legacy' => count($legacy),
            'failed_deployments' => count($failed),
        ];
    }
}
