<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * The pages of the presentation site.
 *
 * The content is not hard-coded in the views but read from config/project.php,
 * so updating the infrastructure (new applications, PHP versions, ports) never
 * requires touching the markup.
 */
class PageController extends Controller
{
    /**
     * Home page: project scope, key numbers and the delivery flow.
     */
    public function home(): View
    {
        return view('pages.home');
    }

    /**
     * The servers of the infrastructure and the applications each one hosts.
     */
    public function infrastructure(): View
    {
        return view('pages.infrastructure');
    }

    /**
     * The Jenkins pipeline stages and the legacy code migration process.
     */
    public function pipeline(): View
    {
        return view('pages.pipeline');
    }

    /**
     * The role of every DevOps technology used in the project.
     */
    public function technologies(): View
    {
        return view('pages.technologies');
    }

    /**
     * The monitoring and logging solution of the infrastructure.
     */
    public function monitoring(): View
    {
        return view('pages.monitoring');
    }

    /**
     * Health endpoint consumed by the Python monitoring utility and by the
     * smoke test stage of the Jenkins pipeline.
     *
     * It answers with 200 plus the identity of the instance, so an automated
     * check can confirm not only that the application is alive, but also which
     * PHP version it ended up running after an upgrade.
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'application' => 'app-company-website',
            'server' => 'VM2-Application-Server-1',
            'php' => PHP_VERSION,
            'laravel' => app()->version(),
            'environment' => app()->environment(),
            'checked_at' => now()->toIso8601String(),
        ]);
    }
}
