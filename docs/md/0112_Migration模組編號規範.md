# Migration 模組編號規範

## 背景

Laravel 12 的內建 migration 已改用 `0001_01_01_000000` 格式取代舊版的日期時間戳記（`2024_08_15_123456`）。
本專案沿用此格式，並擴展為**模組化編號系統**，讓每個業務模組擁有獨立的編號區段。

## 格式定義

```
0001_01_MM_NNNNNN_create_xxx_table.php
│    │  │  │
│    │  │  └── 模組內序號（6 位數）
│    │  └── 模組代碼（2 位數）
│    └── 固定值
└── 固定前綴
```

| 欄位 | 位置 | 說明 |
|------|------|------|
| `0001` | 第 1 段 | 固定前綴，與 Laravel 內建一致 |
| `01` | 第 2 段 | 固定值 |
| `MM` | 第 3 段 | **模組代碼**，2 位數（01–99），按區段分群 |
| `NNNNNN` | 第 4 段 | **模組內序號**，6 位數，每 10 號為一組相關表 |

## 模組區段總覽

| MM 區段 | 類別 | 說明 | 規模 |
|---------|------|------|------|
| **01–09** | 基礎設施 Foundation | 框架核心、共用資料、權限、系統工具 | — |
| **10–19** | 人資行政 HR & Admin | 組織架構、HRM、出勤、資產管理（表名統一 `hrm_` 前綴） | 小–中 |
| **20–29** | 電商 E-commerce | 商品、訂單、客戶、物流、行銷 | 大（預留 10 個子模組） |
| **30–39** | 進銷存 Inventory/ERP | 採購、倉庫、銷售、財務 | 大（預留 10 個子模組） |
| **40–49** | 內容管理 CMS | 文章、頁面、Banner | 中 |
| **50–99** | 保留 Reserved | 未來擴充（CRM、BI、POS…） | — |

---

## 詳細模組規劃

### 01–09 基礎設施 Foundation

| MM | 模組 | 內容 |
|----|------|------|
| `01` | Laravel Core | users, cache, jobs, sessions |
| `02` | 共用資料 Common | countries, currencies, languages, units, timezones |
| `03` | ACL 權限 | roles, permissions, role_translations, permission_translations |
| `04` | 系統工具 System | settings, taxonomy/terms, request_logs, notifications |
| `05–09` | 保留 | |

### 10–19 人資行政 HR & Admin

> **表名前綴規則**：本區段所有資料表統一使用 `hrm_` 前綴，
> 在資料庫中一目了然屬於哪個模組，避免與電商或其他模組的表名衝突。

| MM | 模組 | 內容 |
|----|------|------|
| `10` | HRM 核心（含組織架構） | hrm_organizations, hrm_companies, hrm_departments, hrm_company_user, hrm_employees, hrm_positions, hrm_contracts |
| `11` | 出勤系統 Attendance | hrm_attendance_records, hrm_leave_requests, hrm_overtime_records, hrm_schedules |
| `12` | 資產管理 Asset | hrm_assets, hrm_asset_categories, hrm_depreciation, hrm_maintenance |
| `13–19` | 保留 | 薪資 Payroll、考核 Appraisal、教育訓練 Training… |

#### MM=10 HRM 核心 序號規劃

| NNNNNN | 表 | FK | 說明 |
|--------|------|-----|------|
| `000001` | hrm_organizations | — | 集團 / 頂層組織 |
| `000002` | hrm_companies | organization_id | 公司 |
| `000003` | hrm_departments | company_id | 部門 |
| `000004` | hrm_company_user | company_id, user_id | 公司-使用者 pivot |
| `000010` | hrm_employees | user_id | 員工（員編、到職日…） |
| `000011` | hrm_positions | — | 職位 |
| `000012` | hrm_contracts | employee_id | 合約 |

#### MM=11 出勤系統 序號規劃

| NNNNNN | 表 | FK | 說明 |
|--------|------|-----|------|
| `000001` | hrm_attendance_records | employee_id | 打卡紀錄 |
| `000002` | hrm_attendance_corrections | attendance_id | 補卡申請 |
| `000010` | hrm_leave_types | — | 假別定義 |
| `000011` | hrm_leave_requests | employee_id | 請假申請 |
| `000020` | hrm_overtime_records | employee_id | 加班紀錄 |
| `000030` | hrm_schedules | — | 排班表 |
| `000031` | hrm_schedule_employees | schedule_id, employee_id | 排班明細 |

### 20–29 電商 E-commerce

大模組，拆成多個子模組，每個子模組佔一個 MM 值。

| MM | 子模組 | 內容 |
|----|--------|------|
| `20` | 商品型錄 Catalog | ctl_categories, ctl_products, ctl_options, ctl_option_values, ctl_attributes, ctl_attribute_groups, ctl_filters, ctl_manufacturers |
| `21` | 定價行銷 Pricing | specials, discounts, coupons, gift_vouchers |
| `22` | 客戶 Customers | customer_groups, addresses, wishlist, reward_points |
| `23` | 訂單 Orders | ord_orders, ord_order_products, ord_order_totals, ord_carts, ord_cart_products, ord_returns |
| `24` | 物流稅務 Shipping & Tax | shipping_methods, payment_methods, tax_classes, tax_rates, geo_zones |
| `25` | 評價 SEO Reviews | reviews, seo_urls |
| `26` | 店面佈局 Storefront | banners, layouts, layout_modules |
| `27–29` | 保留 | 多商家 Multi-vendor、數位商品 Digital Products… |

### 30–39 進銷存 Inventory/ERP

| MM | 子模組 | 內容 |
|----|--------|------|
| `30` | 供應商 Suppliers | suppliers, supplier_contacts, supplier_contracts |
| `31` | 採購 Procurement | purchase_orders, purchase_items, receiving, receiving_items |
| `32` | 倉庫 Warehouse | warehouses, locations, stock, stock_transfers, stock_adjustments |
| `33` | 銷售 Sales | sal_orders, sal_order_items, sal_quotations |
| `34` | 財務 Finance | invoices, invoice_items, payments, credit_notes |
| `35–39` | 保留 | 品質管理 QC、生產 Manufacturing… |

### 40–49 內容管理 CMS

| MM | 子模組 | 內容 |
|----|--------|------|
| `40` | 文章 Articles | articles, article_categories, article_translations |
| `41` | 頁面 Pages | pages, page_translations, FAQ |
| `42–49` | 保留 | |

---

## 模組內序號規則（NNNNNN）

每 10 號為一群組，同群組的表彼此相關，群組之間保留空間方便日後插入。

```
000001–000009  主表（核心實體）
000010–000019  第一組關聯表
000020–000029  第二組關聯表
000030–000039  第三組關聯表
...
```

### 範例：多角色使用者 `0001_01_01_NNNNNN`

假設某專案有 `users`（主表）、`members`（前台會員）、`admins`（後台管理員）、`accounts`（帳戶），
應以 `users` 為根，依角色分群展開：

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
| `000100` | members | user_id | 前台會員（補充欄位：暱稱、頭像、等級…） |
| `000101` | member_profiles | member_id | 會員個人資料（地址、電話…） |
| `000102` | member_social_accounts | member_id | 社群登入綁定 |
| `000200` | admins | user_id | 後台管理員（補充欄位：員工編號、職稱…） |
| `000201` | admin_action_logs | admin_id | 管理員操作日誌 |
| `000300` | accounts | user_id | 帳戶（餘額、點數、等級…） |
| `000301` | account_settings | account_id | 帳戶偏好設定 |
| `000302` | account_transactions | account_id | 帳戶交易紀錄 |

對應的 migration 檔名：

```
0001_01_01_000000_create_users_table.php
0001_01_01_000001_create_password_reset_tokens_table.php
0001_01_01_000002_create_sessions_table.php
0001_01_01_000100_create_members_table.php
0001_01_01_000101_create_member_profiles_table.php
0001_01_01_000102_create_member_social_accounts_table.php
0001_01_01_000200_create_admins_table.php
0001_01_01_000201_create_admin_action_logs_table.php
0001_01_01_000300_create_accounts_table.php
0001_01_01_000301_create_account_settings_table.php
0001_01_01_000302_create_account_transactions_table.php
```

**要點：**

- `000000–000099`：Laravel 內建表，不動
- `000100–000199`：members 群組 — 前台會員相關表都集中在這
- `000200–000299`：admins 群組 — 後台管理員相關表都集中在這
- `000300–000399`：accounts 群組 — 帳戶相關表都集中在這
- 每個角色佔一個 100 號群組，各自有空間擴充，互不干擾
- **執行順序自然正確**：`users`(000000) 先建，`members`(000100) 後建，FK 不會出錯

### 範例：電商型錄 `0001_01_20_NNNNNN`

> **原則**：基本資料定義先建（000001–000199），商品主體次之（000200–000299），
> 商品關聯表最後（000300+），確保 FK 依賴順序正確。

| NNNNNN | 表 | 說明 |
|--------|------|------|
| | **基本資料定義（不依賴 products）** | |
| `000001` | ctl_options | 選項 |
| `000002` | ctl_option_translations | 選項多語 |
| `000003` | ctl_option_values | 選項值 |
| `000004` | ctl_option_value_translations | 選項值多語 |
| `000010` | ctl_categories | 商品分類 |
| `000011` | ctl_category_translations | 分類多語 |
| `000020` | ctl_attributes | 屬性 |
| `000021` | ctl_attribute_translations | 屬性多語 |
| `000022` | ctl_attribute_groups | 屬性群組 |
| `000030` | ctl_filters | 篩選器 |
| `000040` | ctl_manufacturers | 製造商 |
| | **商品主體** | |
| `000200` | ctl_products | 商品主表 |
| `000201` | ctl_product_translations | 商品多語 |
| | **商品關聯表（FK→products + 基本資料）** | |
| `000300` | ctl_product_categories | 商品-分類關聯 |
| `000301` | ctl_product_stores | 商品-門市關聯 |
| `000302` | ctl_product_terms | 商品-詞彙關聯 |
| `000310` | ctl_product_options | 商品-選項關聯 |
| `000311` | ctl_product_option_values | 商品選項值 |

### 範例：電商訂單 `0001_01_23_NNNNNN`

| NNNNNN | 表 | 說明 |
|--------|------|------|
| `000001` | ord_orders | 訂單主表 |
| `000002` | ord_order_statuses | 訂單狀態 |
| `000010` | ord_order_products | 訂單商品明細 |
| `000011` | ord_order_options | 訂單商品選項 |
| `000020` | ord_order_totals | 訂單金額小計 |
| `000021` | ord_order_histories | 訂單歷史紀錄 |
| `000030` | ord_carts | 購物車 |
| `000031` | ord_cart_products | 購物車商品 |
| `000040` | ord_returns | 退貨 |
| `000041` | ord_return_reasons | 退貨原因 |

---

## 實際檔名範例

```
# ── Foundation ──
0001_01_01_000000_create_users_table.php
0001_01_01_000001_create_cache_table.php
0001_01_01_000002_create_jobs_table.php
0001_01_03_000001_create_acl_tables.php
0001_01_04_000001_create_settings_table.php
0001_01_04_000010_create_taxonomy_term_tables.php
0001_01_04_000020_create_request_logs_table.php

# ── HR & Admin（hrm_ 前綴）──
0001_01_10_000001_create_hrm_organizations_table.php
0001_01_10_000002_create_hrm_companies_table.php
0001_01_10_000003_create_hrm_departments_table.php
0001_01_10_000004_create_hrm_company_user_table.php
0001_01_10_000010_create_hrm_employees_table.php
0001_01_11_000001_create_hrm_attendance_records_table.php
0001_01_11_000010_create_hrm_leave_types_table.php
0001_01_11_000011_create_hrm_leave_requests_table.php
0001_01_12_000001_create_hrm_assets_table.php
0001_01_12_000010_create_hrm_asset_categories_table.php

# ── E-commerce（基本資料 → 商品 → 關聯表）──
0001_01_20_000001_create_ctl_option_tables.php
0001_01_20_000010_create_ctl_categories_table.php
0001_01_20_000200_create_ctl_product_tables.php
0001_01_20_000300_create_ctl_product_categories_table.php
0001_01_20_000302_create_ctl_product_terms_table.php
0001_01_20_000310_create_ctl_product_option_tables.php
0001_01_23_000001_create_ord_orders_table.php
0001_01_23_000030_create_ord_carts_table.php

# ── Inventory/ERP ──
0001_01_30_000001_create_suppliers_table.php
0001_01_31_000001_create_purchase_orders_table.php
0001_01_32_000001_create_warehouses_table.php
0001_01_34_000001_create_invoices_table.php
```

---

## 設計理念

### 1. 大模組給區段，小模組給單號

電商 (20–29) 和進銷存 (30–39) 各預留 10 個 MM 值，子模組各自獨立，不會擠在一起。
出勤 (`11`) 和資產管理 (`12`) 規模較小，各佔一個 MM 值即可。

### 2. 執行順序天然正確

Migration 按檔名排序執行：

```
Foundation (01-09) → HR (10-19) → E-commerce (20-29) → ERP (30-39) → CMS (40-49)
```

基礎表（users）先建，HR 表（hrm_companies）次之，業務表（orders、purchase_orders）後建，
外鍵依賴順序自然滿足，不需要手動調整。

### 3. 序號間距留空間

模組內每 10 號一組，日後加新表只需在間距中插入，不影響既有檔案。
例如要在 products 和 options 之間加 `product_images`，直接用 `000015`。

### 4. 擴充無痛

MM 值 50–99 完全保留，未來加入 CRM、BI、POS、多租戶等模組都有空間。
每個模組區段也預留了 buffer（如 HR 的 13–19、電商的 27–29）。

### 5. 表名前綴讓歸屬一目了然

業務模組的資料表加上模組前綴（如 `hrm_`），好處：

- **資料庫瀏覽時自然分群**：`hrm_` 開頭的表排在一起，不需要對照文件
- **避免命名衝突**：`companies` 太通用，電商也可能有 `companies`；`hrm_companies` 不會混淆
- **程式碼語意清晰**：`HrmCompany::class` / `$table = 'hrm_companies'` 一看就知道屬於 HR 模組

| 區段 | 前綴 | 範例 |
|------|------|------|
| 01–09 Foundation | 無（Laravel 慣例） | users, cache, jobs, settings |
| 10–19 HR | `hrm_` | hrm_companies, hrm_employees, hrm_attendance_records |
| 20 Catalog | `ctl_` | ctl_products, ctl_categories, ctl_options |
| 23 Orders | `ord_` | ord_orders, ord_carts, ord_returns |
| 21–29 E-commerce 其他 | 各子模組自訂 | 待定 |
| 33 Sales (ERP) | `sal_` | sal_orders, sal_quotations |
| 30–39 Inventory 其他 | 各子模組自訂（如 `inv_`） | 待定 |

> **Foundation 不加前綴**：users、cache、jobs 等 Laravel 內建表維持原名，
> 第三方套件的表（如 spatie permission）也維持原名，避免與套件衝突。

---

## 現有檔案對照與遷移建議

以下是目前 `ecommerce` 分支的 migration 檔案，與建議的新編號對照：

| 現有檔名 | 建議新編號 | 目標模組 |
|----------|-----------|---------|
| `0001_01_01_000000_create_users_table` | `0001_01_01_000000` | 01 Laravel Core（不變） |
| `0001_01_01_000001_create_cache_table` | `0001_01_01_000001` | 01 Laravel Core（不變） |
| `0001_01_01_000002_create_jobs_table` | `0001_01_01_000002` | 01 Laravel Core（不變） |
| `0001_01_01_000010_create_organizations_table` | `0001_01_10_000001` → `hrm_organizations` | 10 HRM 核心 |
| `0001_01_01_000011_create_companies_table` | `0001_01_10_000002` → `hrm_companies` | 10 HRM 核心 |
| `0001_01_01_000012_create_departments_table` | `0001_01_10_000003` → `hrm_departments` | 10 HRM 核心 |
| `0001_01_01_000013_create_company_user_table` | `0001_01_10_000004` → `hrm_company_user` | 10 HRM 核心 |
| `0001_01_01_000020_create_hrm_employees_table` | `0001_01_10_000010` → `hrm_employees` | 10 HRM 核心 |
| `0001_01_01_000050_create_acl_tables` | `0001_01_03_000001` | 03 ACL 權限 |
| `0001_01_01_000060_create_request_logs_table` | `0001_01_04_000020` | 04 系統工具 |
| `2026_02_02_*_create_permission_tables` | 刪除或合併至 `0001_01_03` | 03 ACL 權限 |
| `2026_02_02_*_acl_role_translations` | 刪除或合併至 `0001_01_03` | 03 ACL 權限 |
| `2026_02_02_*_acl_permission_translations` | 刪除或合併至 `0001_01_03` | 03 ACL 權限 |
| `2026_02_05_*_create_settings_table` | `0001_01_04_000001` | 04 系統工具 |
| `2026_02_06_*_create_taxonomy_term_tables` | `0001_01_04_000010` | 04 系統工具 |
| `2026_02_10_*_create_option_tables` | `0001_01_20_000001` → `ctl_options` | 20 商品型錄（基本資料） |
| `2026_02_10_*_create_product_tables` | `0001_01_20_000200` → `ctl_products` | 20 商品型錄（商品主體） |

> **注意**：重新命名 migration 檔案後，若資料庫已執行過舊 migration，
> 需同步更新 `migrations` 資料表中的檔名紀錄，或在開發環境中 `migrate:fresh` 重建。
