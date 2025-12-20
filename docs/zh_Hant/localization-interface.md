# 介面多語

本文件說明 UI 文字翻譯機制（Laravel lang 資料夾）。

---

## 概述

介面多語使用 Laravel 內建的翻譯系統：

```php
// 使用
__('ocadmin::common.save')    // 儲存 / Save

// 自動根據 App::getLocale() 載入對應語系檔案
lang/zh_Hant/common.php → 儲存
lang/en/common.php      → Save
```

---

## 翻譯檔結構

### 套件翻譯檔

```
vendor/elonphp/laravel-ocadmin-modules/
└── resources/
    └── lang/
        ├── zh_Hant/
        │   ├── common.php      # 共用文字
        │   ├── menu.php        # 選單
        │   ├── auth.php        # 登入相關
        │   └── validation.php  # 驗證訊息
        └── en/
            ├── common.php
            ├── menu.php
            ├── auth.php
            └── validation.php
```

### 專案覆寫

```
resources/lang/vendor/ocadmin/
├── zh_Hant/
│   └── common.php      # 覆寫套件翻譯
└── en/
    └── common.php
```

---

## 翻譯檔範例

### common.php - 共用文字

```php
<?php
// resources/lang/zh_Hant/common.php

return [
    // 按鈕
    'save' => '儲存',
    'cancel' => '取消',
    'delete' => '刪除',
    'edit' => '編輯',
    'create' => '新增',
    'update' => '更新',
    'submit' => '送出',
    'confirm' => '確認',
    'back' => '返回',
    'close' => '關閉',

    // 操作
    'search' => '搜尋',
    'filter' => '篩選',
    'reset' => '重設',
    'refresh' => '重新整理',
    'export' => '匯出',
    'import' => '匯入',
    'download' => '下載',
    'upload' => '上傳',

    // 狀態
    'loading' => '載入中...',
    'saving' => '儲存中...',
    'processing' => '處理中...',
    'success' => '成功',
    'error' => '錯誤',
    'warning' => '警告',

    // 訊息
    'no_data' => '無資料',
    'no_results' => '無搜尋結果',
    'confirm_delete' => '確定要刪除嗎？',
    'save_success' => '儲存成功',
    'delete_success' => '刪除成功',
    'operation_failed' => '操作失敗',

    // 欄位
    'id' => 'ID',
    'name' => '名稱',
    'title' => '標題',
    'description' => '描述',
    'status' => '狀態',
    'created_at' => '建立時間',
    'updated_at' => '更新時間',
    'actions' => '操作',

    // 狀態值
    'active' => '啟用',
    'inactive' => '停用',
    'enabled' => '已啟用',
    'disabled' => '已停用',
    'yes' => '是',
    'no' => '否',

    // 分頁
    'per_page' => '每頁',
    'total' => '共',
    'items' => '筆',
    'page' => '頁',
    'first' => '第一頁',
    'last' => '最後一頁',
    'previous' => '上一頁',
    'next' => '下一頁',
];
```

```php
<?php
// resources/lang/en/common.php

return [
    // Buttons
    'save' => 'Save',
    'cancel' => 'Cancel',
    'delete' => 'Delete',
    'edit' => 'Edit',
    'create' => 'Create',
    'update' => 'Update',
    'submit' => 'Submit',
    'confirm' => 'Confirm',
    'back' => 'Back',
    'close' => 'Close',

    // Actions
    'search' => 'Search',
    'filter' => 'Filter',
    'reset' => 'Reset',
    'refresh' => 'Refresh',
    'export' => 'Export',
    'import' => 'Import',
    'download' => 'Download',
    'upload' => 'Upload',

    // Status
    'loading' => 'Loading...',
    'saving' => 'Saving...',
    'processing' => 'Processing...',
    'success' => 'Success',
    'error' => 'Error',
    'warning' => 'Warning',

    // Messages
    'no_data' => 'No data',
    'no_results' => 'No results found',
    'confirm_delete' => 'Are you sure you want to delete?',
    'save_success' => 'Saved successfully',
    'delete_success' => 'Deleted successfully',
    'operation_failed' => 'Operation failed',

    // ...
];
```

### menu.php - 選單

```php
<?php
// resources/lang/zh_Hant/menu.php

return [
    'dashboard' => '儀表板',

    // 系統管理
    'system' => '系統管理',
    'system.logs' => '系統日誌',
    'system.logs.database' => '資料庫',
    'system.logs.archived' => '歷史檔案',
    'system.settings' => '系統設定',

    // 權限管理
    'access_control' => '權限管理',
    'access_control.users' => '使用者',
    'access_control.roles' => '角色',
    'access_control.permissions' => '權限',

    // 分類管理
    'taxonomy' => '分類管理',
    'taxonomy.categories' => '分類',
    'taxonomy.tags' => '標籤',
];
```

```php
<?php
// resources/lang/en/menu.php

return [
    'dashboard' => 'Dashboard',

    // System
    'system' => 'System',
    'system.logs' => 'System Logs',
    'system.logs.database' => 'Database',
    'system.logs.archived' => 'Archived',
    'system.settings' => 'Settings',

    // Access Control
    'access_control' => 'Access Control',
    'access_control.users' => 'Users',
    'access_control.roles' => 'Roles',
    'access_control.permissions' => 'Permissions',

    // Taxonomy
    'taxonomy' => 'Taxonomy',
    'taxonomy.categories' => 'Categories',
    'taxonomy.tags' => 'Tags',
];
```

### auth.php - 登入相關

```php
<?php
// resources/lang/zh_Hant/auth.php

return [
    'login' => '登入',
    'logout' => '登出',
    'register' => '註冊',
    'forgot_password' => '忘記密碼',
    'reset_password' => '重設密碼',
    'remember_me' => '記住我',

    'email' => '電子郵件',
    'password' => '密碼',
    'password_confirmation' => '確認密碼',

    'login_success' => '登入成功',
    'logout_success' => '已登出',
    'login_failed' => '帳號或密碼錯誤',
    'account_disabled' => '帳號已停用',

    'welcome_back' => '歡迎回來',
    'please_login' => '請登入您的帳號',
];
```

---

## 使用方式

### Blade 中使用

```php
{{-- 基本使用 --}}
{{ __('ocadmin::common.save') }}
{{ __('ocadmin::menu.dashboard') }}

{{-- 帶參數 --}}
{{ __('ocadmin::common.total_items', ['count' => $total]) }}

{{-- 複數形式 --}}
{{ trans_choice('ocadmin::common.items', $count) }}

{{-- 使用 @lang 指令 --}}
@lang('ocadmin::common.save')

{{-- 檢查是否有翻譯 --}}
@if (Lang::has('ocadmin::custom.key'))
    {{ __('ocadmin::custom.key') }}
@endif
```

### PHP 中使用

```php
// 基本使用
$text = __('ocadmin::common.save');
$text = trans('ocadmin::common.save');

// 帶參數
$text = __('ocadmin::common.welcome', ['name' => $user->name]);

// 複數形式
$text = trans_choice('ocadmin::common.items', $count);

// 取得所有翻譯
$translations = trans('ocadmin::common');
```

### 帶參數的翻譯

```php
// 翻譯檔
return [
    'welcome' => '歡迎，:name！',
    'items_count' => '共 :count 筆資料',
];

// 使用
__('ocadmin::common.welcome', ['name' => 'John'])
// 輸出：歡迎，John！
```

### 複數形式

```php
// 翻譯檔
return [
    'items' => '{0} 無項目|{1} 1 個項目|[2,*] :count 個項目',
];

// 使用
trans_choice('ocadmin::common.items', 0)   // 無項目
trans_choice('ocadmin::common.items', 1)   // 1 個項目
trans_choice('ocadmin::common.items', 5)   // 5 個項目
```

---

## 覆寫翻譯

### 發佈翻譯檔

```bash
php artisan vendor:publish --tag=ocadmin-lang
```

檔案會複製到 `resources/lang/vendor/ocadmin/`。

### 部分覆寫

只需建立要覆寫的鍵值：

```php
<?php
// resources/lang/vendor/ocadmin/zh_Hant/common.php

return [
    // 只覆寫這一個
    'save' => '儲存變更',
];
```

其他未覆寫的鍵值仍使用套件預設。

---

## 模組翻譯

### 模組翻譯檔

```
app/Ocadmin/Modules/Inventory/
└── Resources/
    └── lang/
        ├── zh_Hant/
        │   └── inventory.php
        └── en/
            └── inventory.php
```

```php
<?php
// app/Ocadmin/Modules/Inventory/Resources/lang/zh_Hant/inventory.php

return [
    'title' => '庫存管理',
    'product' => '商品',
    'stock' => '庫存',
    'low_stock' => '庫存不足',
    'out_of_stock' => '缺貨',
];
```

### 使用模組翻譯

```php
// 模組翻譯使用 inventory:: 前綴
{{ __('inventory::inventory.title') }}
{{ __('inventory::inventory.low_stock') }}
```

### 載入模組翻譯

ModuleLoader 會自動載入：

```php
// ModuleLoader.php
protected function loadTranslations(string $modulePath, string $moduleName): void
{
    $langPath = $modulePath . '/resources/lang';

    if (is_dir($langPath)) {
        $this->app['translator']->addNamespace(
            strtolower($moduleName),
            $langPath
        );
    }
}
```

---

## JSON 翻譯檔

Laravel 也支援 JSON 格式：

```json
// resources/lang/zh_Hant.json
{
    "Welcome": "歡迎",
    "Login to your account": "登入您的帳號"
}
```

```php
// 使用（不需前綴）
{{ __('Welcome') }}
{{ __('Login to your account') }}
```

---

## 驗證訊息

### 覆寫驗證訊息

```php
<?php
// resources/lang/vendor/ocadmin/zh_Hant/validation.php

return [
    'required' => ':attribute 為必填欄位',
    'email' => ':attribute 格式不正確',
    'unique' => ':attribute 已存在',
    'min' => [
        'string' => ':attribute 至少需要 :min 個字元',
        'numeric' => ':attribute 不能小於 :min',
    ],

    // 自訂屬性名稱
    'attributes' => [
        'email' => '電子郵件',
        'password' => '密碼',
        'name' => '名稱',
    ],
];
```

---

## 動態切換語系

### 臨時切換

```php
// 臨時切換語系
$originalLocale = app()->getLocale();
app()->setLocale('en');

$englishText = __('ocadmin::common.save');

// 恢復
app()->setLocale($originalLocale);
```

### 指定語系取得翻譯

```php
// 使用指定語系
$text = __('ocadmin::common.save', [], 'en');
```

---

## 最佳實踐

### 1. 鍵值命名

```php
// 好：使用 snake_case，語意清楚
'confirm_delete' => '確定要刪除嗎？',
'save_success' => '儲存成功',

// 避免：太籠統
'message1' => '...',
'text' => '...',
```

### 2. 分類組織

```php
// 依功能分類
return [
    // 按鈕
    'btn_save' => '儲存',
    'btn_cancel' => '取消',

    // 訊息
    'msg_success' => '成功',
    'msg_error' => '錯誤',
];

// 或使用巢狀
return [
    'buttons' => [
        'save' => '儲存',
        'cancel' => '取消',
    ],
    'messages' => [
        'success' => '成功',
        'error' => '錯誤',
    ],
];
```

### 3. 避免硬編碼

```php
// 好
{{ __('ocadmin::common.save') }}

// 避免
{{ '儲存' }}
```

---

*文件版本：v1.1 - 更新語系格式規範（底線取代橫線）*
