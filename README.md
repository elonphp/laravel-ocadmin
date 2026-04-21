# Laravel Ocadmin

A Laravel 13 example backend that takes design cues from **OpenCart**'s admin UI to implement a set of common CRUD modules — Catalog (Product, Option), Member, Organization (Company / Department / Employee / external Organization), ACL (Permission / Role / User), Vocabulary (Taxonomy / Term), System Settings, and more.

> A **foundational reference project**, not a turnkey product. Intended as the base to fork when starting a new Laravel admin (HRM, POS, e-commerce, internal tools, etc.).

---

## Why model after OpenCart's admin?

Most Laravel admin starters (AdminLTE, CoreUI, etc.) ship a library of **components** — toasts, badges, simple tables, datatables — and leave you to assemble pages. Useful, but every project ends up reinventing the same list / form / filter scaffolding, often inconsistently.

OpenCart takes the opposite approach: it ships **assembled pages**. A list comes with sort + filter + pagination + batch-delete; a form comes with translations + validation feedback; breadcrumbs auto-generate per controller. Drop in a CRUD module and the patterns are already there.

Take the **product form** as a concrete example. OpenCart ships a tabbed product form covering translations, special pricing, options, reward points, images, attributes — most of the recurring UI demands of an e-commerce backend, already assembled. As a backend developer you don't have to think through how these patterns should look; you copy the convention, hook your data, and ship.

The trade-off is real:

- **Pro**: less reinvention; consistent UX across modules; the conventions encode best practice for the common 90% of admin work.
- **Con**: you adopt OpenCart's UI vocabulary. Breaking the pattern means fighting the framework.

This project picks up the same conventions on Laravel + Bootstrap 5. The same approach can be applied on top of AdminLTE or any other admin chrome — the conventions live in controllers, base classes, and view composition, not in the visual theme.

---

## Portal-based architecture

Every entry-point lives under its own directory: `app/Portals/<Name>/`. Currently only `Ocadmin` (back-office) is active, but the structure is built for multiple portals (e.g. an `Ess` for employee self-service, an `Api` for external clients).

Each Portal owns its **routes, controllers, views, view composers, middleware, providers** — all under one directory tree. No more wading through `Controllers/Api/` vs `Controllers/Admin/`, then jumping again to `Services/Api/` vs `Services/Admin/` to follow a single feature. Each Portal is self-contained: if you're working on the back-office, you stay inside `Portals/Ocadmin/`.

```
app/Portals/Ocadmin/
├── Core/                # Base controller, providers, middleware shared across modules
│   ├── Controllers/
│   ├── Middleware/
│   ├── Providers/
│   └── ViewComposers/
├── Modules/             # Feature modules, one directory per resource group
│   ├── Account/         # Current user's profile + devices
│   ├── Catalog/         # Product, Option, OptionValueGroup, OptionValueLink
│   ├── Dashboard/
│   ├── Member/
│   ├── Org/             # Organization, Company, Department, Employee
│   └── System/          # Acl, Menu, Setting, Schema, Log, Term
├── resources/views/     # Portal-scoped Blade views
└── routes/
```

---

## Modules included

| Group | URL prefix | Resources |
|-------|-----------|-----------|
| **Catalog** | `/catalog` | Product · Option · OptionValueGroup · OptionValueLink |
| **Member** | `/member` | Member |
| **Org** | `/org` | Organization (external) · Company · Department · Employee |
| **System** | `/system` | Permission · Role · User · AccessToken · UserDevice · Setting · Menu · Schema (in-admin DB schema editor) · Log |
| **Config** | `/config` | Taxonomy · Term |

Each module ships the same scaffolding: index (list + filter + sort + pagination + batch-delete), form (translations + validation feedback), and AJAX-driven list refresh.

---

## Tech Stack

- **Framework**: Laravel 13 (PHP 8.5+)
- **Frontend**: Blade + jQuery + Bootstrap 5
- **Auth**: Laravel Sanctum + Spatie Laravel Permission (wildcard permissions)
- **i18n**: URL-based locale switching, model translations via `HasTranslation` trait
- **Database**: MySQL / MariaDB / SQLite

---

## Getting Started

### Requirements

- PHP >= 8.5
- Composer
- Node.js & npm
- MySQL / MariaDB (or SQLite for development)

### Installation

```bash
git clone <repository-url>
cd laravel-ocadmin
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

> **First login**
> Seeders create demo accounts with password `123456` (e.g. `admin@example.com`, `developer@example.com`).
> **Change the admin password immediately** and remove demo accounts you don't need.

> **For production**
> `.env.example` ships `APP_DEBUG=true` for local convenience. Before deploying, set `APP_DEBUG=false` and `APP_ENV=production` on the server.

### Development

```bash
composer dev
```

Starts the Laravel dev server, queue worker, log viewer, and Vite in parallel.

---

## Security

If you discover a security vulnerability, follow the disclosure process in [SECURITY.md](./SECURITY.md) — do not open a public issue.

## License

MIT, see [LICENSE](./LICENSE).

---

# Laravel Ocadmin（中文版）

以 Laravel 13 開發的範例後台，借鏡 **OpenCart** 後台 UI 的設計理念，實作一組常見的 CRUD 模組 — 商品型錄（Product、Option）、會員、組織（公司／部門／員工／外部組織）、ACL（權限／角色／使用者）、詞彙（分類／詞項）、系統設定…等。

> 這是一個**基礎範例專案**，不是現成可上線的成品。設計目的是當你要開始一個新的 Laravel 後台（HRM、POS、電商、內部工具…）時，把它 fork 過來當底。

---

## 為什麼以 OpenCart 後台為參考？

大多數 Laravel 後台範本（AdminLTE、CoreUI 等）提供的是一**組元件** — 各種 toast、各種 badge、simple table、datatable… — 然後你自己組頁面。元件確實好用，但每個專案到頭來都在重複造同一套「列表／表單／篩選」骨架，且常常風格不一致。

OpenCart 走相反路線：直接給你**組好的頁面**。一個列表內建排序＋篩選＋分頁＋批次刪除；一張表單內建翻譯＋驗證錯誤回饋；麵包屑依 controller 自動產生。模組丟進去，這些 pattern 已經就位。

拿**商品表單**當具體例子：OpenCart 把多語、特價、選項、點數、圖片、規格…這些電商後台一定會碰到的 UI 元素，全部組成一張帶 tab 的表單直接給你。身為後端工程師，你不需要花腦筋設計這些 UI 該長怎樣；照著 convention 接資料就能上線。

代價是真的：

- **好處**：少很多重複造輪子；模組之間 UX 一致；這套 convention 把後台 90% 常見需求的 best practice 都先寫好了。
- **壞處**：你被綁進 OpenCart 的 UI 詞彙；想偏離常規就會跟框架打架。

本專案把這套 convention 搬到 Laravel + Bootstrap 5 上。同樣的做法也可以套在 AdminLTE 或任何其他後台 chrome 上 — convention 是寫在 controller、base class、view 組合裡，不綁視覺主題。

---

## Portal 架構

每個入口都有自己的目錄：`app/Portals/<Name>/`。目前只啟用 `Ocadmin`（後台），但結構從一開始就為多 Portal 設計（例如 `Ess` 給員工自助、`Api` 給外部客戶）。

每個 Portal 自己擁有 **routes、controllers、views、view composers、middleware、providers** — 全部在同一個目錄樹下。再也不用為了跟一個功能，先在 `Controllers/Api/` 跟 `Controllers/Admin/` 之間切換，再跳到 `Services/Api/` 跟 `Services/Admin/` 之間切換。每個 Portal 是 self-contained：你在做後台的事，就一直待在 `Portals/Ocadmin/` 裡面。

```
app/Portals/Ocadmin/
├── Core/                # 各模組共用的 base controller、provider、middleware
│   ├── Controllers/
│   ├── Middleware/
│   ├── Providers/
│   └── ViewComposers/
├── Modules/             # 功能模組，每個資源群一個目錄
│   ├── Account/         # 當前使用者的個人資料 + 裝置
│   ├── Catalog/         # Product、Option、OptionValueGroup、OptionValueLink
│   ├── Dashboard/
│   ├── Member/
│   ├── Org/             # Organization、Company、Department、Employee
│   └── System/          # Acl、Menu、Setting、Schema、Log、Term
├── resources/views/     # Portal 範圍內的 Blade view
└── routes/
```

---

## 包含的模組

| 群組 | URL 前綴 | 資源 |
|------|---------|------|
| **Catalog** | `/catalog` | Product · Option · OptionValueGroup · OptionValueLink |
| **Member** | `/member` | Member |
| **Org** | `/org` | Organization（外部組織）· Company · Department · Employee |
| **System** | `/system` | Permission · Role · User · AccessToken · UserDevice · Setting · Menu · Schema（後台直接改 DB schema 的編輯器）· Log |
| **Config** | `/config` | Taxonomy · Term |

每個模組都附同一套骨架：列表頁（list + filter + sort + pagination + batch-delete）、表單頁（translations + 驗證回饋）、AJAX 列表刷新。

---

## 技術棧

- **框架**：Laravel 13（PHP 8.5+）
- **前端**：Blade + jQuery + Bootstrap 5
- **認證／授權**：Laravel Sanctum + Spatie Laravel Permission（支援 wildcard 權限）
- **多語**：URL 帶語系切換、Model 翻譯透過 `HasTranslation` trait
- **資料庫**：MySQL / MariaDB / SQLite

---

## 開始使用

### 系統需求

- PHP >= 8.5
- Composer
- Node.js & npm
- MySQL / MariaDB（開發階段也可用 SQLite）

### 安裝

```bash
git clone <repository-url>
cd laravel-ocadmin
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

> **首次登入**
> Seeder 會建立預設帳號，密碼一律 `123456`（如 `admin@example.com`、`developer@example.com`）。
> **登入後請立即修改 admin 密碼**，並把不需要的 demo 帳號刪掉或重新產生。

> **正式環境部署**
> `.env.example` 預設 `APP_DEBUG=true` 是為了 local 開發方便。部署到正式環境前，請在伺服器的 `.env` 改為 `APP_DEBUG=false` 與 `APP_ENV=production`。

### 開發模式

```bash
composer dev
```

同時啟動 Laravel dev server、queue worker、log viewer 與 Vite。

---

## 安全性

如果發現任何安全性漏洞，請依 [SECURITY.md](./SECURITY.md) 的流程回報 — 請勿直接開公開 issue。

## 授權

MIT，請見 [LICENSE](./LICENSE)。
