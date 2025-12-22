# laravel-ocadmin

借用 OpenCart 後台前端的 Laravel 後台管理系統。

## 核心特點

- 基於 Laravel 12 框架
- 前端借用 OpenCart 4 後台樣式
- Controller 設計參考 OpenCart 後台架構
- 支援 EAV 模式的彈性欄位擴展
- 內建系統日誌功能

## 技術棧

- **後端**：PHP 8.2+ / Laravel 12
- **資料庫**：MariaDB / MySQL
- **前端**：OpenCart Admin 樣式 / Bootstrap 5

## 安裝

```bash
# 複製專案
git clone https://github.com/your-username/laravel-ocadmin.git
cd laravel-ocadmin

# 安裝依賴
composer install

# 環境設定
cp .env.example .env
php artisan key:generate

# 資料庫遷移
php artisan migrate

# 啟動開發伺服器
php artisan serve
```

## 專案結構

```
laravel-ocadmin/
├── app/
│   ├── Models/
│   │   └── Identity/               # 使用者 / 身分相關 Model
│   │
│   ├── Portals/
│   │   └── Ocadmin/                # 後台管理入口（Portal）
│   │       ├── Core/               # 後台核心元件（非業務模組）
│   │       │   ├── Controllers/
│   │       │   ├── Providers/
│   │       │   ├── ViewComposers/
│   │       │   └── Views/           # 共用視圖（layouts, auth）
│   │       │
│   │       ├── Modules/             # 後台功能模組集合
│   │       │   ├── Dashboard/       # 儀表板模組（單一模組，無子分類）
│   │       │   │
│   │       │   ├── Common/          # 共用基礎模組群（此層為分類層）
│   │       │   │   ├── Taxonomy/    # 分類樹 / 標籤體系模組
│   │       │   │   │   ├── TaxonomyController.php
│   │       │   │   │   ├── TaxonomyService.php
│   │       │   │   │   └── Views/
│   │       │   │   │       ├── index.blade.php
│   │       │   │   │       ├── list.blade.php
│   │       │   │   │       └── form.blade.php
│   │       │   │   │
│   │       │   │   └── Term/        # 分類項目 / 詞彙管理模組
│   │       │   │       ├── TermController.php
│   │       │   │       ├── TermService.php
│   │       │   │       └── Views/
│   │       │   │           ├── index.blade.php
│   │       │   │           ├── list.blade.php
│   │       │   │           └── form.blade.php
│   │       │   │
│   │       │   ├── Member/          # 會員管理模組（本層即為模組本體）
│   │       │   │   ├── MemberController.php
│   │       │   │   ├── MemberService.php
│   │       │   │   └── Views/
│   │       │   │       ├── index.blade.php
│   │       │   │       ├── list.blade.php
│   │       │   │       └── form.blade.php
│   │       │   │
│   │       │   └── System/          # 系統層模組（平台設定 / 非業務）
│   │       │       └── Setting/     # 系統設定模組
│   │       │
│   │       └── routes/              # Ocadmin 專用路由
│   │
│   ├── Repositories/                # Repository 層（資料存取抽象）
│   │
│   └── Traits/
│       └── HasMetas.php              # EAV 擴展欄位 Trait
│
├── public/
│   └── assets/ocadmin/              # OpenCart 後台前端靜態資源
│
└── docs/
    └── md/                          # 專案文件
```

## 功能模組

- **帳號管理** - 使用者 CRUD
- **系統管理**
  - 詞彙管理
  - 本地化設定（國家、行政區域）
  - 參數設定
  - 欄位定義（Meta Keys）
  - 系統日誌

## EAV 模式

本專案採用 EAV（Entity-Attribute-Value）模式處理彈性欄位：

```php
// 透過 HasMetas trait 透明存取
$user->phone = '0912345678';
$user->save();

// 或明確操作
$user->setMeta('birthday', '1990-01-01');
$user->getMeta('phone');
```

詳細說明請參考 `Ocadmin資料夾 /docs/` 目錄。

## 授權

本專案採用 [MIT License](LICENSE) 授權。
