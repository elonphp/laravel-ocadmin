<?php

namespace App\Portals\Ocadmin\Core\Controllers\System;

use App\Enums\System\SettingType;
use App\Helpers\Classes\OrmHelper;
use App\Models\System\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;

class SettingController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['common', 'system/setting'];
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
                'text' => $this->lang->heading_title,
                'href' => route('lang.ocadmin.system.setting.index'),
            ],
        ];
    }

    /**
     * 列表頁（初始載入）
     */
    public function index(Request $request): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['list'] = $this->getList($request);
        $data['types'] = SettingType::cases();

        return view('ocadmin::system.setting.index', $data);
    }

    /**
     * AJAX 入口（列表刷新）
     */
    public function list(Request $request): string
    {
        return $this->getList($request);
    }

    /**
     * 核心查詢邏輯
     */
    protected function getList(Request $request): string
    {
        $query = Setting::query();
        $filter_data = $this->filterData($request, ['equal_type']);

        // 預設排序
        $filter_data['sort'] = $request->query('sort', 'id');
        $filter_data['order'] = $request->query('order', 'asc');

        // search 關鍵字查詢
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                OrmHelper::filterOrEqualColumn($q, 'filter_code', $search);
                $q->orWhere(function ($q2) use ($search) {
                    OrmHelper::filterOrEqualColumn($q2, 'filter_group', $search);
                });
            });

            unset($filter_data['search'], $filter_data['filter_code'], $filter_data['filter_group']);
        }

        // OrmHelper 自動處理 filter_*, equal_* 及排序
        OrmHelper::prepare($query, $filter_data);

        // 分頁結果
        $settings = OrmHelper::getResult($query, $filter_data);
        $settings->withPath(route('lang.ocadmin.system.setting.list'));

        $data['lang'] = $this->lang;
        $data['settings'] = $settings;
        $data['pagination'] = $settings->links('ocadmin::pagination.default');

        // 建構 URL 參數與排序連結
        $url = $this->buildUrlParams($request);
        $baseUrl = route('lang.ocadmin.system.setting.list');
        $data['sort'] = $filter_data['sort'];
        $data['order'] = $filter_data['order'];
        $nextOrder = ($data['order'] == 'asc') ? 'desc' : 'asc';

        $data['sort_id'] = $baseUrl . "?sort=id&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_code'] = $baseUrl . "?sort=code&order={$nextOrder}" . str_replace('?', '&', $url);
        $data['sort_group'] = $baseUrl . "?sort=group&order={$nextOrder}" . str_replace('?', '&', $url);

        return view('ocadmin::system.setting.list', $data)->render();
    }

    /**
     * 新增頁面
     */
    public function create(): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['setting'] = new Setting();
        $data['types'] = SettingType::cases();

        return view('ocadmin::system.setting.form', $data);
    }

    /**
     * 儲存新增
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code'    => 'required|string|max:255|unique:settings,code',
            'group'   => 'nullable|string|max:100',
            'value'   => 'nullable|string',
            'type'    => 'required|string|in:' . implode(',', SettingType::values()),
            'note'    => 'nullable|string|max:255',
        ]);

        $setting = Setting::create($validated);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_add,
            'replace_url' => route('lang.ocadmin.system.setting.edit', $setting),
            'form_action' => route('lang.ocadmin.system.setting.update', $setting),
        ]);
    }

    /**
     * 編輯頁面
     */
    public function edit(Setting $setting): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['setting'] = $setting;
        $data['types'] = SettingType::cases();

        return view('ocadmin::system.setting.form', $data);
    }

    /**
     * 儲存編輯
     */
    public function update(Request $request, Setting $setting): JsonResponse
    {
        $validated = $request->validate([
            'code'    => 'required|string|max:255|unique:settings,code,' . $setting->id,
            'group'   => 'nullable|string|max:100',
            'value'   => 'nullable|string',
            'type'    => 'required|string|in:' . implode(',', SettingType::values()),
            'note'    => 'nullable|string|max:255',
        ]);

        $setting->update($validated);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_edit,
        ]);
    }

    /**
     * 刪除
     */
    public function destroy(Setting $setting): JsonResponse
    {
        $setting->delete();

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }

    /**
     * 批次刪除
     */
    public function batchDelete(Request $request): JsonResponse
    {
        $ids = $request->input('selected', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => $this->lang->error_select_delete]);
        }

        Setting::whereIn('id', $ids)->delete();

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }

    /**
     * 解析序列化字串為 JSON
     */
    public function parseSerialize(Request $request): JsonResponse
    {
        $value = $request->input('value', '');

        if (empty($value)) {
            return response()->json(['success' => true, 'data' => null]);
        }

        try {
            $data = @unserialize($value);
            if ($data === false && $value !== 'b:0;') {
                return response()->json(['success' => false, 'message' => '無效的序列化字串']);
            }
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * 將資料轉為序列化字串
     */
    public function toSerialize(Request $request): JsonResponse
    {
        $value = $request->input('value', '');

        if (empty($value)) {
            return response()->json(['success' => true, 'data' => '']);
        }

        try {
            $data = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['success' => false, 'message' => 'JSON 格式錯誤：' . json_last_error_msg()]);
            }
            return response()->json(['success' => true, 'data' => serialize($data)]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
