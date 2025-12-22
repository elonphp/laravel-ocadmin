<?php

namespace Elonphp\LaravelOcadminModules\Modules\SystemSetting;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Elonphp\LaravelOcadminModules\Core\Controllers\Controller;

class SystemSettingController extends Controller
{
    public function __construct(
        protected SystemSettingService $settingService
    ) {
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
                'text' => __('system-setting::menu.system_management'),
                'href' => 'javascript:void(0)',
            ],
            (object)[
                'text' => __('system-setting::menu.settings'),
                'href' => ocadmin_route('settings.index'),
            ],
        ];
    }

    /**
     * Display setting listing page (initial load).
     */
    public function index(Request $request): View
    {
        $data = [
            'list' => $this->getList($request),
            'types' => $this->settingService->getTypes(),
            'breadcrumbs' => $this->breadcrumbs,
        ];

        return view('system-setting::index', $data);
    }

    /**
     * AJAX entry point for list refresh.
     */
    public function list(Request $request): string
    {
        return $this->getList($request);
    }

    /**
     * Core list query logic.
     */
    protected function getList(Request $request): string
    {
        $model = config('ocadmin.models.setting', Setting::class);
        $query = $model::query();

        // Filter by code
        if ($request->filled('filter_code')) {
            $query->where('code', 'like', '%' . $request->filter_code . '%');
        }

        // Filter by group
        if ($request->filled('filter_group')) {
            $query->where('group', 'like', '%' . $request->filter_group . '%');
        }

        // Filter by type
        if ($request->filled('filter_type')) {
            $query->where('type', $request->filter_type);
        }

        // Sorting
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'asc');
        $allowedSorts = ['id', 'code', 'group', 'type', 'created_at'];

        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $order === 'asc' ? 'asc' : 'desc');
        }

        // Pagination
        $perPage = $request->integer('per_page', 10);
        $items = $query->paginate($perPage);

        // Build action URL for sorting links
        $action = ocadmin_route('settings.list') . '?' . http_build_query($request->except(['sort', 'order']));

        return view('system-setting::list', compact('items', 'action'))->render();
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        $data = [
            'setting' => $this->settingService->withDefaults(),
            'types' => $this->settingService->getTypes(),
            'breadcrumbs' => $this->breadcrumbs,
        ];

        return view('system-setting::form', $data);
    }

    /**
     * Store a new setting.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255',
            'locale' => 'nullable|string|max:10',
            'group' => 'nullable|string|max:100',
            'content' => 'nullable|string',
            'type' => 'required|string',
            'note' => 'nullable|string|max:255',
        ]);

        // Check for duplicate
        if ($this->settingService->exists($validated['code'], $validated['locale'] ?? '')) {
            return response()->json([
                'success' => false,
                'message' => __('system-setting::setting.duplicate_code'),
                'errors' => ['code' => [__('system-setting::setting.duplicate_code')]],
            ], 422);
        }

        try {
            DB::transaction(function () use ($validated) {
                $this->settingService->create($validated);
            });

            return response()->json([
                'success' => true,
                'message' => __('ocadmin::messages.created'),
                'redirect' => ocadmin_route('settings.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('ocadmin::messages.error'),
            ], 500);
        }
    }

    /**
     * Show edit form.
     */
    public function edit(int $id): View
    {
        $data = [
            'setting' => $this->settingService->find($id),
            'types' => $this->settingService->getTypes(),
            'breadcrumbs' => $this->breadcrumbs,
        ];

        return view('system-setting::form', $data);
    }

    /**
     * Update a setting.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255',
            'locale' => 'nullable|string|max:10',
            'group' => 'nullable|string|max:100',
            'content' => 'nullable|string',
            'type' => 'required|string',
            'note' => 'nullable|string|max:255',
        ]);

        // Check for duplicate (excluding self)
        if ($this->settingService->exists($validated['code'], $validated['locale'] ?? '', $id)) {
            return response()->json([
                'success' => false,
                'message' => __('system-setting::setting.duplicate_code'),
                'errors' => ['code' => [__('system-setting::setting.duplicate_code')]],
            ], 422);
        }

        try {
            DB::transaction(function () use ($id, $validated) {
                $this->settingService->update($id, $validated);
            });

            return response()->json([
                'success' => true,
                'message' => __('ocadmin::messages.updated'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('ocadmin::messages.error'),
            ], 500);
        }
    }

    /**
     * Delete a setting.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            DB::transaction(function () use ($id) {
                $this->settingService->delete($id);
            });

            return response()->json([
                'success' => true,
                'message' => __('ocadmin::messages.deleted'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('ocadmin::messages.error'),
            ], 500);
        }
    }

    /**
     * Delete multiple settings.
     */
    public function destroyMultiple(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $this->settingService->deleteMultiple($validated['ids']);
            });

            return response()->json([
                'success' => true,
                'message' => __('ocadmin::messages.deleted'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('ocadmin::messages.error'),
            ], 500);
        }
    }
}
