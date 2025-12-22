<?php

namespace Elonphp\LaravelOcadminModules\Modules\User;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Elonphp\LaravelOcadminModules\Core\Controllers\Controller;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
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
                'text' => __('user::menu.account_management'),
                'href' => 'javascript:void(0)',
            ],
            (object)[
                'text' => __('user::menu.users'),
                'href' => ocadmin_route('users.index'),
            ],
        ];
    }

    /**
     * Display user listing page (initial load).
     */
    public function index(Request $request): View
    {
        $data = [
            'list' => $this->getList($request),
            'breadcrumbs' => $this->breadcrumbs,
        ];

        return view('user::index', $data);
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
        $model = config('ocadmin.models.user', \App\Models\User::class);
        $query = $model::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');
        $allowedSorts = ['id', 'name', 'email', 'created_at'];

        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $order === 'asc' ? 'asc' : 'desc');
        }

        // Pagination
        $perPage = $request->integer('per_page', 10);
        $items = $query->paginate($perPage);

        // Build action URL for sorting links
        $action = ocadmin_route('users.list') . '?' . http_build_query($request->except(['sort', 'order']));

        return view('user::list', compact('items', 'action'))->render();
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        $data = [
            'user' => $this->userService->withDefaults(),
            'breadcrumbs' => $this->breadcrumbs,
        ];

        return view('user::form', $data);
    }

    /**
     * Store a new user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $this->userService->create($validated);
            });

            return response()->json([
                'success' => true,
                'message' => __('ocadmin::messages.created'),
                'redirect' => ocadmin_route('users.index'),
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
            'user' => $this->userService->find($id),
            'breadcrumbs' => $this->breadcrumbs,
        ];

        return view('user::form', $data);
    }

    /**
     * Update a user.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
        ];

        // Password is optional on update
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|confirmed';
        }

        $validated = $request->validate($rules);

        try {
            DB::transaction(function () use ($id, $validated) {
                $this->userService->update($id, $validated);
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
     * Delete a user.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            DB::transaction(function () use ($id) {
                $this->userService->delete($id);
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
     * Delete multiple users.
     */
    public function destroyMultiple(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $this->userService->deleteMultiple($validated['ids']);
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
