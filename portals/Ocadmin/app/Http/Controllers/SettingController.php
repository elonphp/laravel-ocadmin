<?php

namespace Portals\Ocadmin\Http\Controllers;

use App\Enums\System\SettingType;
use App\Models\System\Setting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SettingController extends Controller
{
    /**
     * 列表頁面
     */
    public function index(Request $request)
    {
        $query = Setting::query();

        // 搜尋條件
        if ($request->filled('filter_code')) {
            $query->where('code', 'like', '%' . $request->filter_code . '%');
        }

        if ($request->filled('filter_group')) {
            $query->where('group', 'like', '%' . $request->filter_group . '%');
        }

        if ($request->filled('filter_type')) {
            $query->where('type', $request->filter_type);
        }

        // 排序
        $sortBy = $request->get('sort', 'id');
        $order = $request->get('order', 'asc');
        $query->orderBy($sortBy, $order);

        $settings = $query->paginate(20)->withQueryString();

        return view('ocadmin::setting.index', [
            'settings' => $settings,
            'types'    => SettingType::cases(),
        ]);
    }

    /**
     * 新增頁面
     */
    public function create()
    {
        return view('ocadmin::setting.form', [
            'setting' => new Setting(),
            'types'   => SettingType::cases(),
        ]);
    }

    /**
     * 儲存新增
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'    => 'required|string|max:255',
            'locale'  => 'nullable|string|max:10',
            'group'   => 'nullable|string|max:100',
            'content' => 'nullable|string',
            'type'    => 'required|string|in:' . implode(',', SettingType::values()),
            'note'    => 'nullable|string|max:255',
        ]);

        $validated['locale'] = $validated['locale'] ?? '';

        // 檢查是否重複
        $exists = Setting::where('locale', $validated['locale'])
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['code' => '此代碼已存在（相同語系下）']);
        }

        Setting::create($validated);

        return redirect()
            ->route('ocadmin.setting.index')
            ->with('success', '參數設定新增成功！');
    }

    /**
     * 編輯頁面
     */
    public function edit(Setting $setting)
    {
        return view('ocadmin::setting.form', [
            'setting' => $setting,
            'types'   => SettingType::cases(),
        ]);
    }

    /**
     * 儲存編輯
     */
    public function update(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'code'    => 'required|string|max:255',
            'locale'  => 'nullable|string|max:10',
            'group'   => 'nullable|string|max:100',
            'content' => 'nullable|string',
            'type'    => 'required|string|in:' . implode(',', SettingType::values()),
            'note'    => 'nullable|string|max:255',
        ]);

        $validated['locale'] = $validated['locale'] ?? '';

        // 檢查是否重複（排除自己）
        $exists = Setting::where('locale', $validated['locale'])
            ->where('code', $validated['code'])
            ->where('id', '!=', $setting->id)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['code' => '此代碼已存在（相同語系下）']);
        }

        $setting->update($validated);

        return redirect()
            ->route('ocadmin.setting.index')
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
}
