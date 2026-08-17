<?php

use Illuminate\Support\Facades\Route;
use App\Portals\WebV1\Core\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| WebV1 Routes — 範本層公開官網範例
|--------------------------------------------------------------------------
|
| URL 形態：/{locale}/...（url_slug='' 不帶中間層，對齊 config/portals.php 的 webv1 段）
| Route 名稱：lang.webv1.*
|
| 雙模架構：本 portal 屬於 Mode A 的前台。Mode B（ocadmin 兼任前台）不該存在 webv1 段，
| 衝突由 OcadminServiceProvider::assertPortalModeConsistency() 在 boot 階段擋下。
| 詳見 docs/common/10001_Portal概述.md §九。
|
*/

Route::group([
    'prefix' => '{locale}',
    'as' => 'lang.webv1.',
    'middleware' => 'setLocale',
], function () {

    Route::get('/', [HomeController::class, 'index'])->name('home');

});
