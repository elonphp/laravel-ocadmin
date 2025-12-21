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
│   │   └── Identity/          # 使用者相關 Model
│   ├── Repositories/          # Repository 層
│   └── Traits/
│       └── HasMetas.php       # EAV 擴展欄位 Trait
├── portals/
│   └── Ocadmin/               # 後台管理模組
│       ├── app/
│       │   └── Http/Controllers/
│       ├── resources/views/
│       └── routes/web.php
├── public/
│   └── assets/ocadmin/        # OpenCart 後台前端資源
└── docs/
    └── md/                    # 專案文件
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

詳細說明請參考 `docs/md/Ocadmin/` 目錄。

## 授權

本專案採用 [MIT License](LICENSE) 授權。
