<?php

namespace Elonphp\LaravelOcadminModules\Modules\SystemLog\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Elonphp\LaravelOcadminModules\Core\Controllers\Controller;
use Elonphp\LaravelOcadminModules\Modules\SystemLog\Services\LogService;

class LogController extends Controller
{
    public function __construct(
        protected LogService $logService
    ) {}

    /**
     * Display database logs.
     */
    public function database(Request $request): View
    {
        $logs = $this->logService->getDatabaseLogs(
            $request->get('level'),
            $request->get('search'),
            $request->integer('per_page', 50)
        );

        $levels = $this->logService->getLevels();

        return view('system-log::database', compact('logs', 'levels'));
    }

    /**
     * Display archived logs (file-based).
     */
    public function archived(Request $request): View
    {
        $files = $this->logService->getLogFiles();
        $selectedFile = $request->get('file');
        $logs = [];

        if ($selectedFile) {
            $logs = $this->logService->parseLogFile($selectedFile);
        }

        return view('system-log::archived', compact('files', 'logs', 'selectedFile'));
    }

    /**
     * Show a single log entry.
     */
    public function show(Request $request, int $id): View
    {
        $log = $this->logService->find($id);

        if (!$log) {
            abort(404);
        }

        return view('system-log::show', compact('log'));
    }

    /**
     * Delete old logs.
     */
    public function cleanup(Request $request)
    {
        $days = $request->integer('days', 30);
        $deleted = $this->logService->cleanup($days);

        return redirect(ocadmin_route('system.logs.database'))
            ->with('success', "Deleted {$deleted} old log entries.");
    }
}
