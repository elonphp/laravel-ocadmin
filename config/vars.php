<?php

/*
|--------------------------------------------------------------------------
| vars.php — 系統共用常數設定（衍生專案 hook 點）
|--------------------------------------------------------------------------
|
| 集中放置 `config('vars.{key}')` 讀取的全域常數。衍生專案覆寫本檔即可，
| 其他 service / middleware / view composer 不必改。
|
| keys：
| - menu_driver  選單來源：'code'（單檔 hardcoded，預設，clone 即跑）
|                       / 'database'（從 sys_menus 表讀取，需先 seed；適合
|                         runtime 後台編輯選單的場景）
|                詳見 docs/common/10011_選單機制.md
|
*/

return [

    'menu_driver' => env('MENU_DRIVER', 'code'),

];
