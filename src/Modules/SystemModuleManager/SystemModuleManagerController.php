<?php

namespace Elonphp\LaravelOcadminModules\Modules\SystemModuleManager;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Elonphp\LaravelOcadminModules\Core\Controllers\Controller;

class SystemModuleManagerController extends Controller
{
    protected SystemModuleManagerService $service;

    public function __construct(SystemModuleManagerService $service)
    {
        $this->service = $service;
        $this->setBreadcrumbs();
    }

    protected function setBreadcrumbs(): void
    {
        $this->breadcrumbs = [
            (object)[
                'text' => __('ocadmin::common.dashboard'),
                'href' => ocadmin_route('dashboard'),
            ],
            (object)[
                'text' => __('ocadmin::menu.system'),
                'href' => 'javascript:void(0)',
            ],
            (object)[
                'text' => __('system-module-manager::menu.modules'),
                'href' => ocadmin_route('modules.index'),
            ],
        ];
    }

    /**
     * Display module list.
     */
    public function index(): View
    {
        $modules = $this->service->discoverModules();

        // Sort by priority, then by name
        uasort($modules, function ($a, $b) {
            if ($a['priority'] !== $b['priority']) {
                return $a['priority'] <=> $b['priority'];
            }
            return $a['name'] <=> $b['name'];
        });

        $data = [
            'modules' => $modules,
            'breadcrumbs' => $this->breadcrumbs,
        ];

        return view('system-module-manager::index', $data);
    }

    /**
     * Show module details and install options.
     */
    public function show(string $alias): View
    {
        $module = $this->service->getModule($alias);

        if (!$module) {
            return redirect()->route('ocadmin.modules.index')
                ->with('error', __('system-module-manager::messages.module_not_found'));
        }

        $data = [
            'module' => $module,
            'breadcrumbs' => $this->breadcrumbs,
        ];

        return view('system-module-manager::show', $data);
    }

    /**
     * Show install confirmation page.
     */
    public function installForm(string $alias): View
    {
        $module = $this->service->getModule($alias);

        if (!$module) {
            return redirect()->route('ocadmin.modules.index')
                ->with('error', __('system-module-manager::messages.module_not_found'));
        }

        if ($module['installed']) {
            return redirect()->route('ocadmin.modules.index')
                ->with('info', __('system-module-manager::messages.already_installed'));
        }

        $data = [
            'module' => $module,
            'checks' => $this->service->runPreInstallChecks($module),
            'breadcrumbs' => $this->breadcrumbs,
        ];

        return view('system-module-manager::install', $data);
    }

    /**
     * Install a module.
     */
    public function install(Request $request, string $alias)
    {
        $module = $this->service->getModule($alias);

        if (!$module) {
            return redirect()->route('ocadmin.modules.index')
                ->with('error', __('system-module-manager::messages.module_not_found'));
        }

        $options = [
            'use_existing_table' => $request->boolean('use_existing_table'),
            'run_seeders' => $request->boolean('run_seeders'),
        ];

        $result = $this->service->install($alias, $options);

        if ($result['success']) {
            return redirect()->route('ocadmin.modules.index')
                ->with('success', __('system-module-manager::messages.installed_success'));
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Enable a module.
     */
    public function enable(string $alias)
    {
        $result = $this->service->enable($alias);

        if ($result['success']) {
            return redirect()->route('ocadmin.modules.index')
                ->with('success', __('system-module-manager::messages.enabled_success'));
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Disable a module.
     */
    public function disable(string $alias)
    {
        $result = $this->service->disable($alias);

        if ($result['success']) {
            return redirect()->route('ocadmin.modules.index')
                ->with('success', __('system-module-manager::messages.disabled_success'));
        }

        return back()->with('error', $result['message']);
    }
}
