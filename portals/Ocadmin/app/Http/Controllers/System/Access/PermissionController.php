<?php

namespace Portals\Ocadmin\Http\Controllers\System\Access;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Helpers\Classes\OrmHelper;
use Portals\Ocadmin\Http\Controllers\Controller;
use Portals\Ocadmin\Services\System\Access\PermissionService;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct(private PermissionService $permissionService)
    {
        parent::__construct();

        $this->getLang(['common', 'system/access/permission']);
    }

    protected function setBreadcrumbs(): void
    {
        $this->breadcrumbs = [
            (object)[
                'text' => $this->lang->text_home,
                'href' => route('lang.ocadmin.dashboard'),
            ],
            (object)[
                'text' => $this->lang->text_system,
                'href' => 'javascript:void(0)',
            ],
            (object)[
                'text' => $this->lang->text_access,
                'href' => 'javascript:void(0)',
            ],
            (object)[
                'text' => $this->lang->heading_title,
                'href' => route('lang.ocadmin.system.access.permission.index'),
            ],
        ];
    }

    /**
     * 列表頁面 - 完整頁面渲染
     */
    public function index(Request $request): View
    {
        $data['lang'] = $this->lang;
        $data['list'] = $this->getList($request);
        $data['breadcrumbs'] = $this->breadcrumbs;

        return view('ocadmin::system.access.permission.index', $data);
    }

    /**
     * AJAX 請求入口 - 僅返回表格 HTML
     */
    public function list(Request $request): string
    {
        return $this->getList($request);
    }

    /**
     * 核心查詢邏輯 - 處理資料查詢並渲染表格部分
     */
    protected function getList(Request $request): string
    {
        $query = Permission::query();
        $filter_data = $request->all();

        OrmHelper::prepare($query, $filter_data);

        // 關鍵字查詢
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                OrmHelper::filterOrEqualColumn($q, 'filter_name', $search);
                $q->orWhere(function ($subQ) use ($search) {
                    OrmHelper::filterOrEqualColumn($subQ, 'filter_title', $search);
                });
            });
        }

        // 父層篩選
        if ($request->has('equal_parent_id')) {
            if ($request->equal_parent_id === '0') {
                $query->whereNull('parent_id'); // OrmHelper 無法處理上一層是 null 的情況，所以用本段處理
            } elseif ($request->equal_parent_id !== '*') {
                $query->where('parent_id', $request->equal_parent_id);
            }
        }

        // 預設排序
        $filter_data['sort'] = $request->get('sort', 'sort_order');
        $filter_data['order'] = $request->get('order', 'asc');

        $permissions = OrmHelper::getResult($query, $filter_data);
        $permissions->withPath(route('lang.ocadmin.system.access.permission.list'));

        // 取得所有父層選項（用於篩選）
        $parentOptions = Permission::whereNull('parent_id')
            ->orderBy('sort_order')
            ->get(['id', 'name', 'title']);

        $url = $this->buildUrlParams($request);

        $data['lang'] = $this->lang;
        $data['permissions'] = $permissions;
        $data['parentOptions'] = $parentOptions;
        $data['action'] = route('lang.ocadmin.system.access.permission.list') . $url;
        $data['url_params'] = $url;

        return view('ocadmin::system.access.permission.list', $data)->render();
    }

    /**
     * 新增頁面
     */
    public function create()
    {
        $parentOptions = $this->permissionService->getParentOptions();

        return view('ocadmin::system.access.permission.form', [
            'lang' => $this->lang,
            'permission' => new Permission(),
            'parentOptions' => $parentOptions,
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    /**
     * 儲存新增 (AJAX)
     */
    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'name'        => 'required|string|max:100|unique:permissions,name',
            'guard_name'  => 'required|string|max:50',
            'title'       => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'parent_id'   => 'nullable|integer|exists:permissions,id',
            'sort_order'  => 'integer|min:0',
            'type'        => 'required|in:menu,action',
        ]);

        if ($validator->fails()) {
            $errors = [];
            foreach ($validator->errors()->toArray() as $field => $messages) {
                $errors[$field] = $messages[0];
            }
            return response()->json([
                'error_warning' => $validator->errors()->first(),
                'errors' => $errors,
            ]);
        }

        $validated = $validator->validated();
        $permission = DB::transaction(fn () => $this->permissionService->create($validated));

        return response()->json([
            'success' => $this->lang->text_add_success,
            'redirect_url' => route('lang.ocadmin.system.access.permission.edit', $permission->id),
            'form_action' => route('lang.ocadmin.system.access.permission.update', $permission->id),
        ]);
    }

    /**
     * 編輯頁面
     */
    public function edit(int $id)
    {
        $permission = Permission::findOrFail($id);
        $parentOptions = $this->permissionService->getParentOptions($id);

        return view('ocadmin::system.access.permission.form', [
            'lang' => $this->lang,
            'permission' => $permission,
            'parentOptions' => $parentOptions,
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    /**
     * 儲存編輯 (AJAX)
     */
    public function update(Request $request, int $id)
    {
        $permission = Permission::findOrFail($id);

        $validator = validator($request->all(), [
            'name'        => 'required|string|max:100|unique:permissions,name,' . $id,
            'guard_name'  => 'required|string|max:50',
            'title'       => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'parent_id'   => 'nullable|integer|exists:permissions,id',
            'sort_order'  => 'integer|min:0',
            'type'        => 'required|in:menu,action',
        ]);

        if ($validator->fails()) {
            $errors = [];
            foreach ($validator->errors()->toArray() as $field => $messages) {
                $errors[$field] = $messages[0];
            }
            return response()->json([
                'error_warning' => $validator->errors()->first(),
                'errors' => $errors,
            ]);
        }

        $validated = $validator->validated();

        // 防止設定自己為父層
        if (isset($validated['parent_id']) && $validated['parent_id'] == $id) {
            return response()->json([
                'error_warning' => $this->lang->error_parent_self,
                'errors' => ['parent_id' => $this->lang->error_parent_self],
            ]);
        }

        DB::transaction(fn () => $this->permissionService->update($permission, $validated));

        return response()->json([
            'success' => $this->lang->text_edit_success,
        ]);
    }

    /**
     * 刪除
     */
    public function destroy(int $id)
    {
        $permission = Permission::findOrFail($id);

        // 檢查是否有子權限
        if (Permission::where('parent_id', $id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_has_children,
            ]);
        }

        DB::transaction(fn () => $this->permissionService->delete($permission));

        return response()->json(['success' => true]);
    }

    /**
     * 批次刪除
     */
    public function batchDelete(Request $request)
    {
        $ids = $request->input('selected', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => '請選擇要刪除的項目']);
        }

        // 檢查是否有子權限
        $hasChildren = Permission::whereIn('parent_id', $ids)->exists();
        if ($hasChildren) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_has_children,
            ]);
        }

        DB::transaction(fn () => $this->permissionService->batchDelete($ids));

        return response()->json(['success' => true]);
    }

    /**
     * 取得所有權限（用於 select2 下拉選單）
     */
    public function all(Request $request)
    {
        $query = Permission::query()
            ->orderBy('sort_order')
            ->orderBy('name');

        // 可選：只取特定類型
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $permissions = $query->get(['id', 'name', 'title', 'type', 'parent_id']);

        return response()->json($permissions);
    }

    /**
     * 取得樹狀結構（用於選單）
     */
    public function tree()
    {
        $permissions = $this->permissionService->getTree();

        return response()->json($permissions);
    }

}
