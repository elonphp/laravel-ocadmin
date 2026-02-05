<?php

namespace App\Portals\Ocadmin\Modules\System\Setting;

use App\Enums\System\SettingType;
use App\Models\System\Setting;
use Illuminate\Http\Request;
use App\Portals\Ocadmin\Core\Controllers\Controller;

class SettingController extends Controller
{
    protected function setBreadcrumbs(): void
    {
        $this->breadcrumbs = [
            (object)[
                'text' => '首頁',
                'href' => route('lang.ocadmin.dashboard'),
            ],
            (object)[
                'text' => '系統管理',
                'href' => 'javascript:void(0)',
            ],
            (object)[
                'text' => '參數設定',
                'href' => route('lang.ocadmin.system.setting.index'),
            ],
        ];
    }

    /**
     * 列表頁面
     */
    public function index(Request $request)
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

        $settings = $query->paginate(20)->withQueryString();

        return view('ocadmin.system.setting::index', [
            'settings' => $settings,
            'types'    => SettingType::cases(),
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    /**
     * 新增頁面
     */
    public function create()
    {
        return view('ocadmin.system.setting::form', [
            'setting' => new Setting(),
            'types'   => SettingType::cases(),
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    /**
     * 儲存新增
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'    => 'required|string|max:255|unique:settings,code',
            'group'   => 'nullable|string|max:100',
            'value'   => 'nullable|string',
            'type'    => 'required|string|in:' . implode(',', SettingType::values()),
            'note'    => 'nullable|string|max:255',
        ]);

        Setting::create($validated);

        return redirect()
            ->route('lang.ocadmin.system.setting.index')
            ->with('success', '參數設定新增成功！');
    }

    /**
     * 編輯頁面
     */
    public function edit(Setting $setting)
    {
        return view('ocadmin.system.setting::form', [
            'setting' => $setting,
            'types'   => SettingType::cases(),
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    /**
     * 儲存編輯
     */
    public function update(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'code'    => 'required|string|max:255|unique:settings,code,' . $setting->id,
            'group'   => 'nullable|string|max:100',
            'value'   => 'nullable|string',
            'type'    => 'required|string|in:' . implode(',', SettingType::values()),
            'note'    => 'nullable|string|max:255',
        ]);

        $setting->update($validated);

        return redirect()
            ->route('lang.ocadmin.system.setting.index')
            ->with('success', '參數設定更新成功！');
    }

    /**
     * 刪除
     */
    public function destroy(Setting $setting)
    {
        $setting->delete();

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

        Setting::whereIn('id', $ids)->delete();

        return response()->json(['success' => true]);
    }

    /**
     * 解析序列化字串為 JSON
     */
    public function parseSerialize(Request $request)
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
    public function toSerialize(Request $request)
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
