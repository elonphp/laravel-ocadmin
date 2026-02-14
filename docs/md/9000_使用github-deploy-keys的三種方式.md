# 使用 GitHub Deploy Keys 的三種方式

本文件說明在 Windows 環境下使用 GitHub Deploy Keys 的三種方式，涵蓋 OpenSSH 與 PuTTY 兩套 SSH 系統。

## 目錄

- [前置準備](#前置準備)
- [方式一：OpenSSH + SSH Config](#方式一openssh--ssh-config)
- [方式二：TortoiseGit + PuTTY Key File](#方式二tortoisegit--putty-key-file)
- [方式三：Pageant 金鑰代理](#方式三pageant-金鑰代理)
- [比較與建議](#比較與建議)
- [常見問題](#常見問題)

---

## 前置準備

### 1. 產生 SSH 金鑰對

#### OpenSSH 格式（方式一使用）

```bash
# 在金鑰目錄產生
cd D:/path/to/ssh-keys/your-key-name
ssh-keygen -t rsa -b 4096 -f private-openssh -C "your-comment"
```

產生檔案：
- `private-openssh` - 私鑰
- `private-openssh.pub` - 公鑰

#### PuTTY 格式（方式二、三使用）

使用 PuTTYgen：

1. 開啟 PuTTYgen
2. **Generate** → 移動滑鼠產生隨機性
3. 或 **Conversions → Import key** 匯入現有的 OpenSSH 私鑰
4. **Save private key** → 存為 `private.ppk`
5. 複製公鑰到 `public.txt`

### 2. 在 GitHub 新增 Deploy Key

1. 前往 Repository → **Settings** → **Deploy keys**
2. 點擊 **Add deploy key**
3. **Title**: 輸入識別名稱（例如：`Production Server`）
4. **Key**: 貼上公鑰內容
   - OpenSSH: `private-openssh.pub` 的內容
   - PuTTY: PuTTYgen 視窗顯示的公鑰
5. ✅ 勾選 **Allow write access**（如需推送權限）
6. 點擊 **Add key**

### 3. 驗證金鑰指紋

確保三種格式的金鑰指紋一致：

```bash
# OpenSSH 私鑰指紋
ssh-keygen -lf D:/path/to/private-openssh

# 輸出範例
2048 SHA256:mMQ33naqqaJqb2cNYncW4tfYk63AISBIierC7lCLfEk no comment (RSA)
```

PuTTYgen 視窗顯示的指紋應該相同：
```
ssh-rsa 2048 SHA256:mMQ33naqqaJqb2cNYncW4tfYk63AISBIierC7lCLfEk
```

GitHub Deploy Key 頁面顯示的指紋也應相同：
```
SHA256:mMQ33naqqaJqb2cNYncW4tfYk63AISBIierC7lCLfEk
```

---

## 方式一：OpenSSH + SSH Config

### 概述

使用標準的 OpenSSH 工具與 SSH config 檔案管理金鑰，這是最標準、跨平台的方式。

**核心原理：**
- 設定 `~/.ssh/config` 檔案定義 SSH 連線規則
- Git 使用 `ssh.exe` (OpenSSH) 作為 SSH 客戶端
- OpenSSH **自動讀取** `~/.ssh/config` 並套用對應的 Host 設定
- 所有使用 OpenSSH 的工具（Git Bash、TortoiseGit、VS Code）都共用此設定

### 適用情境

✅ 想要統一所有 Git 工具的金鑰管理
✅ 需要管理多個 GitHub 帳號或專案
✅ 同時使用命令列、VS Code、TortoiseGit 等工具
✅ 偏好 Unix/Linux 標準工具

### 設定步驟

#### 1. 設定 SSH Config

編輯或建立 `C:\Users\<使用者>\.ssh\config`：

```ssh-config
# Your Project Repository
Host github-your-repository
    HostName github.com
    User git
    IdentityFile D:/path/to/ssh-keys/project-name/private-openssh
    IdentitiesOnly yes
```

**設定說明：**
- `Host`: SSH 連線別名（可自訂）
- `HostName`: 實際連線的主機（固定 `github.com`）
- `User`: SSH 使用者名稱（GitHub 固定為 `git`）
- `IdentityFile`: 私鑰檔案的絕對路徑（使用正斜線 `/`）
- `IdentitiesOnly yes`: 只使用指定的金鑰，不嘗試其他金鑰

#### 2. 設定 Git Remote

```bash
# 新增遠端（使用 Host 別名）
git remote add origin git@github-your-repository:username/your-repository.git

# 或修改現有遠端
git remote set-url origin git@github-your-repository:username/your-repository.git
```

**重點：**
- URL 使用 `git@<Host別名>:<使用者>/<倉庫>.git`
- **不是** `git@github.com`，而是 SSH config 中定義的 `Host` 名稱

#### 3. 設定 TortoiseGit 使用 OpenSSH（重要）

**TortoiseGit 預設使用 PuTTY，需手動切換為 OpenSSH 才能套用 SSH Config 設定。**

設定步驟：

1. **TortoiseGit** → **Settings** → **Network**
2. **SSH client** 設為**空白**或設為：
   ```
   C:\Program Files\Git\usr\bin\ssh.exe
   ```
3. 點擊**確定**

**關鍵說明：**
- ✅ 設定為 `ssh.exe`（或空白）後，TortoiseGit 會使用 **OpenSSH**
- ✅ OpenSSH **自動讀取並套用** `~/.ssh/config` 中的所有設定
- ✅ 包含 `Host` 別名、`IdentityFile`（金鑰路徑）、`HostName` 等所有參數
- ❌ TortoiseGit 的 **"Putty Key" 設定會被忽略**（僅適用於 PuTTY）
- ❌ **無法透過 TortoiseGit 圖形界面指定 OpenSSH 金鑰**，必須在 SSH config 設定

**這樣設定的好處：**
- 命令列 `git.exe`、TortoiseGit、VS Code 都使用相同的 SSH config
- 只需維護一份設定檔（`~/.ssh/config`）
- 所有工具的行為完全一致

#### 4. 測試連線

```bash
# 測試 SSH 連線
ssh -T git@github-your-repository

# 成功輸出
Hi username/your-repository! You've successfully authenticated, but GitHub does not provide shell access.
```

```bash
# 測試 Git 操作
git fetch origin
git pull origin main
```

### 管理多個金鑰範例

```ssh-config
# 公司專案
Host github-company
    HostName github.com
    User git
    IdentityFile C:/Users/admin/.ssh/company_deploy_key
    IdentitiesOnly yes

# 個人專案
Host github-personal
    HostName github.com
    User git
    IdentityFile C:/Users/admin/.ssh/personal_key
    IdentitiesOnly yes

# 客戶專案 A
Host github-client-a
    HostName github.com
    User git
    IdentityFile D:/Projects/ClientA/deploy_key
    IdentitiesOnly yes
```

使用時：
```bash
git remote add origin git@github-company:company-org/project.git
git remote add origin git@github-personal:myusername/my-repo.git
git remote add origin git@github-client-a:client-a-org/repo.git
```

### 優點

✅ 標準、跨平台、所有工具都支援
✅ 集中管理所有 SSH 連線設定
✅ 可管理數十個不同的金鑰和主機
✅ 命令列、TortoiseGit、VS Code 都能用
✅ 設定一次，所有工具共用

### 缺點

❌ 沒有圖形界面（需手動編輯設定檔）
❌ 需要理解 SSH config 語法
❌ 金鑰檔案路徑必須使用絕對路徑

---

## 方式二：TortoiseGit + PuTTY Key File

### 概述

透過 TortoiseGit 的圖形界面直接指定 `.ppk` 金鑰檔案，不需要 SSH config 或 Pageant。

### 適用情境

✅ 只使用 TortoiseGit，不用命令列
✅ 不同專案使用不同金鑰
✅ 偏好圖形化設定
✅ 已經有 `.ppk` 格式金鑰

### 設定步驟

#### 1. 設定 TortoiseGit 使用 PuTTY

1. **TortoiseGit** → **Settings** → **Network**
2. **SSH client** 設為：
   ```
   C:\Program Files\TortoiseGit\bin\TortoiseGitPlink.exe
   ```
3. 確定

#### 2. 設定 Git Remote（使用標準 GitHub URL）

```bash
# 新增遠端（使用標準 GitHub URL）
git remote add origin git@github.com:username/your-repository.git
```

**重點：**
- URL 使用標準的 `git@github.com:<使用者>/<倉庫>.git`
- **不需要** SSH config 的 Host 別名

#### 3. 設定遠端使用的 PuTTY Key

**方法 A：透過 TortoiseGit 圖形界面**

1. **TortoiseGit** → **Settings** → **Git** → **Remote**
2. **Remote** 下拉選擇要設定的遠端（例如：`origin`）
3. **Putty Key** 點擊 `...` 瀏覽選擇：
   ```
   D:\path\to\ssh-keys\project-name\private.ppk
   ```
4. 點擊 **Add/Save**

**方法 B：手動編輯 Git Config**

編輯 `.git\config`：

```ini
[remote "origin"]
    url = git@github.com:username/your-repository.git
    fetch = +refs/heads/*:refs/remotes/origin/*
    puttykeyfile = D:\\path\\to\\ssh-keys\\project-name\\private.ppk
```

**注意：** 路徑使用雙反斜線 `\\`

#### 4. 測試連線

使用 TortoiseGit 圖形界面：

1. 右鍵 → **TortoiseGit** → **Pull**
2. 選擇 Remote: `origin`
3. 執行

或使用命令列（必須使用 TortoiseGitPlink）：
```bash
# 設定使用 TortoiseGitPlink
set GIT_SSH=C:\Program Files\TortoiseGit\bin\TortoiseGitPlink.exe

# 測試
git fetch origin
```

### 優點

✅ 圖形化設定，不需編輯設定檔
✅ 每個遠端可指定不同金鑰
✅ 金鑰檔案可放在任意位置
✅ 不需要載入 Pageant

### 缺點

❌ 只適用於 TortoiseGit
❌ 命令列 `git.exe` 無法使用（除非設定 GIT_SSH）
❌ 不支援 SSH config
❌ 切換專案時要確認設定正確

---

## 方式三：Pageant 金鑰代理

### 概述

Pageant 是 PuTTY 的 SSH 金鑰代理程式，將金鑰載入記憶體後，所有使用 PuTTY 的程式都能自動使用。

### 適用情境

✅ 使用多個需要相同金鑰的專案
✅ 不想在每個專案設定金鑰路徑
✅ 金鑰有密碼保護，只想輸入一次
✅ 習慣 PuTTY/TortoiseSVN 工作流程

### 設定步驟

#### 1. 設定 TortoiseGit 使用 PuTTY

1. **TortoiseGit** → **Settings** → **Network**
2. **SSH client** 設為：
   ```
   C:\Program Files\TortoiseGit\bin\TortoiseGitPlink.exe
   ```
3. 確定

#### 2. 啟動 Pageant

執行 Pageant：
```
C:\Program Files\TortoiseGit\bin\pageant.exe
```

或
```
C:\Program Files\PuTTY\pageant.exe
```

系統匣會出現 Pageant 圖示（小電腦 🖥️）。

#### 3. 載入金鑰到 Pageant

**方法 A：拖曳**
- 將 `.ppk` 檔案拖曳到 Pageant 圖示上

**方法 B：右鍵選單**
- 右鍵點擊 `.ppk` 檔案 → **Load into Pageant**

**方法 C：Pageant 介面**
1. 右鍵 Pageant 圖示 → **View Keys**
2. 點擊 **Add Key**
3. 選擇 `.ppk` 檔案

如果金鑰有密碼，會提示輸入密碼（只需輸入一次）。

#### 4. 驗證金鑰已載入

右鍵 Pageant 圖示 → **View Keys**

會看到：
```
Algorithm    Comment                           Source
ssh-rsa 2048 rsa-key-20260210                  D:\...\private.ppk
```

#### 5. 設定 Git Remote

```bash
# 使用標準 GitHub URL
git remote add origin git@github.com:username/your-repository.git
```

**重點：**
- 使用標準 `git@github.com` URL
- **不需要**在 Git config 設定 `puttykeyfile`
- TortoiseGitPlink 會自動從 Pageant 取得金鑰

#### 6. 測試連線

```bash
# TortoiseGit 拉取
右鍵 → TortoiseGit → Pull
```

Pageant 會自動提供金鑰，無需額外設定。

### 開機自動啟動 Pageant

**方法 A：加入啟動資料夾**

1. 建立捷徑到 `pageant.exe`
2. 修改捷徑目標為：
   ```
   "C:\Program Files\TortoiseGit\bin\pageant.exe" "D:\path\to\key1.ppk" "D:\path\to\key2.ppk"
   ```
3. 將捷徑複製到：
   ```
   C:\Users\<使用者>\AppData\Roaming\Microsoft\Windows\Start Menu\Programs\Startup
   ```

**方法 B：工作排程器**

設定更進階的啟動條件。

### 優點

✅ 金鑰密碼只需輸入一次
✅ 所有使用 PuTTY 的程式自動共用金鑰
✅ 不需要在每個專案設定金鑰路徑
✅ 適合管理多個金鑰
✅ 安全性高（金鑰在記憶體中，關機自動清除）

### 缺點

❌ 需要手動啟動 Pageant（除非設定自動啟動）
❌ 只適用於 PuTTY 系統（OpenSSH 不支援）
❌ 忘記載入金鑰會導致認證失敗
❌ Windows 限定

---

## 比較與建議

### TortoiseGit SSH 客戶端設定的影響

**TortoiseGit 的 SSH client 設定決定了使用哪套系統：**

| SSH Client 設定 | 使用系統 | 金鑰設定方式 | 遠端 URL 格式 |
|----------------|---------|------------|-------------|
| 空白 或 `ssh.exe` | OpenSSH | `~/.ssh/config` | `git@<Host別名>:user/repo.git` |
| `TortoiseGitPlink.exe` | PuTTY | `.git/config` 的 `puttykeyfile` 或 Pageant | `git@github.com:user/repo.git` |

**重要：**
- 設定為 OpenSSH (`ssh.exe`) → **自動套用** `~/.ssh/config`，忽略 TortoiseGit 的 "Putty Key" 設定
- 設定為 PuTTY (`TortoiseGitPlink.exe`) → **不讀取** `~/.ssh/config`，使用 `puttykeyfile` 或 Pageant

### 功能比較表

| 功能 | 方式一：SSH Config | 方式二：PuTTY Key File | 方式三：Pageant |
|------|-------------------|----------------------|----------------|
| **使用工具** | OpenSSH | TortoiseGit + PuTTY | TortoiseGit + PuTTY |
| **金鑰格式** | OpenSSH | .ppk | .ppk |
| **設定方式** | 編輯文字檔 | 圖形界面 | 圖形界面 |
| **命令列支援** | ✅ 完整支援 | ❌ 需額外設定 | ❌ 需額外設定 |
| **TortoiseGit** | ✅ 支援 | ✅ 支援 | ✅ 支援 |
| **VS Code** | ✅ 支援 | ❌ 不支援 | ❌ 不支援 |
| **跨平台** | ✅ 是 | ❌ Windows only | ❌ Windows only |
| **管理多金鑰** | ✅ 優秀 | ⚠️ 手動設定 | ✅ 優秀 |
| **設定複雜度** | ⚠️ 中等 | ✅ 簡單 | ✅ 簡單 |
| **密碼保護** | ⚠️ 每次輸入 | ⚠️ 每次輸入 | ✅ 只輸入一次 |

### 建議使用情境

#### 🏆 **推薦：方式一（SSH Config）**

適合：
- 專業開發者
- 需要管理多個專案/帳號
- 同時使用多種 Git 工具
- 重視標準化和跨平台

**範例情境：**
- 同時參與公司專案、個人專案、客戶專案
- 使用 Git Bash、VS Code、TortoiseGit
- 需要在不同電腦同步設定

#### 方式二（PuTTY Key File）

適合：
- TortoiseGit 重度使用者
- 只需管理少數專案
- 不使用命令列
- 偏好圖形化操作

**範例情境：**
- 只維護 1-2 個專案
- 只使用 TortoiseGit，不用 VS Code 或命令列
- 不想學習 SSH config 語法

#### 方式三（Pageant）

適合：
- 金鑰有密碼保護
- 頻繁進行 Git 操作
- 管理多個使用相同認證系統的服務
- 已熟悉 PuTTY 生態系統

**範例情境：**
- 金鑰設有高強度密碼
- 一天內需多次 push/pull
- 同時使用 PuTTY SSH 連線遠端伺服器

### 混合使用建議

可以在不同專案使用不同方式：

```
ProjectA/  → 使用方式一（SSH Config）
ProjectB/  → 使用方式二（PuTTY Key File）
Pageant    → 載入常用金鑰
```

但建議**保持一致性**，避免混淆。

---

## 常見問題

### Q1: 為什麼會出現 "Permission denied (publickey)" 錯誤？

**可能原因：**

1. **金鑰不匹配**
   ```bash
   # 比對指紋
   ssh-keygen -lf /path/to/private-key
   ```
   確認與 GitHub Deploy Key 指紋一致。

2. **SSH Config Host 別名錯誤**
   ```bash
   # 錯誤
   git remote add origin git@github.com:user/repo.git

   # 正確（使用 SSH config 中的 Host 別名）
   git remote add origin git@github-your-alias:user/repo.git
   ```

3. **TortoiseGit 使用錯誤的 SSH 客戶端**
   - 檢查 Settings → Network → SSH client
   - 如果要用 `.ppk` + `puttykeyfile` 設定，必須是 `TortoiseGitPlink.exe`
   - 如果要用 `~/.ssh/config`，必須設為 `ssh.exe` 或空白（使用 OpenSSH）
   - **兩者不能混用**：OpenSSH 不認 `.ppk`，PuTTY 不讀 `~/.ssh/config`

4. **Deploy Key 未新增到 GitHub**
   - 檢查 Repository → Settings → Deploy keys
   - 確認公鑰已新增

5. **Pageant 未載入金鑰**
   - 右鍵 Pageant → View Keys
   - 確認金鑰已載入

### Q2: OpenSSH 和 PuTTY 金鑰可以互轉嗎？

**可以，使用 PuTTYgen：**

**OpenSSH → PuTTY (.ppk)**
1. PuTTYgen → **Conversions** → **Import key**
2. 選擇 OpenSSH 私鑰（檔案類型選 "All Files"）
3. **Save private key** → 存為 `.ppk`

**PuTTY (.ppk) → OpenSSH**
1. PuTTYgen → **Load** 載入 `.ppk`
2. **Conversions** → **Export OpenSSH key**
3. 存為 `private-openssh`（無副檔名）

**驗證轉換成功：**
```bash
# 比對指紋
ssh-keygen -lf private-openssh

# PuTTYgen 視窗也顯示指紋
# 兩者應該相同
```

### Q3: 可以同時使用多個 Deploy Keys 嗎？

**可以，有兩種方式：**

**方式 A：SSH Config（推薦）**
```ssh-config
Host github-project-a
    HostName github.com
    IdentityFile /path/to/key-a

Host github-project-b
    HostName github.com
    IdentityFile /path/to/key-b
```

使用：
```bash
git remote add origin git@github-project-a:user/repo-a.git
git remote add origin git@github-project-b:user/repo-b.git
```

**方式 B：不同專案設定不同 puttykeyfile**

ProjectA/.git/config：
```ini
[remote "origin"]
    puttykeyfile = D:\\Keys\\key-a.ppk
```

ProjectB/.git/config：
```ini
[remote "origin"]
    puttykeyfile = D:\\Keys\\key-b.ppk
```

### Q4: 如何檢查目前使用哪個金鑰？

**OpenSSH（方式一）：**
```bash
# 顯示詳細連線過程
ssh -Tv git@github-your-alias

# 會顯示：
# debug1: Offering public key: /path/to/key RSA SHA256:xxx
```

**PuTTY（方式二、三）：**
```bash
# 檢查 git config
git config --get remote.origin.puttykeyfile

# 或檢查 Pageant
右鍵 Pageant 圖示 → View Keys
```

### Q5: 金鑰檔案要設定什麼權限？

**Linux/Mac：**
```bash
chmod 600 ~/.ssh/private-key
chmod 644 ~/.ssh/private-key.pub
```

**Windows：**
- Git Bash 的 chmod 在 Windows 上通常無效
- Windows 使用 NTFS 權限，OpenSSH 會檢查但不嚴格要求
- 如果遇到權限警告，可嘗試：
  1. 右鍵金鑰檔案 → 內容 → 安全性
  2. 進階 → 停用繼承
  3. 移除所有使用者，只保留當前使用者的完全控制

### Q6: Deploy Key 和個人 SSH Key 有什麼差別？

| 項目 | Deploy Key | Personal SSH Key |
|------|-----------|------------------|
| **範圍** | 單一 Repository | 整個 GitHub 帳號 |
| **權限** | 可設定唯讀或讀寫 | 完整帳號權限 |
| **用途** | CI/CD、自動化部署 | 個人開發 |
| **建議** | 生產環境、伺服器 | 本機開發 |
| **安全性** | 高（限定單一 repo） | 中（可存取所有 repo） |

**建議：**
- 開發機：使用個人 SSH Key
- 生產伺服器：使用 Deploy Key（唯讀）
- CI/CD：使用 Deploy Key（讀寫，如需推送）

### Q7: TortoiseGit 可以使用 OpenSSH 嗎？

**可以！但必須正確設定。**

**常見誤解：**
- ❌ "TortoiseGit 只能用 PuTTY"
- ❌ "TortoiseGit 不支援 SSH config"

**正確理解：**
- ✅ TortoiseGit **可以使用 OpenSSH**（設定 SSH client 為 `ssh.exe` 或空白）
- ✅ 使用 OpenSSH 時，**自動套用** `~/.ssh/config` 設定
- ✅ 使用 OpenSSH 時，**無法透過 TortoiseGit GUI 指定金鑰**（必須在 SSH config 設定）

**設定方式：**
1. TortoiseGit → Settings → Network → SSH client 設為空白或 `ssh.exe`
2. 設定 `~/.ssh/config`（參考方式一）
3. Git remote URL 使用 SSH config 的 Host 別名

**範例：**
```ssh-config
# ~/.ssh/config
Host github-my-project
    HostName github.com
    IdentityFile D:/path/to/key
```

```bash
# Git remote
git remote add origin git@github-my-project:user/repo.git
```

TortoiseGit 會自動使用 SSH config 中的設定，與命令列完全一致。

### Q8: 忘記金鑰密碼怎麼辦？

**無法復原**，必須：
1. 產生新的金鑰對
2. 更新 GitHub Deploy Key
3. 更新本機設定

**防範措施：**
- 使用密碼管理器儲存金鑰密碼
- 或不設定密碼（但降低安全性）
- 使用 Pageant 減少輸入次數

---

## 總結

### 快速決策流程圖

```
是否需要跨工具（命令列、VS Code、TortoiseGit）？
├─ 是 → 使用方式一（SSH Config）
└─ 否 → 只用 TortoiseGit？
         ├─ 是 → 金鑰有密碼且頻繁操作？
         │        ├─ 是 → 使用方式三（Pageant）
         │        └─ 否 → 使用方式二（PuTTY Key File）
         └─ 否 → 使用方式一（SSH Config）
```

### 最佳實踐

1. **優先選擇 SSH Config（方式一）**
   - 標準、通用、跨平台
   - 所有工具都支援
   - 易於維護和分享設定

2. **每個專案/環境使用獨立金鑰**
   - 提高安全性
   - 降低金鑰洩露風險
   - 方便權限管理

3. **金鑰命名規範**
   ```
   專案名稱-環境-用途
   例如：myproject-prod-deploy
        myproject-dev-ci
        website-staging-readonly
   ```

4. **定期更換金鑰**
   - 建議每年更換
   - 離職人員相關金鑰立即撤銷

5. **備份金鑰但加密儲存**
   - 使用密碼管理器
   - 或加密的雲端儲存
   - 切勿明文儲存

6. **文件化金鑰配置**
   - 記錄每個金鑰的用途
   - 記錄設定方式
   - 方便團隊成員理解

---

## 參考資源

- [GitHub Deploy Keys 官方文件](https://docs.github.com/en/developers/overview/managing-deploy-keys)
- [OpenSSH Config 手冊](https://man.openbsd.org/ssh_config)
- [PuTTY 官方文件](https://www.chiark.greenend.org.uk/~sgtatham/putty/docs.html)
- [TortoiseGit 官方文件](https://tortoisegit.org/docs/)

---

**最後更新：** 2026-02-10
**版本：** 1.0
**作者：** Elon PHP
