# Settings 多層擴充架構

> 本文為 `0102_參數設定.md` 的補充，聚焦在「如何依專案需求擴充 settings 的作用域」。

---

## 一、名詞釐清：兩種「Store」的歧義

`0102_參數設定.md` 末節「多門市擴充」使用了 `stores` + `store_settings` 的命名，
其中的 `store` 沿用 **OpenCart 原型的概念**：

| 系統 | store 的意思 |
|------|------------|
| OpenCart | 不同的電商網站（不同網址、不同商品目錄） |
| branchMain 0102 | 同上（沿用 OpenCart 概念） |
| **本系統（如 branchHuabing）** | **實體門市據點**（餐廳分店） |

這兩個「store」語意不同。引用 0102 的 `stores + store_settings` 擴充方案時，
**需先確認自己系統中 store 指的是哪一層**，再決定命名。

---

## 二、Settings 的三個潛在層級

| 層級 | 概念 | 對應 branchHuabing |
|------|------|------------------|
| **全域** | 適用整個系統，無主體區分 | `settings` 表（現有） |
| **品牌 / 站台** | 不同品牌有不同行為或組態 | `brand_settings`（待擴充） |
| **門市 / 據點** | 個別門市有特殊設定 | `store_settings`（未來視需求） |

**哪層是 OpenCart 的 `store_id`？**
→ 品牌層。OpenCart 的一個 store = 一個電商網站 = 本系統的一個 brand。

---

## 三、魚與熊掌能否兼得？

**問題：** 若 branchMain 加入 `brand_id`（或 `store_id`），基礎框架就綁定了多品牌概念，
不再通用。但若不加，多品牌專案又得自行擴充。這是否魚與熊掌不可兼得？

**答：可以兼得，解法是「擴充而非修改」。**

```
branchMain（基底）              branchHuabing（專案）
┌──────────────────────┐       ┌──────────────────────┐
│  settings            │       │  settings            │  ← 繼承，不動
│  （全域，code 唯一）  │       │  （全域設定）         │
└──────────────────────┘       ├──────────────────────┤
                               │  brand_settings      │  ← 專案自行新增
                               │  （品牌級覆寫）       │
                               └──────────────────────┘
```

- branchMain 的 `settings` 表永遠是全域、無主體的，**不需要改**
- 各專案視需求**新增一張覆寫表**（命名自訂）
- 兩者透過「查找順序」整合：品牌設定 → 全域設定 → 程式預設值

branchMain 的通用性完全保留；多品牌需求在專案層處理。

---

## 四、各類專案的擴充選擇

### 4.1 純後台 / 單一主體（不需擴充）

```
settings（全域）
```

適用：HRM、內部工具、單一品牌且無分店差異的系統。

---

### 4.2 多品牌 / 多站台（如 branchHuabing）

新增 `brand_settings` 表，對應 OpenCart 的 `store_settings`：

```sql
brand_settings
  id
  brand_id   FK → brands.id
  group
  code
  value
  type
  note
  UNIQUE(brand_id, code)
```

查找順序：

```
setting(code, brand_id)
│
├─ 1. brand_settings WHERE brand_id = ? AND code = ?   → 品牌覆寫
└─ 2. settings WHERE code = ?                          → 全域預設
```

---

### 4.3 多品牌 + 多門市（更細粒度，未來視需求）

在 4.2 的基礎上再加一層：

```
setting(code, brand_id, store_id)
│
├─ 1. store_settings WHERE store_id = ? AND code = ?   → 門市覆寫
├─ 2. brand_settings WHERE brand_id = ? AND code = ?   → 品牌覆寫
└─ 3. settings WHERE code = ?                          → 全域預設
```

**目前不建議直接跳到這一層**，原因：
- 門市特有屬性（電話、地址、狀態）直接放 `stores` 的欄位就夠
- 真正需要「門市可動態調整的行為參數」的場景很罕見
- 三層 cascade 增加查詢複雜度，且多數設定只會用到前兩層

---

## 五、命名建議

| 層級 | branchMain 0102 的命名 | 建議依語意自訂 |
|------|----------------------|--------------|
| 全域 | `settings` | `settings`（不動） |
| 品牌/站台 | `stores` + `store_settings` | `brands` + `brand_settings` |
| 實體門市 | （未涵蓋） | `stores` + `store_settings` |

branchMain 的 0102 使用 OpenCart 的 `store` 詞彙，是框架層的泛稱。
各專案依自己的業務語言重新命名，**不需要硬跟 OpenCart**。

---

## 六、branchMain 的通用邊界

branchMain 負責提供：
- `settings` 表（全域設定，所有專案通用）
- 擴充模式的文件與說明（本文）

**branchMain 不負責：**
- 品牌層（`brand_settings`）→ 各專案自行建立
- 門市層（`store_settings`）→ 各專案自行建立（若需要）

這是有意為之的邊界。讓基底框架保持最小，複雜度留在需要的專案裡。
