# CLAUDE.example.md — ocadmin 衍生專案 CLAUDE.md 起手範本

> **這是什麼**：ocadmin（projMain）衍生專案的 `CLAUDE.md` 範本。新衍生專案開站時，把本檔**複製成該專案根目錄的 `CLAUDE.md`**（各專案 CLAUDE.md 通常 gitignore、為本機私檔），再把所有 `<填寫：…>` 佔位補成該專案實情、刪掉用不到的段。
>
> **本檔（CLAUDE.example.md）入版控**（像 `.env.example`），是共通慣例的單一事實源；機制/慣例有改良時改本檔，再讓各專案同步。專案專屬的值（站名、DB、定價、業務基調）不寫進本範本，只在各自 CLAUDE.md 補。

---

## 專案簡介

<填寫：一兩句話講這個專案是什麼、賣給誰、核心業務層是什麼。例如「projXxx ＝○○服務網站，建構於 ocadmin 後台框架之上；後台沿用 ocadmin，對外消費端服務（…）為本專案新增的業務層。」>

<填寫（選填）：若有「為什麼做／定價／風險／節奏」的決策事實源檔，列路徑並要求動產品決策前先讀。>

## 溝通語言：一律繁體中文

**一律用繁體中文**回覆與寫說明（含對話、commit message、文件、程式註解）。不要漂成日文或簡體。

## 與 projMain（ocadmin）的關係

- **本機資料夾**：`<填寫：proj資料夾名>\`；**ocadmin 框架識別符**仍為 `ocadmin`（namespace / portal / config key / env prefix）。兩者不必同名。
- 共用機制（Portal 多入口、ACL、選單、helper、設定）**沿用 ocadmin**；跨檔引用 ocadmin 共用文件時加 `ocadmin/` prefix。
- ocadmin 框架層的權威文件在 projMain；**編輯 projMain 範圍的檔案前，先讀 projMain 的 `CLAUDE.md`**（`D:\Codes\PHP\Laravel\LaravelOcadmin\projMain\CLAUDE.md`）。
- **同步方向（改良往上流，裁減只在下游）**：projMain 是上游基礎範本。
  - ✅ 個別專案長出好寫法 → 應**往上同步回 projMain**，讓範本受益。
  - ✅ 個別專案用不到的功能 → **只在本專案刪除**，**不要連 projMain 一起刪**（範本該保留的範例留給其他衍生專案）。
  - 刪除是不對稱的：下游裁減不回推；只有「改良」才往上同步。
- ⚠️ **本專案是獨立 repo**：<填寫：remote 現況——是否已移除 projMain 的 remotes、是否已建專屬 GitHub repo 並 `git remote add`；push 前的注意事項>。

## 專案清單登錄（必須）

本專案的本機路徑與用途**必須登錄**在 projMain 的 [`docs/private/ocadmin專案清單.md`](D:\Codes\PHP\Laravel\LaravelOcadmin\projMain\docs\private\ocadmin專案清單.md)（ocadmin 系列所有衍生專案的本機路徑單一事實源）。**請引用此檔**，不要在各專案 CLAUDE.md 重複列舉跨專案路徑。

- 若尚未登錄：到該清單新增一筆（資料夾路徑、一句用途、repo / remote 現況）。
- 若已登錄：在此**註明「已登錄」**（例如 `> ✅ 已登錄於 ocadmin專案清單.md`），避免重複新增。

> 好處：① 清單不會漏掉任何專案；② 跨專案處理時引用清單路徑即可定位所有姐妹專案，比各自硬編路徑方便。

> ✅ 已登錄狀態：<填寫：已登錄 / 尚未登錄；若已登錄註明於清單哪個分段（通用專案 / 商業專案）>

## 指令（多版本 PHP，透過根目錄 bat 包裝）

```bash
# 執行前先 cd <填寫：專案絕對路徑>
./php.bat artisan migrate
./php.bat artisan db:seed
./composer.bat install
npm install && npm run dev      # Vite 前端
```

PHP 路徑：`D:\Servers\PHP\php85\php.exe`（PHP 8.5）。

## 技術棧

Laravel 13、PHP 8.5、Blade + jQuery + Bootstrap 5、Sanctum 認證、Spatie Permission、Vite。（同 ocadmin）

## 本機站台

<填寫：本機站台網址與架法。例如「已用 Apache 建好 `https://xxx.test/`（HTTPS）；前台在 …、後台在 /zh-hant/admin」。規範：驗證一律打此網址、勿自己 `artisan serve` 起背景 server；curl 自簽憑證用 `curl -k`。>

## 資料庫

| 連線 | 資料庫 | 用途 |
|---|---|---|
| `mysql`（預設） | `<填寫>` | 主資料庫（業務 + 後台） |
| `sysdata` | `<填寫，通常 ocadmin_sysdata>` | 系統資料（request_logs 等） |

<填寫：MySQL 帳密與後台登入帳密（本機開發值）。>

## 資料庫操作（不走 migration 累積，改 transition 機制）

**本系統不把 Laravel migration 當「版本累積記錄」用。** migration 主檔（`create_*_table.php`）＝**最新 schema 規格的 single source of truth**；schema 異動實際靠下表工具套用，不靠堆疊 `add_xxx` 檔。權威說明見 projMain `docs/common/00007_資料庫Transition變更機制.md`。

| 階段 | 工作流 | 工具 |
|---|---|---|
| **上線前** | 改主表 migration → `migrate:fresh --seed` 重建（含 seeder；若本專案有 legacy ETL 才包成 `db:rebuild`） | `migrate:fresh` |
| **上線後** | 改主表 migration（保持規格同步）＋**同步**寫 `database/transitions/YYYYMMDD_NNN_*.php` 套到實際 DB | `db:transition` |

**鐵律：**
- ❌ **任何階段都不開 `add_xxx_to_yyy` migration**——主表 migration 永遠是 single source of truth（要加欄/改欄/加索引/加新表，一律改主檔）。
- ❌ **上線後絕對不可 `migrate:fresh` / `db:rebuild`**——會清掉正式資料；上線後一切異動走 transition。
- transition 與主表 migration 的修改**成對發生**：transition 給已有資料的環境套用、主表 migration 給未來新環境建表，兩邊不能漏。撰寫原則（idempotent、DDL 設 `transaction:false`）見 00007、範本 `database/transitions/example.php`。

> **`db:rebuild` 只在「需要移植舊資料庫」時才需要**，非框架內建。它＝「`migrate:fresh --seed` ＋ 把舊系統資料 ETL 進新表（＋可能的帳號中心匯入）」的包裝指令；**移植舊庫是它存在的核心理由**——若沒這需求，純 `migrate:fresh --seed` 就夠，不必另包指令。**全新專案沒有舊資料庫要搬，就不需要、也不該有 `db:rebuild`**，上線前重建純跑 `migrate:fresh --seed`。哪天真要從舊庫搬資料，才補一支 `db:rebuild`（可比照有 legacy ETL 的專案寫法）。

## 臨時 PHP 腳本

需執行多行 PHP（如查 DB）時，建暫存檔執行後刪除，避免 `php -r` 搭 bash 被 shell 吃掉 `$` 變數：

```bash
# Write _tmp_query.php → ./php.bat _tmp_query.php → rm _tmp_query.php
```

## Git 提交慣例

- 何時 commit／push 由 user 指示；若本專案尚未設 remote，可 local commit、不可 push。
- 新一輪改動若與**上一次 commit 主題相近、且變動檔案少**，優先 `git commit --amend` 合併，避免把連貫工作切成一堆無意義小 commit。
  - ✅ amend：同機制的微調／漏改／typo／對應文件補充／延續上次 commit 範圍的後續修改。
  - ❌ 不要 amend：已 push 的 commit、跨主題修改、會讓 commit message 變不準、想保留決策時序的改動。
- 不確定就先問 user。詳見 projMain `docs/common/00010_開發協作流程.md`。

## 開發記錄的查核來源（進度筆記瘦身原則）

**已完成的事不寫進度筆記。** 進度筆記只放「還沒做完的」。查核分兩條路，各有各的來源：

| 要查什麼 | 去哪裡查 | 追蹤 |
|---|---|---|
| **已完成** | `git log`——**改了什麼**（精簡，一眼掃過歷史用） | ✅ |
| | `docs/done/*`——該工作的**完整脈絡**：做了什麼、為什麼這樣做、踩過什麼坑 | ✅ |
| | `docs/md/*`——較正式的**決議與設計**：架構、取捨、被否決的方案 | ✅ |
| **待辦** | `docs/todo/*`（工作便條）+ `docs/todo/00000000_進度筆記.md`（跨 session 接手用） | ❌ 會變動 |

理由：完成事項寫進筆記會讓它無限膨脹，且與 git log / docs 重複——同一件事三個地方寫，還會不同步。

**因此**：
- **脈絡寫在該工作的 todo 文件裡**（邊做邊累積），完成後整份搬到 `docs/done/`——它本身就是那份紀錄，不必另外謄一次
- **commit 訊息保持精簡**：講清楚「改了什麼」即可，脈絡不重複貼進去；需要時在訊息末尾指向對應的 `docs/done/` 或 `docs/md` 文件
- 通用、耐久的設計決議 → 提煉進 `docs/md/`（那是給未來的人看的，不隨單次工作生滅）
- 進度筆記裡的項目一旦完成 → **直接刪掉**，不必標 ✅ 也不必等時效；大型工作在筆記只留一行指向 done 檔

`.gitignore` 對應（`docs/` 只放行入版控的那幾個）：

```gitignore
/docs/*
!/docs/md/
!/docs/done/
```

### ⚠️ 什麼可以放進 `docs/done/`（搬檔＝送進版控）

**`docs/done/` 進 git。** 搬檔前先確認兩件事：

**① 主題必須是「本專案自己的業務工作」。** 以下**一律不可**放進商業專案的 git，留在 gitignored 的 `docs/todo/`（或搬去 projMain 自己的 repo）：

- **跨專案 / 框架範本同步**（projMain、姐妹專案的 sync sprint、範本層改版對齊）——那是本機開發鏈的事，與本專案的業務無關
- 本機工具鏈、開發環境、git 部署腳本的折騰過程
- 任何引用其他專案 commit hash、內部路徑、內部專案代號的內容

**② 內容不得含敏感資訊。** `todo/` 不入 git，寫的時候不會顧忌——本機絕對路徑、DB 名稱與帳密、客戶可識別資訊都可能在裡面，搬之前先移除。

> 判斷法：**這份文件如果被本專案以外的人看到，會不會出問題或造成困惑？** 會 → 不要搬，留在 `todo/`。

> **`docs/done/` 到底要不要入 git，各專案自行決定**：
> - **入 git**（如 projSunline）：只有單一私有 remote、沒有對外公開的疑慮時採用。好處是脈絡跟著版控走、換機器不會掉。
> - **不入 git**（如 projMain）：remote 會對外公開時採用，檔案純留本機、自行備份。
>
> ⚠️ **不要用「入 git 但在 deploy 腳本排除」的折衷做法**——那會讓「這東西到底會不會公開」同時取決於 `.gitignore`、分支 tree、腳本清單三處，任一處不一致就出錯。projMain 曾用這種雙 remote mirror 模型，已於 2026-08-04 廢除。**判準只留一個：有沒有被 `.gitignore`**。
>
> <填寫：本專案的決定>

## 「進度」/「進度筆記」的指代

當 user 提到「進度」、「進度筆記」、「看進度」等詞，預設指 [`docs/todo/00000000_進度筆記.md`](docs/todo/00000000_進度筆記.md)（gitignore 的 scratch handoff 檔，跨 session 接手用）。

- **「看進度」/「繼續進度筆記」**：先 Read 該檔，掌握進行中 / 未完 / user 操作待辦 / 下一首要 / 已對齊契約後再行動。要知道「先前做過什麼」查 `git log`、`docs/done` 與 `docs/md`，不要期待筆記裡有。
- **「繼續進度」/「推進度」**：意指**繼續筆記裡「下一首要」標示的工作項**，不需多問直接動工。
- session 結束前依該檔開頭「維護慣例」段更新。

### user 說「更新進度」＝ 動手更新 + 準備收尾 session

預設意圖是：更新進度筆記、對話準備關閉重啟、收尾當前 session。直接動工更新（不必再問「要更新嗎」），更新內容應假設「下一句就是 /clear」。

**只寫這四類**：進行中未完的事、user 的操作待辦、下一首要、已對齊/待決契約。**本 session 已完成並 commit 的事不要寫**（見上節）——完成的項目若原本在筆記裡，直接刪掉那幾行。

- ✅ 視為指令：「更新進度」、「更新一下進度」、「收尾，更新進度」
- ❓ 需先確認：「進度筆記是什麼？」、「我剛自己更新了，你看一下」——這類是詢問/回報，不是動工指令

### user 說「清理進度」＝ 修剪進度筆記、防止無限變長

直接動工修剪（不必再問；git log 才是 history source of truth，刪掉的完成事項都救得回）：

1. **刪除所有已完成項**，不論新舊、不論有沒有標日期——已完成的查 `git log` / `docs/md` / `docs/done/`。
2. **豁免**：「已對齊／待決契約」段（耐久契約，只在被新決策取代時清）；未完成項（⬜／🔄／暫緩，不論擱置多久都留）；檔首「維護慣例」「reload 指引」段。
3. 若刪掉的是大型工作，確認 `docs/done/` 或 `docs/md/` 有對應歸檔；沒有就先補一份再刪。

- ✅ 視為指令：「清理進度」、「清理一下進度」
- ❓ 需先確認：「進度可以清理嗎？」「哪些該清？」——這類是詢問，先說明規則不動手

## 跨專案編輯前先讀對方 CLAUDE.md

在本專案以外（projMain、姐妹專案等）讀取或編輯檔案時，**先 Read 該專案根目錄的 CLAUDE.md**（若存在）。Claude Code 啟動時只把 working directory 的 CLAUDE.md 注入 context，跨專案不會自動載入，容易忽略對方的命名/規範差異。每個專案 git 也是獨立 repo，commit 時別搞混。

## 文件參考

- **共用基礎文件 — `projMain/docs/common/`**：框架慣例、程式規範、Portal/Module 架構、Service 分層、Model Scope、transition 機制等共用文件都在此（如 `00003_Ocadmin程式規範.md`、`00007_資料庫Transition變更機制.md`、`00010_開發協作流程.md`）。實作新功能前先看 projMain 共用文件了解框架慣例，再於本專案實作。projMain 僅供參考，**不是本專案的執行依賴**。
- **本專案專用文件 — `<填寫>/docs/`**：編號規則（衍生專案寫 2x 段）＋目錄結構詳見本專案 `docs/md/20000_文件說明.md`。

## 文件編號引用慣例

跨 branch 引用編號時加 prefix 避免歧義：

| 寫法 | 適用情境 |
|---|---|
| 裸寫 `23100` | 本專案內互引、上下文已明確 |
| `ocadmin/10007` | 引用 ocadmin 共用機制（1x、0x、9x 段） |
| `<填寫:proj代號>/23100` | 在 projMain 或其他專案文件中引用本專案文件 |

第一位數字：`0`/`1`/`9` ＝ ocadmin；`2` ＝ 所有衍生專案共用命名空間。第二位（業務段）跨專案語意一致。
