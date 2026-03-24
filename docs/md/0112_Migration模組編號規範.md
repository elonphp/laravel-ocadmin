# Migration 模組編號規範

## 背景

Laravel 12+ 的內建 migration 改用 `0001_01_01_000000` 格式取代舊版日期時間戳記（`2024_08_15_123456`）。
本專案群沿用此格式，擴展為**模組化編號系統**，讓業務模組擁有獨立的編號區段。

## 適用專案

| 專案 | 說明 | 主要使用區段 |
|------|------|-------------|
| 基礎專案 | 共用基礎系統，所有專案的根基 | 01–04, 10, 20 |
| 人事系統 | 出勤、薪資、保險等 HR 功能 | 01–04, 10–13 |
| 電商系統 | 商品型錄、訂單、進銷存 | 01–04, 10, 20, 23 |
| B2B 客製系統 | B2B 銷售、授權方案 | 01–04, 10, 20, 33 |

基礎專案定義共用的 Foundation（01–09）與組織架構（10），其餘專案在此之上各自擴充業務模組。
所有專案共用同一套編號規範，按需取用不同區段。

## 格式定義

```
0001_01_MM_NNNNNN_create_xxx_table.php
│    │  │  │
│    │  │  └── 模組內序號（6 位數）
│    │  └── 模組代碼（2 位數，01–99）
│    └── 固定值
└── 層級代碼
```

| 欄位 | 位置 | 說明 |
|------|------|------|
| `0001` | 第 1 段 | **層級代碼**（見下方分層說明） |
| `01` | 第 2 段 | 固定值 |
| `MM` | 第 3 段 | **模組代碼**，2 位數（01–99），按區段分群 |
| `NNNNNN` | 第 4 段 | **模組內序號**，6 位數 |

### 分層機制

| 層級代碼 | 用途 | 說明 |
|----------|------|------|
| `0001` | 通用模組 | 標準功能，各專案按需取用。大部分 migration 屬於此層 |
| `0002` | 專案客製 | 特定專案的獨特商業邏輯，其他專案用不到 |

Migration 按檔名排序執行，`0001_*` 全部先跑完，`0002_*` 再跑。
因此客製層可自由引用通用層的任何表，不需擔心 FK 依賴順序。

> **判斷原則**：如果一張表只有特定客戶會用，且不太可能被其他專案複用，就放 `0002`。
> 反之，即使目前只有一個專案在用（如 HRM 出勤），只要是標準模組功能，仍歸 `0001`。

## 開發階段原則

所有專案目前處於開發階段，適用以下原則：

1. **不使用 alter table migration** — 欄位變更直接修改主 create migration 檔案
2. **不使用日期格式** — 消除所有 `2026_*` 開頭的 migration，統一為 `000N_01_MM_NNNNNN`
3. **`migrate:fresh` 重建** — 開發環境隨時可重建，不需擔心向後相容
4. **跨模組 FK（同層）** — 若兩張表分屬不同 MM，低 MM 的表可宣告 nullable FK 欄位（不加 `constrained()`），由 Eloquent 關聯保障完整性
5. **跨模組 FK（跨層）** — 客製層（`0002`）可自由對通用層（`0001`）的表加 FK constraint，因為通用層一定先執行完畢

---

## 模組區段總覽

| MM 區段 | 類別 | 說明 |
|---------|------|------|
| **01–09** | 基礎設施 Foundation | 框架核心、共用資料、權限、系統工具 |
| **10–19** | 人資行政 HR & Admin | 組織架構、員工、出勤、薪資、資產 |
| **20–29** | 電商零售 E-commerce | 商品型錄、定價、訂單、金流、物流、POS |
| **30–39** | 進銷存 / 貿易 Supply Chain | 供應商、採購、庫存、銷售、物流配送 |
| **40–49** | 製造品管 Manufacturing | BOM、製程、品管、維修、MRP |
| **50–59** | 財務稅務 Finance | 總帳、應收付、發票、稅務 |
| **60–69** | CRM 客戶關係 | 客戶管理、行銷活動 |
| **70–79** | CMS 內容管理 | 文章、頁面、Banner |
| **80–89** | 報表監控 Analytics | 自訂報表、營運監控 |
| **90–99** | 保留 Reserved | 行動化、整合、未來擴充 |

---

## 詳細模組規劃

### 01–09 基礎設施 Foundation

| MM | 模組 | 內容 |
|----|------|------|
| `01` | Laravel Core | users, password_reset_tokens, sessions, cache, jobs, personal_access_tokens, user_devices |
| `02` | 共用資料 Common | countries, currencies, languages, units, timezones |
| `03` | ACL 權限 | permission_tables (spatie), acl_role_translations, acl_permission_translations, acl_portal_users |
| `04` | 系統工具 System | settings, taxonomy/terms, request_logs, notifications |
| `05` | API 接口 Integration | webhooks, API keys（保留） |
| `06` | 個資保護 Privacy | consent_logs, data_retention（保留） |
| `07` | 序號管理 Sequence | sequences, serial_rules（保留） |
| `08` | 通知訊息 Messaging | notification_channels, templates（保留） |
| `09` | 工作流程 Workflow | workflow_definitions, steps（保留） |

#### MM=01 Laravel Core 序號規劃

| NNNNNN | 表 | 說明 |
|--------|------|------|
| `000000` | users | 使用者主表（含 password_reset_tokens、sessions） |
| `000001` | cache | 快取 |
| `000002` | jobs | 佇列 |
| `000005` | personal_access_tokens | Sanctum API Token |
| `000010` | user_devices | 使用者裝置 |

#### MM=02 共用資料 序號規劃

| NNNNNN | 表 | 說明 | 專案 |
|--------|------|------|------|
| `000001` | countries | 國家（ISO 3166） | Sunline |

#### MM=03 ACL 權限 序號規劃

| NNNNNN | 表 | 說明 |
|--------|------|------|
| `000001` | acl_tables | spatie roles + permissions + model_has_roles 等，含 acl_role_translations、acl_permission_translations |
| `000010` | acl_portal_users | Portal 使用者對應 |

#### MM=04 系統工具 序號規劃

| NNNNNN | 表 | 說明 |
|--------|------|------|
| `000001` | settings | 系統設定 |
| `000010` | taxonomy_term_tables | 分類法＋詞彙 |
| `000020` | request_logs | HTTP 請求日誌 |

### 10–19 人資行政 HR & Admin

> **表名前綴**：本區段資料表在基礎專案與人事系統使用 `hrm_` 前綴。
> 電商系統不加前綴。

| MM | 模組 | 內容 |
|----|------|------|
| `10` | 組織架構 Organization | organizations, companies, departments, company_user, employees, positions, stores |
| `11` | 出勤管理 Attendance | calendar, clock_records, daily_attendances, monthly_summaries, attendance_exceptions |
| `12` | 薪資計算 Payroll | salary, payroll |
| `13` | 保險福利 Insurance | insurance, benefits |
| `14` | 請假排休 Leave | leave_types, leave_requests（保留） |
| `15` | 零用金 Petty Cash | petty_cash, reimbursements（保留） |
| `16` | 專案管理 Project | projects, tasks, milestones（保留） |
| `17` | 固定資產 Asset | assets, asset_categories, depreciation（保留） |
| `18–19` | 保留 | |

#### MM=10 組織架構 序號規劃

| NNNNNN | 表 | FK | 說明 |
|--------|------|-----|------|
| `000001` | organizations | — | 集團 / 頂層組織 |
| `000002` | companies | organization_id | 公司（基礎/人事: `hrm_companies`） |
| `000003` | departments | company_id | 部門（基礎/人事: `hrm_departments`） |
| `000004` | company_user | company_id, user_id | 公司-使用者 pivot |
| `000010` | employees | user_id | 員工（基礎/人事: `hrm_employees`） |
| `000011` | positions | — | 職位（保留） |
| `000012` | contracts | employee_id | 合約（保留） |
| `000020` | stores | company_id | 門市（電商系統） |

> **B2B 客製系統**的 `organization_option_values` 屬於專案客製，歸入 `0002_01_10_000030`。

#### MM=11 出勤管理 序號規劃（人事系統）

| NNNNNN | 表 | 說明 |
|--------|------|------|
| `000001` | hrm_calendar_tables | 行事曆（含 holidays） |
| `000002` | hrm_clock_records | 打卡紀錄 |
| `000003` | hrm_daily_attendances | 日出勤（含 approved_times） |
| `000004` | hrm_monthly_summaries | 月出勤彙總 |
| `000010` | hrm_attendance_exceptions | 出勤異常 |

#### MM=12 薪資計算 序號規劃（人事系統）

| NNNNNN | 表 | 說明 |
|--------|------|------|
| `000001` | hrm_salary_tables | 薪資結構 |
| `000002` | hrm_payroll_tables | 薪資計算 |

#### MM=13 保險福利 序號規劃（人事系統）

| NNNNNN | 表 | 說明 |
|--------|------|------|
| `000001` | hrm_insurance_tables | 勞健保 |

### 20–29 電商零售 E-commerce & Retail

大模組，拆成多個子模組，每個 MM 值代表一個子模組。

| MM | 子模組 | 表名前綴 | 內容 |
|----|--------|---------|------|
| `20` | 商品型錄 Catalog | `ctl_` / `clg_` | options, categories, products, BOM, authorization_plans |
| `21` | 定價行銷 Pricing | 待定 | specials, discounts, coupons, gift_vouchers |
| `22` | 客戶會員 Customer | 待定 | customer_groups, addresses, wishlist, reward_points |
| `23` | 訂單 Orders (B2C) | `ord_` | orders, order_products, order_payments, carts, returns |
| `24` | 物流 Shipping | 待定 | shipping_methods, geo_zones, tax_classes, tax_rates |
| `25` | 金流 Payment | 待定 | payment_methods, transactions |
| `26` | 店面佈局 Storefront | 待定 | banners, layouts, layout_modules |
| `27` | POS / 連鎖加盟 | `pos_` | terminals, sessions, franchise |
| `28` | 新零售 New Retail | 待定 | — |
| `29` | 保留 | | |

#### MM=20 商品型錄 序號規劃

> **原則**：基本資料定義先建（000001–000099），
> 商品主體（000200–000299），商品關聯表（000300+），BOM（000400+）。

| NNNNNN | 表 | 說明 |
|--------|------|------|
| | **基本資料定義** | |
| `000001` | ctl_options（含 translations） | 選項定義 |
| `000003` | ctl_option_values（含 translations） | 選項值 |
| `000010` | ctl_option_value_groups | 選項值群組 |
| `000015` | ctl_option_value_links | 選項值連動（cascading） |
| `000020` | ctl_categories（含 translations） | 商品分類（保留） |
| `000030` | ctl_attributes（含 translations） | 屬性（保留） |
| `000031` | ctl_attribute_groups | 屬性群組（保留） |
| `000040` | ctl_filters | 篩選器（保留） |
| `000050` | ctl_manufacturers | 製造商（保留） |
| | **商品主體** | |
| `000200` | ctl_products（含 translations） | 商品主表 |
| | **商品關聯表** | |
| `000300` | ctl_product_categories | 商品-分類（保留） |
| `000301` | ctl_product_stores | 商品-門市（保留） |
| `000302` | ctl_product_terms | 商品-詞彙 |
| `000310` | ctl_product_options | 商品-選項 |
| `000311` | ctl_product_option_values | 商品選項值（保留） |
| | **BOM 物料清單** | |
| `000400` | ctl_bom_tables | 產品 BOM |

> **B2B 客製系統差異**：使用 `clg_` 前綴取代 `ctl_`，表名如 `clg_options`、`clg_option_values` 等。
> 授權方案（authorization_plans）屬於專案客製，歸入 `0002_01_20`（見 B2B 客製系統目標檔案）。

#### MM=23 訂單 序號規劃（電商系統）

| NNNNNN | 表 | 說明 |
|--------|------|------|
| `000001` | ord_orders | 訂單主表 |
| `000002` | ord_order_statuses | 訂單狀態 |
| `000005` | ord_order_payments | 付款紀錄 |
| `000010` | ord_order_products | 訂單商品明細 |
| `000011` | ord_order_options | 訂單商品選項 |
| `000020` | ord_order_totals | 訂單金額小計 |
| `000021` | ord_order_histories | 訂單歷史紀錄 |
| `000030` | ord_carts | 購物車 |
| `000031` | ord_cart_products | 購物車商品 |
| `000040` | ord_returns | 退貨 |
| `000041` | ord_return_reasons | 退貨原因 |

### 30–39 進銷存 / 貿易 Supply Chain & Trade

| MM | 子模組 | 表名前綴 | 內容 |
|----|--------|---------|------|
| `30` | 供應商 Suppliers | — | suppliers, contacts, contracts |
| `31` | 採購 Procurement | — | purchase_orders, purchase_items, receiving |
| `32` | 庫存 Inventory | `inv_` | warehouses, stock, transfers, adjustments |
| `33` | 銷售 Sales (B2B) | `sal_` | orders, quotations |
| `34` | 合約 Contract | — | contracts, terms |
| `35` | 物流配送 Distribution | — | routes, shipments |
| `36` | 貿易管理 Trade | — | trade_orders |
| `37` | 進出口 Import/Export | — | customs, declarations |
| `38` | 行業專用 | — | 軍福品等 |
| `39` | 保留 | | |

#### MM=33 銷售 序號規劃（B2B 客製系統）

| NNNNNN | 表 | 說明 |
|--------|------|------|
| `000001` | sal_orders | 銷售訂單（含 salesperson / dealer snapshot） |
| `000010` | sal_order_products（含 translations） | 訂單商品明細 |
| `000011` | sal_order_product_options（含 translations） | 訂單商品選項 |
| `000020` | sal_order_comments | 訂單備註 |

### 40–49 製造品管 Manufacturing & Quality

| MM | 模組 | 內容 |
|----|------|------|
| `40` | 產品結構 BOM | bom_headers, bom_components（製造級） |
| `41` | 製程管理 SFC | work_centers, routings, operations |
| `42` | 製令 / 託外 Work Order | work_orders, outsource_orders |
| `43` | 品質管理 QMS | inspections, defects, corrective_actions |
| `44` | 維修服務 RMA | repair_orders, warranty, service_tickets |
| `45` | 物料需求 MRP | mrp_runs, planned_orders |
| `46` | 成本計算 Costing | cost_centers, cost_elements |
| `47–49` | 保留 | |

> **註**：電商系統的 `ctl_bom_tables`（MM=20）是產品級 BOM（商品由哪些子項組成），
> 與本區段的製造級 BOM（生產工序、用料比例）層次不同。

### 50–59 財務稅務 Finance & Tax

| MM | 模組 | 內容 |
|----|------|------|
| `50` | 會計總帳 GL | accounts, chart_of_accounts, periods |
| `51` | 應付帳款 AP | payables, payment_schedules |
| `52` | 應收帳款 AR | receivables, collection_records |
| `53` | 自動分錄 AJS | journal_templates, auto_entries |
| `54` | 票據資金 Notes | notes, bills, fund_transfers |
| `55` | 稅務申報 Tax | tax_returns, tax_filings |
| `56` | 電子發票 E-Invoice | einvoice_records, exchange_logs |
| `57` | 商業會計 Accounting Standards | ifrs_mappings |
| `58` | 合併財報 Consolidated | consolidation_sets, elimination_entries |
| `59` | 保留 | |

### 60–89 CRM / CMS / 報表

| MM | 模組 | 內容 |
|----|------|------|
| `60` | 客戶管理 CRM | contacts, opportunities, pipelines |
| `61` | 行銷活動 Marketing | campaigns, email_templates |
| `62–69` | CRM 保留 | |
| `70` | 文章 Articles | articles, article_categories, translations |
| `71` | 頁面 Pages | pages, menus, FAQ |
| `72–79` | CMS 保留 | |
| `80` | 自訂報表 Report | report_templates, saved_reports |
| `81` | 營運監控 Dashboard | dashboards, alerts, KPIs |
| `82–89` | 報表保留 | |

### 90–99 保留 Reserved

| MM | 模組 | 內容 |
|----|------|------|
| `90` | 行動化 Mobile | mobile_configs, push_tokens |
| `91` | 線上整合 Online | integration_endpoints, sync_logs |
| `92–99` | 未來擴充 | |

---

## 模組內序號規則（NNNNNN）

每 10 號為一群組，同群組的表彼此相關，群組之間保留空間方便日後插入。
表規模較大時改用每 100 號一群組。

```
000001–000009  主表（核心實體）
000010–000019  第一組關聯表
000020–000029  第二組關聯表
000030–000039  第三組關聯表
...
000100–000199  第一大群組（角色/分支）
000200–000299  第二大群組
000300–000399  第三大群組
...
```

### 範例：多角色使用者 `0001_01_01_NNNNNN`

```
                    ┌── members  (FK: user_id)  前台會員
                    │     └── member_profiles
        users ──────┤
                    ├── admins   (FK: user_id)  後台管理員
                    │     └── admin_action_logs
                    │
                    └── accounts (FK: user_id)  帳戶
                          └── account_settings
```

| NNNNNN | 表 | FK | 說明 |
|--------|------|-----|------|
| `000000` | users | — | 使用者主表（Laravel 內建） |
| `000001` | password_reset_tokens | — | 密碼重設（Laravel 內建） |
| `000002` | sessions | — | 工作階段（Laravel 內建） |
| `000100` | members | user_id | 前台會員 |
| `000101` | member_profiles | member_id | 會員個人資料 |
| `000200` | admins | user_id | 後台管理員 |
| `000201` | admin_action_logs | admin_id | 管理員操作日誌 |
| `000300` | accounts | user_id | 帳戶 |
| `000301` | account_settings | account_id | 帳戶偏好設定 |
| `000302` | account_transactions | account_id | 帳戶交易紀錄 |

### 範例：電商型錄 `0001_01_20_NNNNNN`

> **原則**：基本資料定義先建（000001–000199），商品主體次之（000200–000299），
> 商品關聯表最後（000300+），確保 FK 依賴順序正確。

| NNNNNN | 表 | 說明 |
|--------|------|------|
| | **基本資料定義（不依賴 products）** | |
| `000001` | ctl_options（含 translations） | 選項 |
| `000003` | ctl_option_values（含 translations） | 選項值 |
| `000010` | ctl_option_value_groups | 選項值群組 |
| `000020` | ctl_categories（含 translations） | 商品分類 |
| `000030` | ctl_attributes（含 translations, groups） | 屬性 |
| `000040` | ctl_filters | 篩選器 |
| `000050` | ctl_manufacturers | 製造商 |
| | **商品主體** | |
| `000200` | ctl_products（含 translations） | 商品主表 |
| | **商品關聯表（FK→products + 基本資料）** | |
| `000300` | ctl_product_categories | 商品-分類 |
| `000301` | ctl_product_stores | 商品-門市 |
| `000302` | ctl_product_terms | 商品-詞彙 |
| `000310` | ctl_product_options | 商品-選項 |
| `000311` | ctl_product_option_values | 商品選項值 |
| | **BOM 物料清單** | |
| `000400` | ctl_bom_tables | 產品 BOM |

---

## 表名前綴規則

業務模組的資料表加上模組前綴，好處：

- **資料庫瀏覽時自然分群**：同前綴的表排在一起
- **避免命名衝突**：`companies` 太通用，`hrm_companies` 不會混淆
- **程式碼語意清晰**：`HrmCompany::class` 一看就知道屬於 HR 模組

| 區段 | 前綴 | 範例 |
|------|------|------|
| 01–09 Foundation | 無（Laravel 慣例） | users, cache, jobs, settings |
| 10–19 HR（基礎/人事） | `hrm_` | hrm_companies, hrm_employees |
| 10 組織架構（電商） | 無 | companies, departments, stores |
| 20 Catalog（電商） | `ctl_` | ctl_products, ctl_options |
| 20 Catalog（B2B 客製） | `clg_` | clg_options, clg_option_values |
| 23 Orders | `ord_` | ord_orders, ord_order_products |
| 32 Inventory | `inv_` | inv_warehouses, inv_stock |
| 33 Sales | `sal_` | sal_orders, sal_order_products |

> **Foundation 不加前綴**：users、cache、jobs 等 Laravel 內建表維持原名。
> 第三方套件的表（如 spatie permission）也維持原名，避免與套件衝突。

---

## 設計理念

### 1. 大模組給區段，小模組給單號

電商 (20–29)、進銷存 (30–39)、製造 (40–49)、財務 (50–59) 各預留 10 個 MM 值。
出勤 (`11`)、薪資 (`12`) 規模較小，各佔一個 MM 值即可。

### 2. 執行順序天然正確

Migration 按檔名排序執行：

```
Foundation (01-09) → HR (10-19) → E-commerce (20-29) → ERP (30-39) → ... → Reserved (90-99)
```

基礎表先建，業務表後建，外鍵依賴順序自然滿足。

### 3. 序號間距留空間

模組內每 10 號一組，日後加新表只需在間距中插入，不影響既有檔案。

### 4. 擴充無痛

MM 值 40–99 大量保留，未來加入 CRM、BI、POS、製造、財務等模組都有空間。
每個模組區段也預留了 buffer（如 HR 的 14–19、電商的 28–29）。

### 5. 四專案共用一套規則

基礎專案只用 01–04 + 10 + 20，人事系統多用 11–13，電商系統多用 20 + 23。
各自按需取用，編號不衝突。

---

## 各專案目標檔案清單

以下為整理完成後各專案 `database/migrations/` 的目標狀態。

### 基礎專案

```
# MM=01 Laravel Core
0001_01_01_000000_create_users_table.php
0001_01_01_000001_create_cache_table.php
0001_01_01_000002_create_jobs_table.php
0001_01_01_000005_create_personal_access_tokens_table.php
0001_01_01_000010_create_user_devices_table.php

# MM=03 ACL
0001_01_03_000001_create_acl_tables.php                  ← 合併: spatie + translations + menu_fields
0001_01_03_000010_create_acl_portal_users_table.php

# MM=04 System
0001_01_04_000001_create_settings_table.php              ← 合併: settings + is_autoload
0001_01_04_000010_create_taxonomy_term_tables.php
0001_01_04_000020_create_request_logs_table.php

# MM=10 Organization
0001_01_10_000001_create_organizations_table.php
0001_01_10_000002_create_hrm_companies_table.php
0001_01_10_000003_create_hrm_departments_table.php
0001_01_10_000010_create_hrm_employees_table.php

# MM=20 Catalog
0001_01_20_000001_create_option_tables.php
0001_01_20_000010_create_option_value_group_tables.php
0001_01_20_000200_create_product_tables.php
```

共 17 檔（原 22 檔，合併 alter-table 與 2026_* 格式）

### 電商系統

```
# MM=01 Laravel Core
0001_01_01_000000_create_users_table.php
0001_01_01_000001_create_cache_table.php
0001_01_01_000002_create_jobs_table.php
0001_01_01_000005_create_personal_access_tokens_table.php

# MM=03 ACL
0001_01_03_000001_create_permission_tables.php           ← 合併: spatie + acl_translations
0001_01_03_000010_create_acl_portal_users_table.php

# MM=04 System
0001_01_04_000001_create_settings_table.php
0001_01_04_000010_create_taxonomy_term_tables.php
0001_01_04_000020_create_request_logs_table.php

# MM=10 Organization
0001_01_10_000001_create_organizations_table.php
0001_01_10_000002_create_companies_table.php
0001_01_10_000003_create_departments_table.php
0001_01_10_000004_create_company_user_table.php
0001_01_10_000020_create_stores_table.php

# MM=20 Catalog
0001_01_20_000001_create_ctl_option_tables.php
0001_01_20_000200_create_ctl_product_tables.php
0001_01_20_000302_create_ctl_product_terms_table.php
0001_01_20_000310_create_ctl_product_option_tables.php
0001_01_20_000400_create_ctl_bom_tables.php

# MM=23 Orders
0001_01_23_000001_create_ord_order_tables.php
0001_01_23_000005_create_ord_order_payments_table.php
```

共 21 檔（原 22 檔，合併 2026_* 格式）

### 人事系統

```
# MM=01 Laravel Core
0001_01_01_000000_create_users_table.php
0001_01_01_000001_create_cache_table.php
0001_01_01_000002_create_jobs_table.php

# MM=03 ACL
0001_01_03_000001_create_acl_tables.php                  ← 合併: spatie + translations
0001_01_03_000010_create_acl_portal_users_table.php

# MM=04 System
0001_01_04_000001_create_settings_table.php
0001_01_04_000010_create_taxonomy_term_tables.php
0001_01_04_000020_create_request_logs_table.php

# MM=10 Organization
0001_01_10_000001_create_organizations_table.php
0001_01_10_000002_create_hrm_companies_table.php
0001_01_10_000003_create_hrm_departments_table.php
0001_01_10_000010_create_hrm_employees_table.php         ← 合併: work_schedule + insurance_fields

# MM=11 Attendance
0001_01_11_000001_create_hrm_calendar_tables.php
0001_01_11_000002_create_hrm_clock_records_table.php
0001_01_11_000003_create_hrm_daily_attendances_table.php ← 合併: approved_times
0001_01_11_000004_create_hrm_monthly_summaries_table.php
0001_01_11_000010_create_hrm_attendance_exceptions_table.php

# MM=12 Payroll
0001_01_12_000001_create_hrm_salary_tables.php
0001_01_12_000002_create_hrm_payroll_tables.php

# MM=13 Insurance
0001_01_13_000001_create_hrm_insurance_tables.php
```

共 20 檔（原 28 檔，合併全部 alter-table 與 2026_* 格式）

### B2B 客製系統

```
# ═══ 0001 通用層 ═══

# MM=01 Laravel Core
0001_01_01_000000_create_users_table.php                 ← 合併: shipping_fields + timezone + locale
0001_01_01_000001_create_cache_table.php
0001_01_01_000002_create_jobs_table.php

# MM=02 Common
0001_01_02_000001_create_countries_table.php

# MM=03 ACL
0001_01_03_000001_create_acl_tables.php                  ← 合併: spatie + translations
0001_01_03_000010_create_acl_portal_users_table.php

# MM=04 System
0001_01_04_000001_create_settings_table.php
0001_01_04_000010_create_taxonomy_term_tables.php
0001_01_04_000020_create_request_logs_table.php

# MM=10 Organization
0001_01_10_000001_create_organizations_table.php         ← 含 authorization_plan_id (nullable, 無 FK constraint)

# MM=20 Catalog
0001_01_20_000001_create_clg_option_tables.php           ← 合併: options + extra_columns
0001_01_20_000015_create_clg_option_value_links_table.php ← 合併: links + is_cascadable

# MM=33 Sales
0001_01_33_000001_create_sal_order_tables.php            ← 合併: orders + products + options + comments − section

# ═══ 0002 客製層 ═══

# MM=10 Organization（客製）
0002_01_10_000030_create_organization_option_values_table.php

# MM=20 Catalog（客製）
0002_01_20_000060_create_authorization_plan_tables.php   ← 含 translations + option_values pivot (FK→0001 的 options)
```

共 16 檔（原 23 檔，大量合併 alter-table 與 2026_* 格式）
