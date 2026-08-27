# Portal 概述

> Portal 是系統的應用入口。本檔涵蓋 Portal 概念、`config/portals.php` 設定、角色 / 權限 / URL 結構、Portal 內資料夾階層、新增 Portal 步驟、ocadmin 雙模架構。

## 目錄

- [一、Portal 概念](#一portal-概念)
- [二、config/portals.php](#二configportalsphp)
- [三、角色與權限的 Portal 前綴](#三角色與權限的-portal-前綴)
- [四、URL 結構](#四url-結構)
- [五、技術選型與共用資源](#五技術選型與共用資源)
- [六、Portal 內資料夾階層](#六portal-內資料夾階層)
  - [6.0 Core/ 不是每個 Portal 都有](#60-core-不是每個-portal-都有)
  - [6.0.1 portal 級 layer 資料夾](#601-portal-級-layer-資料夾)
  - [6.0.2 Core/ 與 Modules/ 是兩個獨立的軸](#602-core-與-modules-是兩個獨立的軸)
- [七、新增 Portal 步驟](#七新增-portal-步驟)
- [八、雙模架構：前後台分離 vs ocadmin 兼任前台](#八雙模架構前後台分離-vs-ocadmin-兼任前台)
- [相關文件](#相關文件)

---

## 一、Portal 概念

Portal 是系統的**應用入口**。每個 Portal 面向不同的使用者群體，擁有獨立的介面、角色與權限範圍。

下圖為 Portal-by-Portal 對比卡（聚焦各 Portal 面向 / 角色前綴的差異）。含 Laravel 基礎框架與 Portal 共用層的整體系統圖見 [10000 §核心觀點](10000_系統架構.md#核心觀點這是一個-laravel-專案)。

```
┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐
│   Ocadmin   │  │     Hrm     │  │     Web     │  │     POS     │
│   Portal    │  │   Portal    │  │   Portal    │  │   Portal    │
│             │  │             │  │             │  │             │
│  公司內部    │  │  HR / 主管   │  │  大眾/客戶   │  │  門市人員    │
│  後台管理    │  │  / 員工      │  │  官網前台    │  │  銷售系統    │
└─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘
  role: admin.*    role: hrm.*      role: web.*      role: pos.*
```

每個 Portal：

- 有自己的**角色**（以 `role_prefix` 為前綴，如 `admin.*`、`hrm.*`）
- 有自己的**權限**（`{role_prefix}.{module}.{resource}.{action}`）
- 可以有獨立的**技術棧**（Blade 或前後端分離；見 [§五](#五技術選型與共用資源)）
- 可以有獨立的 URL 前綴與認證方式

本範本目的是**示範 Ocadmin 後台**，主力為 Ocadmin Portal；同時內含一個最小 WebV1 前台 scaffold 作為 Mode A（前後台分離）的對照樣本。下表的 Hrm / POS 是 Portal 架構**可承載的其他應用入口類型範例**，由衍生專案視業務需求採用或增減，與本範本無關。

| Portal | url_slug | role_prefix | 面向對象 | 在本範本 |
|---|---|---|---|---|
| Ocadmin | `admin` | `admin` | 公司內部職員 | ✅ 實作 |
| WebV1 | `''`（root path `/{locale}/...`） | `webv1` | 大眾 / 客戶 | ✅ 最小 scaffold |
| POS | 依專案定義 | 依專案定義 | 門市人員 | — 範例 |
| Hrm | `hrm` | `hrm` | HR / 主管 / 員工（含 mss、ess、team 等子模組） | — 範例 |

> 「範例」欄位的 url_slug / role_prefix 僅作為命名慣例展示，衍生專案可採用、改名或不採用。WebV1 的 `v1` 是版本 token（黏字一字 `webv1`，不寫 `web_v1` —— 對齊 dir `WebV1` 寫法）；衍生專案改版時可加 `WebV2` 並存過渡。

---

## 二、config/portals.php

### 2.1 設定檔內容

```php
return [
    'ocadmin' => [
        'web_enabled'       => env('OCADMIN_WEB_ENABLED', false), // 是否兼任前台（見 §八 雙模架構）
        'url_slug'          => 'admin',                            // kebab-case，URL 路徑段
        'role_prefix'       => env('OCADMIN_ROLE_PREFIX', 'admin'),       // snake_case，角色命名前綴
        'permission_prefix' => env('OCADMIN_PERMISSION_PREFIX', 'admin'), // snake_case，權限命名前綴
        'auth_driver'       => env('OCADMIN_AUTH_DRIVER', 'sanctum'),     // sanctum / oauth
        'dir'               => 'Ocadmin',                          // PascalCase，對應 app/Portals/Ocadmin
    ],
    'webv1' => [
        'url_slug'          => '',                                 // 空字串：root path，URL 形如 /{locale}/...
        'role_prefix'       => 'webv1',                            // snake-friendly：v1 黏在 web 上，對齊 dir
        'permission_prefix' => 'webv1',
        'dir'               => 'WebV1',                            // PascalCase，對應 app/Portals/WebV1
    ],
];
```

> 範本層採 **`webv1` 作為前台 portal key**（對應 dir `WebV1`）。命名把版本 token `v1` 與 `web` 黏成一字而非 snake_case `web_v1`，是為了與 `dir` 寫法一致（兩者都是 `WebV1` 而非 `Web_V1`）。`v1` 是版本識別不是業務詞，例外可接受。env var 對齊用 `WEBV1_*`。

### 2.2 欄位說明

| 欄位 | 用途 | 必填 | 說明 |
|---|---|---|---|
| array key | 內部識別碼，config 索引 | ✓ | 穩定不變，不影響 URL 或角色 |
| `web_enabled` | ocadmin 是否兼任前台（Mode B axis） | ✓ ocadmin 段 | bool，永遠存在；`true` → ocadmin 對所有人開放；`false` → 純後台。見 [§八](#八雙模架構前後台分離-vs-ocadmin-兼任前台) |
| `url_slug` | 路由前綴，決定 path-based URL | 選填 | 字串 → `/{locale}/{url_slug}/...`；空字串 → 不帶中間層 `/{locale}/...`；省略 → domain-based portal |
| `role_prefix` | 角色 / 權限命名前綴 | ✓ | 所有角色以此為前綴 |
| `permission_prefix` | 權限命名前綴 | ✓ | 通常等於 `role_prefix`；分開定義保留彈性 |
| `auth_driver` | 認證 driver 選擇 | 選填 | `sanctum` / `oauth`；預設 sanctum，見 [10026 OAuth 整合](10026_OAuth帳號中心整合.md) |
| `dir` | `app/Portals/` 下的實作目錄名稱 | ✓ | 一個 key 對應一個目錄 |

**設計原則：一個 key，一份設定，一個目錄。**

> Prefix 字串拼接統一走 `permName()` / `roleName()` helper，code / seeder 全程不 hardcode prefix。詳見 [10007 §5.5 Prefix 注入機制](10007_權限機制.md)。

### 2.3 四個概念完全解耦

`url_slug`、`role_prefix`、`dir` 各自獨立，可以不同，可以個別異動：

```php
// 情境 1：URL 改為 /backend，但角色不動（既有授權不受影響）
'ocadmin' => [
    'url_slug'    => 'backend',
    'role_prefix' => 'admin',
    'dir'         => 'Ocadmin',
],

// 情境 2：大版本改版，新舊 Portal 並存過渡
'ocadmin' => [
    'url_slug'    => 'ocadmin',     // 舊後台移至 /ocadmin
    'role_prefix' => 'admin',
    'dir'         => 'Ocadmin',
],
'ocadmin-v2' => [
    'url_slug'    => 'admin',       // 新後台接管 /admin
    'role_prefix' => 'admin',       // 相同角色，明確宣告
    'dir'         => 'OcadminV2',
],

// 情境 3：domain-based Portal（官網）— url_slug 省略不寫
'web_other_site' => [
    'role_prefix' => 'web_other_site',
    'dir'         => 'WebOtherSite',
],

// 情境 4：單一後台對全員開放，URL 不帶中間層
//   適用於只有一個 Portal、所有角色（HR / 主管 / 員工）共用同一套後台的內部系統
'ocadmin' => [
    'url_slug'    => '',            // 空字串 = 不帶 URL 中間層
    'role_prefix' => 'web',         // 角色仍有前綴；URL 與角色解耦
    'dir'         => 'Ocadmin',
],
```

> `url_slug` 三種取值：字串 → 對應 `/{locale}/{url_slug}/...`；空字串 → 不帶中間層；省略不寫 → domain-based。

### 2.4 讀取

```php
config('portals.ocadmin.url_slug')  // 'admin'
collect(config('portals'))           // 所有 portal
```

---

## 三、角色與權限的 Portal 前綴

角色與權限皆帶有 `role_prefix` / `permission_prefix`，確保不同入口之間的隔離：

| 項目 | 格式 | 範例 |
|---|---|---|
| 角色 | `{role_prefix}.{role_name}` | `admin.operator`、`hrm.hr_manager` |
| 權限 | `{permission_prefix}.{module}.{resource}.{action}` | `admin.system.setting.modify`、`hrm.mss.employee.access` |
| 全域角色（例外） | `{role_name}` | `super_admin`、`system`（跨 Portal） |

### 3.1 Portal 存取判斷

```php
// User Model
public function hasPortalRole(string $rolePrefix): bool
{
    if ($this->hasRole('super_admin')) {
        return true;
    }

    return $this->roles->contains(
        fn ($role) => str_starts_with($role->name, $rolePrefix . '.')
    );
}

$user->hasPortalRole('admin');  // 是否可進入 Ocadmin
$user->hasPortalRole('hrm');    // 是否可進入 Hrm Portal
```

### 3.2 Middleware

```php
// 路由級 Portal 守門，參數為 role_prefix（runtime 由 config 拼接，不 hardcode）
Route::middleware(['auth', 'requirePortalRole:' . config('portals.ocadmin.role_prefix')])
    ->group(/* Ocadmin 路由 */);
```

完整 Spatie 整合、wildcard、Gate 機制見 [10007 權限機制](10007_權限機制.md)。

---

## 四、URL 結構

### 4.1 基本結構

```
/{locale}/{url_slug}/{module}/{resource}
```

- `{locale}` — 語系前綴，如 `zh-hant`、`en`。單語系專案亦保留以利擴充
- `{url_slug}` — Portal 識別，來自 `config/portals.php` 的 `url_slug`
- 其後為各 Portal 自訂的路由結構

### 4.2 不放業務識別碼於 URL 前綴

品牌、門市等業務識別碼**不應**放入 URL 前綴（如 `/{locale}/{brand-slug}/pos`）：

- 品牌是登入後的 session 上下文，不是應用入口識別符
- 品牌更名時 URL 需跟著改
- 管理後台跨品牌，無法對應單一品牌

**正確做法**：URL 只到 portal 層級，品牌由 session / auth 決定。

### 4.3 各 Portal 各自有登入頁

```
/{locale}/admin/login       ← Ocadmin
/{locale}/hrm/login         ← Hrm
/{locale}/pos/login         ← POS
```

優點：各 Portal 完全自治，設備（門市平板）可直接書籤到正確入口。

**Auth Guard**：多個 Portal 可共用同一個 guard（如 `web`），差異在登入後的 `role_prefix` 驗證。

---

## 五、技術選型與共用資源

### 5.1 各 Portal 技術選型

按「Portal」分。Laravel / PHP / DB 等全系統基礎技術棧見 [10000 §技術棧](10000_系統架構.md#技術棧全系統)。

不同 Portal 的使用者情境不同，技術棧隨之調整。本範本只實作 Ocadmin Portal；下表其他列為架構承載的範例：

| Portal | 使用者 | 裝置 | 介面特性 | 技術選型 |
|---|---|---|---|---|
| Ocadmin（實作） | 管理人員 | 桌機 | 資料密集、表格多、表單多 | Blade + jQuery + Bootstrap 5 |
| Hrm（範例） | HR / 主管 / 員工 | 桌機 / 手機 | 互動豐富、表單複雜 | 衍生專案選型（前後端分離見 [`docs/research/前後端分離的權限機制.md`](../research/前後端分離的權限機制.md)） |
| POS（範例） | 門市店員 | 平板 | 大按鈕、觸控優化、快速結帳 | 衍生專案選型 |
| Web（範例） | 大眾 / 客戶 | 各種 | SEO 重要、靜態為主 | 衍生專案選型 |

> 不同 Portal 採不同 CSS 框架時（例如 Bootstrap 與 Tailwind），class 命名衝突由 Portal 層隔離（各自獨立的 Vite 進入點與 bundle）。

### 5.2 Portal 間的共用資源

對應 [10000 §核心觀點](10000_系統架構.md#核心觀點這是一個-laravel-專案) ASCII 圖底部「共用：Models / Migrations / Services」的完整細則。Portal 之間**共用後端資源，隔離前端資源**：

| 資源 | 共用 | 說明 |
|---|---|---|
| `app/Models/` | ✓ | 所有 Portal 使用相同 Eloquent Model |
| `app/Services/` | ✓ | **跨 Portal 共用**的 Service。只有單一 Portal 用到的下沉到 `app/Portals/{Portal}/Services/`，只有單一 Module 用到的下沉到該 Module 目錄——三層判準見 [§6.0.1](#601-portal-級-layer-資料夾) |
| `app/Helpers/` | ✓ | OrmHelper、DateHelper 等工具 |
| `database/migrations/` | ✓ | 資料表統一管理 |
| `lang/` | ✓ | 各 Portal 有自己的語言檔目錄，但語系檔集中在頂層 `lang/` |
| Blade Views | ✗ | 各 Portal 完全獨立 |
| 前端資源（JS / CSS） | ✗ | 各 Portal 獨立的 Vite 進入點與 bundle |
| 路由檔 | ✗ | 各 Portal 有獨立的 `routes/` 目錄 |

---

## 六、Portal 內資料夾階層

`app/Portals/{Portal}/` 底下有三種東西，各有各的組織策略：

| 資料夾 | 性質 | 結構策略 | 修改頻率 |
|---|---|---|---|
| `Core/` | **從範本繼承**的標準供應品（Auth / Account / System / Common） | **Laravel-native layer-grouped**（`Controllers/` `Services/` `Providers/` …） | 低 |
| `Modules/` | 個別專案自有的業務模組 | **Module-grouped**（每個 resource 一個 folder，內含 Controller / Service / Request） | 高 |
| portal 級 `{Layer}/`（`Services/`、`Providers/` …） | 專案自有、**被本 Portal 兩個以上 Module 共用**的分層 | 與 `app/{Layer}/` 同名同義，作用範圍縮到本 Portal | 中 |

```
app/Portals/Ocadmin/
├── Core/                  ← 從範本繼承（layer-grouped）
│   ├── Controllers/
│   ├── Services/
│   ├── Contracts/
│   ├── Drivers/
│   ├── Providers/
│   └── ViewComposers/
├── Services/              ← 專案自有、跨 Module 共用（portal 級 layer）
└── Modules/               ← 業務模組（module-grouped）
    ├── Catalog/
    ├── Member/
    └── Org/
```

### 6.0 Core/ 不是每個 Portal 都有

`Core/` 表達的是「**這個 Portal 從範本繼承來的東西**」。判準就一句話：

> **這個 Portal 有沒有從範本繼承東西。有才開 `Core/`。**

- **後台 Portal（ocadmin）**：✅ 開。Auth / Account / System / Common 整批都是範本隨附，`Core/` 名副其實；[§6.2.2](#622-衍生專案可修改-core) 的 `[core-divergence]` 紀律也才有意義——它標記的正是「偏離範本」。
- **非後台 Portal**（前台、POS、對外 API…）：❌ 不開。這些 Portal 整個都是專案自有，根本沒有「範本隨附 vs 專案自有」這條界線可分。硬開 `Core/` 只會讓它退化成「非 Modules 的雜項層」——多一層目錄卻不表達任何資訊，還讓 `[core-divergence]` 失去判準（專案自有的東西放進 `Core/` 之後，每次改都要糾結要不要標 divergence）。

沒有 `Core/` 的 Portal，把 layer 資料夾**直接放在 portal 根層**：

```
app/Portals/WebV1/
├── Controllers/
│   └── HomeController.php
├── Providers/
│   └── WebV1ServiceProvider.php
├── resources/views/webv1/
└── routes/webv1.php
```

> ⚠️ **這個範例沒有 `Modules/`，是因為該 Portal 目前只有一支首頁 controller、沒有業務模組**——不是因為它沒有 `Core/`。有業務模組的 Core-less Portal 一樣要開 `Modules/`，見 [§6.0.2](#602-core-與-modules-是兩個獨立的軸)。

> **注意 [§6.2](#62-core-結構) 那份清單的性質**：它是「後台範本附贈了哪些東西」的**具名清單**，不是 `Core/` 的抽象定義。判斷某個 Portal 該不該有 `Core/`，用的是上面那句判準，不是去比對那份清單。

### 6.0.1 portal 級 layer 資料夾

不論有沒有 `Core/`，portal 根層都可以有與 `app/` 同名同義的 layer 資料夾（`Services/`、`Events/`、`Listeners/`、`Jobs/`、`Console/`、`Mail/` …），裝**專案自有、被本 Portal 兩個以上 Module 共用**的東西。

心智模型：**一個 Portal 就是縮小版的 `app/`，外加一個 `Modules/`**。三層規則：

| 作用範圍 | 位置 |
|---|---|
| 跨 Portal | `app/{Layer}/` |
| **單一 Portal 內、跨 Module** | **`app/Portals/{Portal}/{Layer}/`** |
| 單一 Module | `app/Portals/{Portal}/Modules/{Domain}/{Resource}/` |

這一層的存在理由是**消除 Module 之間的橫向相依**：沒有它，被兩個 Module 共用的領域規則只能塞進其中一個 Module（B module 得 `use` A module，兩個本該能各自增刪、各自移植的 feature 就綁死了），或是提前推到 `app/{Layer}/`（把跨 Portal 層撐成雜物間）。

`Core/{Layer}/` 與 portal 級 `{Layer}/` 可以並存，兩者分工是**來源**、不是作用範圍：

| | 放什麼 | 例 |
|---|---|---|
| `Core/Services/` | 範本隨附的 | `SchemaService` |
| `Services/`（portal 根層） | 專案自己加的、跨 Module 共用 | 被 `Core/` 與 `Modules/Account/` 同時用到的 `UserDeviceService` |

補一條紀律：**升層是單向的**——先放最裡層，日後真的被更大的範圍用到才往上搬，不要預先卡位。判準細節見 [10016 §判準：作用範圍決定層級](10016_架構分層職責.md#判準作用範圍決定層級)。

### 6.0.2 Core/ 與 Modules/ 是兩個獨立的軸

§六開頭那張表容易被讀成「`Core/` 或 `Modules/` 二選一」。兩者其實互不決定：

| 軸 | 判準 |
|---|---|
| 有沒有 `Core/` | 這個 Portal 有沒有從範本繼承東西（[§6.0](#60-core-不是每個-portal-都有)） |
| 有沒有 `Modules/` | 這個 Portal 有沒有業務模組 |

**沒有 `Core/` 不代表不用 `Modules/`。** portal 根層的 layer 資料夾就是「沒有 `Core/` 時 `Core/` 的替身」——職責分界完全照 [§6.2](#62-core-結構) / [§6.3](#63-modules-結構) 那條，只是少了「繼承來源」那層語意：

| 放什麼 | 位置 |
|---|---|
| **portal baseline**：base controller、login、home、Account 維度的自助 Profile（[§6.7](#67-self-service-邊界account-維度-vs-業務-module-維度)）——**不屬於任何業務模組**的東西 | 有 `Core/` → `Core/Controllers/`；無 → 根層 `Controllers/` |
| **業務模組** | **一律** `Modules/{Domain}/{Resource}/`，不論該 Portal 有沒有 `Core/` |

**為什麼要明講**：不寫死的話，Core-less Portal 的根層 `Controllers/` 會原封不動重演 [§6.0](#60-core-不是每個-portal-都有) 描述的那個老毛病——退化成「非 Modules 的雜項層」，業務模組全平鋪在裡面。而 module-grouped 的好處（[§6.1](#61-為何-core-與-modules-結構不同)：一個 feature 一個資料夾，便於開發、刪除、移植）跟「東西從哪繼承來」毫無關係，前台一樣需要。

> **實際踩過的反例**：一個沒有 `Core/` 的前台 Portal 把業務模組全平鋪在根層 `Controllers/{Domain}/`、完全不開 `Modules/`，而同專案的後台 Portal 走 `Modules/{Domain}/{Resource}/`——同一份 codebase 兩套組織法，讀的人得同時記兩套。
>
> 這種寫法在**加第二層之前都是免費的**，所以很容易一路拖下去。等到該 Portal 真的長出 `Services/OrderService.php`，同一個 feature 就被切在兩棵樹上（`Controllers/Order/` 一半、`Services/` 一半、之後 `Requests/` 再一半），那正是 [§6.1](#61-為何-core-與-modules-結構不同) 說 module-grouped 要解決的問題。搬遷成本只會愈拖愈貴。

#### 6.0.2.1 `Modules/` 深度各 Portal 自己判

`{Domain}` 那一層要不要開，看**該 Portal 自己的五層座標**（[§6.3.1](#631-中介層雙條件)），**不是照抄別的 Portal**：

| Portal | URL | Module 位置 |
|---|---|---|
| 後台 | `/admin/sale/orders` | `Modules/Sale/Order/` — 五層對齊 ✓，開中介層 |
| 前台 | `/orders` | `Modules/Order/` — 五層對齊 ✗，拉平（[§6.3.2](#632-條件矩陣) 對應格） |

同一個 entity 在兩個 Portal 的 Module 深度不同，**不算不一致**——一致的是規則，不是層數。

### 6.1 為何 Core/ 與 Modules/ 結構不同

| 觀察點 | Core/ | Modules/ |
|---|---|---|
| 規模 | 小（feature 數量有限、不隨專案大） | 大（每專案自由增長） |
| 修改頻率 | 低 | 高 |
| 修改模式 | 偶爾改 Provider / 一個 Controller | 開發一個 feature 同時動 Controller + Service + Request |
| 新增 / 刪除 feature | 罕見 | 常態 |
| 移植 feature 到別專案 | 不發生（Core/ 是共用基底） | 可能（feature 可重用） |

對 **Modules/**：module-grouped 讓「一個 feature = 一個 folder」，便於開發、刪除、移植。

對 **Core/**：layer-grouped 跟 Laravel 慣例對齊，新人直覺找得到檔；feature 數量有限，module-grouped 的 scaling 優勢不發生。

### 6.2 Core/ 結構

> 本節描述的是**後台 Portal（ocadmin）**的 `Core/`。其他 Portal 是否有 `Core/`，見 [§6.0](#60-core-不是每個-portal-都有)。

Core/ 包含範本隨附的標準供應品：

- **Auth** — Login + AuthDriver 機制
- **Account** — 自助 Profile
- **System** — Log / Schema / Setting / Taxonomy / Term / Menu / MenuTree + **Acl 子層**（系統管理工具 + 通用詞彙管理 + 訪問控制）
- **Common** — ImageManager（跨 module 工具）
- **Providers / ViewComposers** — 架構分層

```
Core/
├── Controllers/
│   ├── OcadminController.php          ← base class
│   ├── LoginController.php            ← inject AuthDriver
│   ├── ImageManagerController.php
│   ├── Account/
│   │   └── ProfileController.php
│   └── System/
│       ├── LogController.php
│       ├── SchemaController.php
│       ├── SettingController.php
│       ├── TaxonomyController.php
│       ├── TermController.php
│       ├── MenuController.php
│       ├── MenuTreeController.php
│       └── Acl/
│           ├── PermissionController.php
│           ├── RoleController.php
│           ├── UserController.php
│           ├── AccessTokenController.php
│           └── UserDeviceController.php
├── Services/
│   └── SchemaService.php
├── Contracts/Auth/
│   └── AuthDriver.php
├── Drivers/Auth/
│   ├── SanctumAuthDriver.php
│   └── OauthAuthDriver.php
├── Providers/
│   └── OcadminServiceProvider.php
└── ViewComposers/
    ├── LocaleComposer.php
    └── MenuComposer.php
```

#### 6.2.1 默認 flat 原則

`Core/Controllers/{Module}/` 直接放 `*Controller.php`，flat；只在 sibling controllers 多到難掃描、或概念上構成獨立子模組時才開中介層。

| 區段 | 結構 | 理由 |
|---|---|---|
| `Core/Controllers/System/` | flat（Log / Schema / Setting / Taxonomy / Term / Menu / MenuTree 七檔平鋪） | 各自獨立，無概念性 grouping |
| `Core/Controllers/System/Acl/` | 開中介層 | ACL 五個 sibling controllers，且概念上是「系統管理 → 訪問控制」子模組 |
| `Core/Services/` | flat（`SchemaService` 單檔） | 單檔無需子層 |
| portal 根層 `Services/` | flat（同一 domain ≥ 2 支才開子目錄） | 對齊 [10016 §目錄結構：默認 flat](10016_架構分層職責.md#目錄結構默認-flat) |

中介層的開啟條件對齊 [§6.3 Modules/ 中介層判定](#63-modules-結構)。

#### 6.2.2 衍生專案可修改 Core/

衍生專案允許修改 Core/ 應對小範圍差異（典型案例：認證 driver 參數化、view 主題覆蓋）。改動紀律：

- Commit message 加 `[core-divergence]` tag + 簡述原因
- Core/ 內偏離處加註解：`// [DIVERGE-FROM-MAIN] YYYY-MM-DD 原因`

結構性新策略（如 2FA / WebAuthn / 多重認證流程）才落 `Modules/Auth/`，不在 Core/ 內疊。

### 6.3 Modules/ 結構

> `Modules/` 的有無與該 Portal 有沒有 `Core/` **無關**——業務模組一律進 `Modules/`，見 [§6.0.2](#602-core-與-modules-是兩個獨立的軸)。

Modules/ 預設深度兩層 `{Domain}/{Resource}/`：

```
Modules/
└── {Domain}/                           ← 業務領域
    └── {Resource}/                     ← 資源
        ├── {Resource}Controller.php
        ├── {Resource}Service.php       ← 如有
        ├── Services/                   ← 多個 service 需要分檔時
        ├── Requests/                   ← form request（如有）
        └── README.md                   ← 索引：對應 Models / Migrations / Views / Permissions
```

每個 resource 一個 folder，內含該 resource 的全部 active code（Controller / Service / Request / Middleware）。Model 不放 Modules/ 內，集中在 `app/Models/{Domain}/`（見 [§6.5](#65-model-位置)）。

#### 6.3.1 中介層雙條件

中介 grouping folder（如 `{Domain}/{Group}/{Resource}/`）必須**兩個條件同時滿足**才開：

1. **五層對齊**：URL / Route name / Permission / View folder 也都使用該中介層 token（不只 Module folder 自己擅自加層）
2. **多 sibling**（含規劃中）：該中介層下**目前有 ≥ 2 個 resource**，**或** 該 module 的 README 明確標註規劃中將有 ≥ 2 個 resource 在合理時程內

任一條件不滿足都應拉平。Class basename 仍保留 resource 全名（見 [§6.4](#64-controller-命名)）。

#### 6.3.2 條件矩陣

| 五層對齊 | 多 sibling | 處置 |
|---|---|---|
| ✓ | ✓ | **開中介層** |
| ✗ | ✓ | **拉平 Module folder**，URL / Permission / View 維持現況 |
| ✗ | ✗ | **拉平 Module folder** |
| ✓ | ✗ | **邊界案例**：若 README 標註規劃擴充，**保留中介層**；否則拉平要連 URL / Permission 一起改 |

#### 6.3.3 邊界案例：`Member/Member/` 自我重複

`Modules/Member/Member/MemberController.php` 屬「五層對齊 ✓ + 多 sibling ✗」格。判定**保留中介層**：

- Member 是「會員管理」母選單概念，**規劃中**將有多個子 resource（例如 Member / Level / …）
- 條件 2 的「README 規劃擴充」例外正是為此設計

要件：`Modules/Member/README.md` 必須標註「Member module 規劃含 Member、Level、…」。若日後規劃 resource 未實作，回頭評估是否拉平。

### 6.4 Controller 命名

**Class basename = resource 名，前綴只在跟 scope 段重複時削掉**。三條判定：

1. **削**：當 class 前綴 = 父層 module / scope folder 名（表達「資料範圍 / 模組分類」的段），削掉前綴
2. **保留**：當 class basename = resource 名（最末層 folder 名），保留是 Laravel 慣例
3. **保留**：當 class basename = `{父 resource}{子 resource}` 表達 sub-resource 關係（如 `EmployeeLeaveController` 處理 Employee 的 Leave 子資源），保留有意義

#### 6.4.1 範例對照

| 結構位置 | class basename | 理由 |
|---|---|---|
| `Modules/Catalog/Product/ProductController.php` | `ProductController` | `Product` 是 resource 名（最末層） |
| `Modules/Catalog/OptionValueGroup/OptionValueGroupController.php` | `OptionValueGroupController` | 複合 resource 名整體保留 |
| `Core/Controllers/System/Acl/UserController.php` | `UserController` | 削除 `System` / `Acl` 前綴 |
| `Core/Controllers/System/Acl/UserDeviceController.php` | `UserDeviceController` | 五層其他四層 token 都用 `user_device`；audience scope（admin 管全員 vs 個人管自己）由 path + permission 承載，class basename 不重複表達（audience 分流見 [§6.7](#67-self-service-邊界account-維度-vs-業務-module-維度)） |

#### 6.4.2 撞名處理

削掉前綴後可能跨 module 出現同名 class（例：`Modules/Catalog/Product/ProductController` 與 `Modules/Sale/Product/ProductController` 都叫 `ProductController`）。處理方式：

```php
use App\Portals\Ocadmin\Modules\Catalog\Product\ProductController;
use App\Portals\Ocadmin\Modules\Sale\Product\ProductController as SaleProductController;
```

class basename 不為了避免 import alias 而提前 prefix。alias 是 import 點該處理的事，class 本身保持乾淨。

#### 6.4.3 命名空間五層對齊處理

`Core/Controllers/System/Acl/UserController.php` 做五層 token 對齊分析時，`Controllers/` 視為 Laravel-native layer noise（如同 `app/Http/Controllers/UserController.php` 沒人把 `Http/Controllers` 算進 URL 路徑）。剝掉後 token 序列 = `system / acl / user`，與 URL / Permission / View 比對對齊。完整五層座標規則見 [10023 §九](10023_英文名稱單複數規範.md)。

### 6.5 Model 位置

**Model 集中放 `app/Models/{Domain}/{Model}.php`，Modules/ 內不放 Model**（不論真檔或 source-to-publish 副本）。每個 module 配 `README.md` 作為**索引清冊**，列出本 module 涉及的 Model / Migration / View / Permission 路徑，但**不放程式碼**。

```
app/
├── Models/Catalog/Product.php                          ← Eloquent Model 唯一位置
└── Portals/Ocadmin/Modules/Catalog/Product/
    ├── ProductController.php                            ← use App\Models\Catalog\Product
    ├── ProductService.php                               ← 如有
    └── README.md                                        ← 索引（不含程式碼）
```

#### 6.5.1 README 內容範本

```markdown
# Catalog/Product Module

## 對應 Models
- `app/Models/Catalog/Product.php`
- `app/Models/Catalog/ProductOption.php`

## 對應 Migrations
- `database/migrations/..._create_catalog_products_table.php`
- `database/migrations/..._create_catalog_product_options_table.php`

## 對應 Views
- `resources/views/{theme}/catalog/product/`

## 對應 Permission
- `{permission_prefix}.catalog.product.*`
```

純索引、不含 PHP 程式碼。新建 Model 應該用 `php artisan make:model {Domain}/{Name}` 或從範本起手，不是從 README 複製貼上。

#### 6.5.2 為何不採「Module 內放 Model source + publish 到 app/Models/」

- Laravel `vendor:publish` 模式成立的前提是「source 在上游不可變、published 副本本地可改」，intra-repo 第一方模組沒有這個性質
- 同一個 repo 內兩份 Model 檔（source + published）必然 diverge，沒人會記得改完 published 同步回 source
- Model 跨模組關聯（`belongsTo` / `hasMany`）使「個別 module 獨立 publish」不成立
- 真正的「自包含模組」目標靠 README 文件層面達成即可，不必程式碼層面 duplicate

### 6.6 選單樹層級獨立於五層對齊

[10023 §九](10023_英文名稱單複數規範.md) 的五層只含 URL / Route / Permission / Controller / View；選單樹（sidebar 母選單與子項）是 UX 層，跟五層正交。

判斷選單何時開中介層 = 純 UX 考量：

- 同類 link ≥ 2 個 → 適合開中介層作為母選單群組
- 母選單下平鋪過多項 → 適合開中介層避免視覺擁擠

實例：

| 選單 | 中介層開啟情形 |
|---|---|
| 詞彙（Vocabulary）群 | 選單樹開中介層；Controller 沒開（Taxonomy / Term 平鋪 `Core/Controllers/System/`） |
| ACL 群 | 兩邊都開（5 個 controller sibling 滿足 [§6.3.1 雙條件](#631-中介層雙條件)） |

選單樹是否開中介層不影響 Controller / URL / Permission 五層的對齊判定，反之亦然。

### 6.7 Self-service 邊界：Account 維度 vs 業務 module 維度

帳號自助操作（user 對自己做的事）依「**對誰 / 做什麼**」拆兩個正交維度：

| 維度 | 是什麼 | 位置 | 範例 |
|---|---|---|---|
| **Account 維度** | 「我這個人」— 個人帳號屬性管理 | `Core/Controllers/Account/`（模板必備）+ `Modules/Account/`（專案自加） | Profile / Password / UserDevice / Notification |
| **業務 module 維度** | 「我做的事」— 業務行為 self-service vs admin 視角 | `Modules/{Domain}/{Resource}/{User\|Admin}Controller`（同 module 內兩支 controller） | Leave / Attendance / Salary 的 self-service 與 admin view |

兩維度彼此正交：Profile 屬 Account（個人資料）、請假申請屬業務 module（HR 業務），不混雜也不互相吸收。

#### 6.7.1 Account 維度：Core/ vs Modules/ 分界

Account 維度進一步以「**模板必備 vs 專案自加**」分流，是 [§6.1](#61-為何-core-與-modules-結構不同) Core / Modules 邊界原則的具體展開：

| 子層 | 性質 | 範例 | 修改頻率 |
|---|---|---|---|
| `Core/Controllers/Account/` | 模板隨附的必備 self-service | `ProfileController`（未來可擴 `PasswordController` 等） | 低 |
| `Modules/Account/` | 專案自加、各 fork 可增刪 | `UserDeviceController`、未來如 `NotificationController` | 中 |

分界判定 = 「**每個 ocadmin 都該有的 baseline** vs **個別專案選用的延伸**」。Profile 是每個 ocadmin 都必備（user 需能改自己的密碼 / 顯示名），UserDevice / Notification 屬可裁切的延伸。

> **UserDevice 啟用前提（auth_driver token-based）**：UserDevice 模組本質是「裝置 token 註冊 + 撤銷」，session-based auth 沒有「裝置」概念（cookie 是 browser session 不是 device）。啟用 `Modules/Account/UserDeviceController` → `config/portals.ocadmin.auth_driver` 必須為 token-based（`sanctum` / `oauth`）。詳見 [10009 §1](10009_UserDevice裝置管理.md)。
>
> 2FA 是登入額外驗證 challenge，**非** UserDevice 的強制前提（兩者邏輯獨立）。

#### 6.7.2 業務 module 維度：User / Admin 雙 controller pattern

業務 module 同時有 self-service 與管理視角時，於同一 module 內開兩支 controller，permission scope 不同：

```
Modules/Hr/                            ← 業務 domain
└── Leave/                             ← 業務 resource（如 Leave / Attendance / Salary）
    ├── UserController.php             ← 我看我的請假紀錄 / 提出申請
    └── AdminController.php            ← HR / 主管審批 / 看全員請假
```

對應 routes / permissions 拆兩段，scope 由 path + permission 承載：

```php
Route::get('/my/leaves',    [UserController::class,  'index'])->middleware('can:hrm.leave.user.access');
Route::get('/admin/leaves', [AdminController::class, 'index'])->middleware('can:hrm.leave.admin.access');
```

class basename `UserController` / `AdminController` 表達 **audience**（誰看的），不重複表達業務 scope（業務 scope 由 module folder + permission 承載）。跨 module 撞名（同名 `UserController` 跨業務 module）靠 import alias 處理（[§6.4.2](#642-撞名處理)）。

> **ocadmin 範本層只示範 Account 維度，不落地業務 module 範例**：ocadmin 走電商視角（Catalog / Member / Org），HR / 差勤等 self-service 業務 module 屬衍生專案範圍。本節 pattern 規範供衍生 branch（projBaz / projFoo 等）落地參考；ocadmin 範本層的 self-service 實證是 `Core/Controllers/Account/ProfileController` 與 `Modules/Account/UserDeviceController`，皆屬 Account 維度。

---

## 七、新增 Portal 步驟

以新增 `POS` Portal 為例。資料夾結構規則見 [§六](#六portal-內資料夾階層)，本節聚焦操作步驟。

### Step 1：`config/portals.php` 登記

```php
'pos' => [
    'url_slug'    => 'pos',
    'role_prefix' => 'pos',
    'dir'         => 'Pos',
],
```

> **命名慣例**：array key snake_case（含版本號，如 `pos_catering_v3`）；`url_slug` kebab-case；`role_prefix` / `permission_prefix` snake_case；`dir` PascalCase（對齊 PSR-4 namespace，例如 `PosCateringV3`）。

### Step 2：建立目錄結構

POS 不從後台範本繼承任何東西，因此**不開 `Core/`**（判準見 [§6.0](#60-core-不是每個-portal-都有)），layer 資料夾直接放 portal 根層：

```
app/Portals/Pos/
├── Controllers/
│   └── PosController.php              ← 基底 Controller
├── Providers/
│   └── PosServiceProvider.php         ← 註冊路由、視圖 namespace
├── Services/                          ← 按需；跨 Module 共用才開（見 §6.0.1）
├── Modules/
│   └── Sale/
│       └── Order/
│           └── OrderController.php
├── resources/
│   └── views/
│       └── pos/                       ← 視圖 namespace 同名子層；對應 `pos::xxx`
└── routes/
    └── pos.php

lang/                                  ← 專案根目錄（不在 Portal 內）
├── en/pos/                            ← `__('pos/...')`
└── zh_Hant/pos/
```

> 若新 Portal 確實是從後台範本 fork 出來的（繼承 Auth / Account / System / Common 那一整套），才照 [§6.2](#62-core-結構) 開 `Core/`，並把繼承來的東西放進去。

> **語系檔放在專案根目錄 `lang/` 而非 Portal 內**：所有 Portal 的語系檔集中在頂層 `lang/{locale}/{namespace}/`，由 Laravel 預設機制載入（`__('pos/...')` 直接可用），無需在 ServiceProvider 註冊。詳見 [10002 多語機制](10002_多語機制.md)。

### Step 3：建立 ServiceProvider

ServiceProvider 的 `boot()` 內**明確指定來源路徑**（路由檔、視圖 namespace、語系檔如需）。每條路徑都用 `app_path()` / `lang_path()` 寫絕對位置：

```php
namespace App\Portals\Pos\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class PosServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // 1. 路由檔
        Route::middleware(['web'])
            ->group(app_path('Portals/Pos/routes/pos.php'));

        // 2. 視圖 namespace：`pos::xxx` → app/Portals/Pos/resources/views/pos/xxx.blade.php
        View::addNamespace('pos', app_path('Portals/Pos/resources/views/pos'));

        // 3. 語系檔：放在專案根目錄的 `lang/{locale}/pos/`
        //    用 `__('pos/...')` 直接存取，不需 loadTranslationsFrom；
        //    詳見 10002_多語機制.md「不使用 namespace」段。
    }
}
```

> 視圖 namespace 採 `View::addNamespace($name, $path)`（不是 `loadViewsFrom`），因為 Portal 是專案內部一等公民，不走 vendor 套件的 publish 流程。實際 path 比 namespace 多一層同名資料夾（`resources/views/pos/`），與 Ocadmin Portal 一致。

### Step 4：在 `bootstrap/providers.php` 註冊

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Portals\Ocadmin\Core\Providers\OcadminServiceProvider::class,
    App\Portals\Pos\Providers\PosServiceProvider::class,  // 新增
];
```

### Step 5：設定路由（pos.php）

```php
use App\Portals\Pos\Modules\Sale\Order\OrderController;

Route::group([
    'prefix'     => '{locale}/pos',
    'as'         => 'lang.pos.',
    'middleware' => ['setLocale', 'auth', 'requirePortalRole:' . config('portals.pos.role_prefix')],
], function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
});
```

### Step 6：建立基底 Controller

```php
namespace App\Portals\Pos\Controllers;

use App\Http\Controllers\Controller;

abstract class PosController extends Controller
{
    // Portal 共用邏輯
}
```

### Checklist

- [ ] `config/portals.php` 登記 `url_slug`、`role_prefix`、`permission_prefix`、`dir`
- [ ] 建立 `app/Portals/{Dir}/` 目錄結構（依 [§六](#六portal-內資料夾階層)；有從範本繼承才開 `Core/`，否則 layer 資料夾直接放 portal 根層）
- [ ] 建立 `ServiceProvider`，註冊路由與視圖
- [ ] 在 `bootstrap/providers.php` 加入 ServiceProvider
- [ ] 建立基底 Controller（繼承 `App\Http\Controllers\Controller`）
- [ ] 建立路由檔，套用 `requirePortalRole:{role_prefix}` middleware
- [ ] 為 Portal 使用者建立角色（`{role_prefix}.{role_name}`）

---

## 八、雙模架構：前後台分離 vs ocadmin 兼任前台

Ocadmin 系列支援兩種部署模式，由 `config/portals.php` 的 `ocadmin.web_enabled` 與前台 portal 段（範本層為 `webv1`）是否存在的組合決定。

### 8.1 兩種模式

**Mode A — 前後台分開**

> **注意**：這裡說的是「前後**台**分開」（front-office vs back-office：對外的官網 / 客戶介面 vs 對內的管理介面），**不是「前後端分離」**（frontend vs backend：SPA + API 的技術架構切分）。Mode A / Mode B 都可選任意前端技術；技術選型見 [§五](#五技術選型與共用資源)。

完整正式專案，前台對外（客戶 / 會員 / 大眾使用者），後台 ocadmin 給管理員 / 員工 / 客服。範例：電商系統。

- DB `users` 表存登入帳號（包含 backend admin 與 frontend member 兩種身份）
- `members` 表存前台會員業務 record（透過 `user_id` 關聯 `users` 表）
- 業務語義分流：**前台 = members**（會員 / 客戶）／ **後台 = users**（管理員 / 員工 / 客服）

**Mode B — ocadmin 兼任前台（單應用）**

無獨立前台 portal，所有人走 ocadmin。範例：差勤系統等內部工具。

- DB `users` 表存登入帳號
- `employees` 表（或對應業務實體表）存業務 record（透過 `user_id` 關聯）
- 不存在「member」概念

### 8.2 共同 invariant

- DB `users` 表永遠存在
- Spatie permission 系統處理 role / permission；程式邏輯不在乎 user 業務語義（會員 / 管理員 / 員工），只在乎 permission
- **登入 ocadmin 的硬條件**：必須擁有至少一個 `{role_prefix}.*` 開頭的角色（prefix 來自 `config/portals.php` ocadmin.role_prefix；預設 `admin`）

### 8.3 切換機制

`config/portals.php` 內 `ocadmin.web_enabled` 永遠存在；前台 portal 段（範本層為 `webv1`）選擇性存在。兩 axis 組合決定模式：

| `ocadmin.web_enabled` | 前台 portal 段（`webv1`） | 模式 | 處置 |
|---|---|---|---|
| `false` | 存在 | **典型 Mode A** — 前後台分離 | 正常運行 |
| `true` | 不存在 | **典型 Mode B** — ocadmin 一套到底 | 正常運行 |
| `false` | 不存在 | admin-only 內部工具 | 正常運行 |
| `true` | 存在 | **衝突態** | boot 階段 throw `LogicException` |

衝突態檢查實作於 `OcadminServiceProvider::boot()` 內 `assertPortalModeConsistency()`。

### 8.4 為什麼 `web_enabled` 放 ocadmin 段

表達的是「ocadmin 自己是否兼任前台」（Mode B 的核心 idea），不是「是否有獨立前台」。語意以正面方式表達 Mode B，避免「沒有 `webv1` 段 = Mode B」這種隱晦的負面定義。

`web_enabled` 永遠存在 = 每個 fork 都意識到此 axis 存在（不會 silently 走錯模式）；衍生專案 `false → true` 是明示的 mode 切換動作。

---

## 相關文件

- [10000_系統架構.md](10000_系統架構.md) — 全系統架構總覽
- [10007_權限機制.md](10007_權限機制.md) — 角色 / 權限命名規範、Spatie 設定、prefix 注入機制、權限檢查方式
- [10016_架構分層職責.md](10016_架構分層職責.md) — Controller / Service / Repository / Model 職責、何時抽 Service、Service 三層作用範圍判準
- [10023_英文名稱單複數規範.md](10023_英文名稱單複數規範.md) — 兩軸命名規範：橫向（各層單複數）+ 縱向（五層階層座標對齊）
- [10026_OAuth帳號中心整合.md](10026_OAuth帳號中心整合.md) — OAuth driver 細節
- [00003_Ocadmin程式規範.md](00003_Ocadmin程式規範.md) — Ocadmin Portal 完整開發規範
