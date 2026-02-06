<?php

namespace App\Portals\Ocadmin\Core\Controllers\System;

use App\Enums\System\SettingType;
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
     * 列表頁面
     */
    public function index(Request $request): View
    {
        $query = Setting::query();

        if ($request->filled('filter_code')) {
            $query->where('code', 'like', '%' . $request->filter_code . '%');
        }

        if ($request->filled('filter_group')) {
            $query->where('group', 'like', '%' . $request->filter_group . '%');
        }

        if ($request->filled('filter_type')) {
            $query->where('type', $request->filter_type);
        }

        $sortBy = $request->get('sort', 'id');
        $order = $request->get('order', 'asc');
        $query->orderBy($sortBy, $order);

        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['settings'] = $query->paginate(20)->withQueryString();
        $data['types'] = SettingType::cases();

        return view('ocadmin::system.setting.index', $data);
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
