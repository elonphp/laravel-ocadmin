# Settings 多層擴充架構

> 本文為 `10021_參數設定.md` 的補充，聚焦在「當不同對象需要不同設定時，該擴充什麼」。
>
> OpenCart 4 原始設計參考：[`docs/reference/opencart4x.sql`](../reference/opencart4x.sql) 的 `oc_setting`（含 `store_id` 欄位）、`oc_store`（電商網站主檔）。

---

## 結論先講

**`sys_settings` 維持全域**，不加 `tenant_id` / `brand_id` / `store_id` 欄位、不建 cascade 覆寫表。

個別主體（品牌、門市等）的差異需求，**走業務 entity 自身的欄位、JSON 屬性或獨立關聯表**——詳細四種解法見 §四。

> 真的需要 multi-layer cascade（極少數情境）的擴充模式整理在範本範圍外的研究筆記：
> [`docs/research/參數多層架構擴充.md`](../research/參數多層架構擴充.md)。

---

## 一、定位

`10021_參數設定.md` 的 `sys_settings` 是**全域、無主體**的鍵值表。多主體（多品牌、多門市）的設定需求，本文討論該如何延伸。

關鍵問題：要不要在 `sys_settings` 結構上加 `tenant_id` / `brand_id` / `store_id`，或建另一張覆寫表？

本範本答案：**都不要**。理由見 §二。

---

## 二、「該因 X 而異」不等於「系統設定為 X 覆寫」

多數時候**根本不需要走 cascade 覆寫**，原因：

「該因 X 而異的東西」**不等於**「系統設定為 X 覆寫」。

| 該因 X 而異的東西 | 本質 | 該放哪 |
|---|---|---|
| Entity 的固有屬性（門市電話、地址、營業時間、產能、排休） | Entity 的特徵 | Entity 自身欄位 / 關聯表 |
| 真的是「系統設定」但特定 Entity 要不一樣（罕見） | 系統設定的 per-entity override | 才考慮 cascade（見 research） |

99% 的情境屬於前者。先排除前者，剩下的才考慮 cascade。

---

## 三、擴充而非修改：ocadmin 邊界

```
ocadmin（基底）              衍生專案
┌──────────────────────┐       ┌──────────────────────┐
│  sys_settings        │       │  sys_settings        │  ← 繼承，不動
│  （全域，code 唯一）  │       │  （全域設定）         │
└──────────────────────┘       ├──────────────────────┤
                               │  brands.* 欄位        │  ← 各主體差異
                               │  stores.* 欄位        │  ← 走業務表
                               │  brands.settings JSON │
                               └──────────────────────┘
```

- ocadmin 的 `sys_settings` 表永遠是全域、無主體的，**不需要改**
- 衍生專案視需求**擴充對應業務 entity**（brands / stores 等），而不是擴充 sys_settings

ocadmin 的通用性完全保留；多主體需求由業務 entity 自然承擔。

---

## 四、四種解法（依適用情境排序）

### 4.1 選項 A：`{entity_table}.{specific_field}` — 獨立固定欄位

```php
Schema::table('org_stores', function (Blueprint $table) {
    $table->string('opening_time', 5)->nullable();   // "07:00"
    $table->string('closing_time', 5)->nullable();
});
```

**優**：強型別、可索引、可外鍵、SQL 直查清楚、IDE 自動完成
**缺**：每多一種需求就加欄位，種類太多會欄位爆炸

**適用**：少數明確、穩定的單值屬性（門市的 `phone` / `address` / `code` 已是此風格）

### 4.2 選項 B：`{entity_table}.settings` — 單欄 JSON

```php
Schema::table('org_stores', function (Blueprint $table) {
    $table->json('settings')->nullable()->comment('門市專屬設定，無 schema 約束');
});
```

```jsonc
{
  "slot_capacities": { "07:00": 250, "08:00": 250 },
  "opening_hours":   { "mon": ["07:00", "21:00"], "sat": ["08:00", "22:00"] },
  "tax_rate_override": 0.08
}
```

讀取：

```php
function entity_setting(Model $entity, string $path, mixed $default = null): mixed {
    return data_get($entity->settings, $path, $default);
}

$cap = entity_setting($store, 'slot_capacities.07:00', 200);
```

**優**：擴充無 migration、欄位數固定、適合彈性 schema、單欄 trivial
**缺**：無 schema 約束、查詢需 JSON_EXTRACT、無索引（除 generated column）、IDE 沒提示、容易塞太多東西

**適用**：每 entity 一份、結構化、單一物件、不需 row 級查詢的設定（如門市營業時間整週是一個物件）

### 4.3 選項 C：獨立 1:N 關聯表

```php
Schema::create('org_store_capacity_overrides', function (Blueprint $table) {
    $table->id();
    $table->foreignId('store_id')->constrained('org_stores')->cascadeOnDelete();
    $table->date('date')->nullable()->comment('null=常態覆寫；有值=特定日期覆寫');
    $table->time('slot_start_time');
    $table->integer('capacity');
    $table->unique(['store_id', 'date', 'slot_start_time']);
});
```

**優**：強 schema、可有大量 row、可索引查詢、可關聯 join
**缺**：要建表，過度為單一 use case 投資

**適用**：屬性是「多筆、要 ad-hoc query」的（如「某天某店某時段產能 = ？」）

### 4.4 選項 D：`{level}_settings` cascade — **本範本不推薦**

需要把同一筆 `code` 在不同層級（全域 / 品牌 / 門市）覆寫時的擴充模式。本範本不採用，內容整理在 [`docs/research/參數多層架構擴充.md`](../research/參數多層架構擴充.md)。

---

## 五、決策流程

```
新需求出現「這個值要因 X 而異」
  │
  ├─ 是 X 的固有屬性嗎？（電話、地址、營業時間、產能…）
  │   ├─ 是 → 屬性是單值且穩定？        → 選 A：{entity}.{field}
  │   │       屬性是複雜物件、單筆？     → 選 B：{entity}.settings (JSON)
  │   │       屬性是多筆 row、要查詢？   → 選 C：獨立關聯表
  │   │
  │   └─ 不是（真的是「系統設定但這個 X 要不同」）
  │       └─ 全系統只有 1-2 個這種需求？ → 用 A/B 的 helper 包裝臨時處理
  │       └─ 開始累積 5+ 個？           → 才考慮選 D（見 research）
```

---

## 六、不要的反模式

❌ **誤用 1：把 entity 的固有屬性塞進 sys_settings 或 cascade 表**

```php
// 錯：門市電話本來就該在 stores.phone
StoreSetting::create(['store_id' => 1, 'code' => 'config_telephone', 'value' => '02-1234-5678']);
```

正解：放 `org_stores.phone`（已是現況），不是 `store_settings`。

❌ **誤用 2：因為「未來可能要覆寫」就先加 cascade 表**

YAGNI。cascade 表加上後**會誘導**把所有「跟 X 有關的東西」都塞進來，違反 §二 的判準。等真有 5+ 個需要覆寫的系統設定再加。

---

## 七、ocadmin 的通用邊界

ocadmin 負責提供：
- `sys_settings` 表（全域設定，所有專案通用）
- 擴充模式的文件與說明（本文）

**ocadmin 不負責：**
- 品牌層 / 門市層的差異承載 → 由各專案的 brands / stores 等業務表自行擴充
- Multi-layer cascade（如 brand_settings / store_settings）→ 本範本不推薦；研究內容見 research

這是有意為之的邊界。讓基底框架保持最小，複雜度留在需要的專案裡。
