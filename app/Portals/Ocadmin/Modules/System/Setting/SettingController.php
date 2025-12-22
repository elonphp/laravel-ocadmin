<?php

namespace App\Portals\Ocadmin\Modules\System\Setting;

use App\Enums\System\SettingType;
use App\Models\System\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Helpers\Classes\OrmHelper;
use App\Portals\Ocadmin\Core\Controllers\Controller;

class SettingController extends Controller
{
    public function __construct(
        private SettingService $settingService
    ) {
        parent::__construct();
    }

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
     * 列表頁面 - 完整頁面渲染
     */
    public function index(Request $request): View
    {
        $data['list'] = $this->getList($request);
        $data['types'] = SettingType::cases();
        $data['breadcrumbs'] = $this->breadcrumbs;

        return view('ocadmin.system.setting::index', $data);
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
        $query = Setting::query();
        $filter_data = $request->all();

        OrmHelper::prepare($query, $filter_data);

        // 預設排序
        $filter_data['sort'] = $request->get('sort', 'id');
        $filter_data['order'] = $request->get('order', 'asc');

        // 使用 OrmHelper 獲取結果
        $settings = OrmHelper::getResult($query, $filter_data);

        // 設置分頁器路徑
        $settings->withPath(route('lang.ocadmin.system.setting.list'));

        // 建構 URL 參數
        $url = $this->buildUrlParams($request);

        // 準備資料
        $data['settings'] = $settings;
        $data['action'] = route('lang.ocadmin.system.setting.list') . $url;
        $data['url_params'] = $url;

        return view('ocadmin.system.setting::list', $data)->render();
    }

    /**
     * 新增頁面
     */
    public function create()
    {
        $data['setting'] = new Setting();
        $data['types'] = SettingType::cases();
        $data['breadcrumbs'] = $this->breadcrumbs;

        return view('ocadmin.system.setting::form', $data);
    }

    /**
     * 儲存新增 (AJAX)
     */
    public function store(Request $request)
    {
        $validator = validator($request->all(), [
            'code'    => 'required|string|max:50',
            'key'     => 'required|string|max:100',
            'locale'  => 'nullable|string|max:10',
            'value'   => 'nullable|string',
            'type'    => 'required|string|in:' . implode(',', SettingType::values()),
            'note'    => 'nullable|string|max:255',
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
        $locale = $validated['locale'] ?? '';

        // 檢查是否重複
        if ($this->settingService->exists($locale, $validated['code'], $validated['key'])) {
            return response()->json([
                'error_warning' => '此設定鍵已存在（相同語系和命名空間下）',
                'errors' => ['key' => '此設定鍵已存在（相同語系和命名空間下）'],
            ]);
        }

        $setting = DB::transaction(fn () => $this->settingService->create($validated));

        return response()->json([
            'success' => '參數設定新增成功！',
            'redirect_url' => route('lang.ocadmin.system.setting.edit', $setting->id),
            'form_action' => route('lang.ocadmin.system.setting.update', $setting->id),
        ]);
    }

    /**
     * 編輯頁面
     */
    public function edit(Setting $setting)
    {
        $data['setting'] = $setting;
        $data['types'] = SettingType::cases();
        $data['breadcrumbs'] = $this->breadcrumbs;

        return view('ocadmin.system.setting::form', $data);
    }

    /**
     * 儲存編輯 (AJAX)
     */
    public function update(Request $request, Setting $setting)
    {
        $validator = validator($request->all(), [
            'code'    => 'required|string|max:50',
            'key'     => 'required|string|max:100',
            'locale'  => 'nullable|string|max:10',
            'value'   => 'nullable|string',
            'type'    => 'required|string|in:' . implode(',', SettingType::values()),
            'note'    => 'nullable|string|max:255',
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
        $locale = $validated['locale'] ?? '';

        // 檢查是否重複（排除自己）
        if ($this->settingService->exists($locale, $validated['code'], $validated['key'], $setting->id)) {
            return response()->json([
                'error_warning' => '此設定鍵已存在（相同語系和命名空間下）',
                'errors' => ['key' => '此設定鍵已存在（相同語系和命名空間下）'],
            ]);
        }

        DB::transaction(fn () => $this->settingService->update($setting, $validated));

        return response()->json([
            'success' => '參數設定更新成功！',
        ]);
    }

    /**
     * 刪除
     */
    public function destroy(Setting $setting)
    {
        DB::transaction(fn () => $this->settingService->delete($setting));

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

        DB::transaction(fn () => $this->settingService->batchDelete($ids));

        return response()->json(['success' => true]);
    }

    /**
     * 解析序列化字串為 JSON
     */
    public function parseSerialize(Request $request)
    {
        $content = $request->input('content', '');

        if (empty($content)) {
            return response()->json(['success' => true, 'data' => null]);
        }

        try {
            $data = @unserialize($content);
            if ($data === false && $content !== 'b:0;') {
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
        $content = $request->input('content', '');

        if (empty($content)) {
            return response()->json(['success' => true, 'data' => '']);
        }

        try {
            $data = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['success' => false, 'message' => 'JSON 格式錯誤：' . json_last_error_msg()]);
            }
            return response()->json(['success' => true, 'data' => serialize($data)]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
