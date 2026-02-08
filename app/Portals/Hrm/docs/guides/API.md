# HRM Portal - Postman API 測試範例

## 基本設定

- **Base URL**: `http://localhost/hrm`
- **Content-Type**: `application/json`
- **Accept**: `application/json`

---

## 行事曆管理 API

### 1. 取得行事曆列表

```http
GET /hrm/calendar
```

**Query Parameters**:
```
?year=2026
&month=2
&day_type=workday
&is_workday=1
&search=春節
&sort=date
&order=desc
&per_page=20
```

**Response**:
```json
{
  "calendars": {
    "data": [
      {
        "id": 1,
        "date": "2026-02-10",
        "day_type": "workday",
        "is_workday": true,
        "name": null,
        "description": null,
        "color": null,
        "created_at": "2026-02-09T12:00:00.000000Z",
        "updated_at": "2026-02-09T12:00:00.000000Z"
      }
    ],
    "current_page": 1,
    "total": 100
  },
  "filters": {...},
  "breadcrumbs": [...]
}
```

---

### 2. 新增行事曆記錄

```http
POST /hrm/calendar
Content-Type: application/json
```

**Request Body**:
```json
{
  "date": "2026-02-10",
  "day_type": "workday",
  "is_workday": true,
  "name": null,
  "description": null,
  "color": "#3B82F6"
}
```

**Response**:
```json
{
  "success": true,
  "message": "行事曆記錄新增成功",
  "data": {
    "id": 1,
    "date": "2026-02-10",
    "day_type": "workday",
    "is_workday": true,
    ...
  }
}
```

---

### 3. 更新行事曆記錄

```http
PUT /hrm/calendar/1
Content-Type: application/json
```

**Request Body**:
```json
{
  "date": "2026-02-10",
  "day_type": "holiday",
  "is_workday": false,
  "name": "春節",
  "description": "農曆新年",
  "color": "#EF4444"
}
```

**Response**:
```json
{
  "success": true,
  "message": "行事曆記錄更新成功",
  "data": {...}
}
```

---

### 4. 刪除行事曆記錄

```http
DELETE /hrm/calendar/1
```

**Response**:
```json
{
  "success": true,
  "message": "行事曆記錄刪除成功"
}
```

---

### 5. 批次建立工作日

```http
POST /hrm/calendar/batch-create
Content-Type: application/json
```

**Request Body**:
```json
{
  "start_date": "2026-03-01",
  "end_date": "2026-03-31",
  "weekends": [0, 6]
}
```

**說明**:
- `weekends`: 陣列，0=週日, 1=週一, ..., 6=週六
- 預設 `[0, 6]` 表示週日和週六為週末

**Response**:
```json
{
  "success": true,
  "message": "批次建立成功，共建立 31 筆記錄",
  "count": 31
}
```

---

### 6. 批次刪除

```http
POST /hrm/calendar/batch-delete
Content-Type: application/json
```

**Request Body**:
```json
{
  "ids": [1, 2, 3, 4, 5]
}
```

**Response**:
```json
{
  "success": true,
  "message": "批次刪除成功"
}
```

---

### 7. 匯入國定假日

```http
POST /hrm/calendar/import-holidays
Content-Type: application/json
```

**Request Body**:
```json
{
  "holidays": [
    {
      "date": "2026-01-01",
      "name": "中華民國開國紀念日"
    },
    {
      "date": "2026-02-17",
      "name": "春節"
    },
    {
      "date": "2026-02-18",
      "name": "春節"
    },
    {
      "date": "2026-02-19",
      "name": "春節"
    },
    {
      "date": "2026-02-20",
      "name": "春節"
    },
    {
      "date": "2026-02-21",
      "name": "春節"
    },
    {
      "date": "2026-02-28",
      "name": "和平紀念日"
    },
    {
      "date": "2026-04-04",
      "name": "兒童節及清明節"
    },
    {
      "date": "2026-04-05",
      "name": "兒童節及清明節"
    },
    {
      "date": "2026-06-14",
      "name": "端午節"
    },
    {
      "date": "2026-09-20",
      "name": "中秋節"
    },
    {
      "date": "2026-10-10",
      "name": "國慶日"
    }
  ]
}
```

**Response**:
```json
{
  "success": true,
  "message": "匯入成功，共處理 12 筆假日",
  "count": 12
}
```

---

### 8. 設定補班日

```http
POST /hrm/calendar/set-makeup-workday
Content-Type: application/json
```

**Request Body**:
```json
{
  "date": "2026-02-07",
  "name": "補 2/20（五）春節之班"
}
```

**Response**:
```json
{
  "success": true,
  "message": "補班日設定成功",
  "data": {
    "id": 10,
    "date": "2026-02-07",
    "day_type": "makeup_workday",
    "is_workday": true,
    "name": "補 2/20（五）春節之班",
    ...
  }
}
```

---

### 9. 取得月曆資料（API）

```http
GET /hrm/calendar/api/month?year=2026&month=2
```

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "date": "2026-02-01",
      "day_type": "weekend",
      "is_workday": false,
      ...
    },
    {
      "id": 2,
      "date": "2026-02-02",
      "day_type": "workday",
      "is_workday": true,
      ...
    },
    ...
  ]
}
```

---

## 日期類型說明

| day_type | 說明 | is_workday |
|----------|------|------------|
| `workday` | 工作日 | `true` |
| `weekend` | 週末 | `false` |
| `holiday` | 國定假日 | `false` |
| `company_holiday` | 公司假日 | `false` |
| `makeup_workday` | 補班日 | `true` |
| `typhoon_day` | 颱風假 | `false` |

---

## 測試流程建議

### 步驟 1：批次建立 2026 年 3 月的工作日
```http
POST /hrm/calendar/batch-create
{
  "start_date": "2026-03-01",
  "end_date": "2026-03-31",
  "weekends": [0, 6]
}
```

### 步驟 2：匯入 2026 年國定假日
```http
POST /hrm/calendar/import-holidays
{
  "holidays": [...]
}
```

### 步驟 3：設定補班日
```http
POST /hrm/calendar/set-makeup-workday
{
  "date": "2026-02-07",
  "name": "補班"
}
```

### 步驟 4：查詢 2 月的行事曆
```http
GET /hrm/calendar?year=2026&month=2
```

### 步驟 5：查看單筆記錄（需要透過 Inertia）
```http
GET /hrm/calendar/1
```

---

## Laravel Tinker 測試

```bash
php artisan tinker
```

```php
// 測試 Service
$service = app(\App\Portals\Hrm\Modules\Calendar\CalendarDayService::class);

// 批次建立工作日
$service->batchCreateWorkdays(
    \Carbon\Carbon::parse('2026-03-01'),
    \Carbon\Carbon::parse('2026-03-31')
);

// 匯入假日
$service->importHolidays([
    ['date' => '2026-01-01', 'name' => '元旦'],
    ['date' => '2026-02-17', 'name' => '春節'],
]);

// 設定補班日
$service->setMakeupWorkday('2026-02-07', '補班');

// 查詢
\App\Models\Hrm\CalendarDay::whereBetween('date', ['2026-02-01', '2026-02-28'])->get();
```

---

## 前端工程師接手指南

當前端工程師要建立 React 組件時，Controller 已經返回以下資料結構：

### Index 頁面
```tsx
// resources/js/Pages/Calendar/Index.tsx
import { PageProps } from '@/types';

interface CalendarDay {
  id: number;
  date: string;
  day_type: string;
  is_workday: boolean;
  name: string | null;
  description: string | null;
  color: string | null;
  created_at: string;
  updated_at: string;
}

interface Props extends PageProps {
  calendars: {
    data: CalendarDay[];
    current_page: number;
    total: number;
    // ... Laravel pagination props
  };
  filters: {
    year?: number;
    month?: number;
    day_type?: string;
    is_workday?: boolean;
    search?: string;
  };
}

export default function Index({ calendars, filters }: Props) {
  // 使用付費模板的 UI 組件
  return (
    <div>
      {/* 實作列表介面 */}
    </div>
  );
}
```
