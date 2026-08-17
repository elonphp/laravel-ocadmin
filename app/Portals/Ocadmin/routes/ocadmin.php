<?php

use Illuminate\Support\Facades\Route;
use App\Portals\Ocadmin\Core\Controllers\LoginController;
use App\Portals\Ocadmin\Core\Controllers\System\TaxonomyController;
use App\Portals\Ocadmin\Core\Controllers\System\TermController;
use App\Portals\Ocadmin\Core\Controllers\System\Acl\PermissionController;
use App\Portals\Ocadmin\Core\Controllers\System\Acl\RoleController;
use App\Portals\Ocadmin\Core\Controllers\System\Acl\UserController;
use App\Portals\Ocadmin\Modules\Dashboard\DashboardController;
use App\Portals\Ocadmin\Modules\Org\Organization\OrganizationController;
use App\Portals\Ocadmin\Modules\Org\Company\CompanyController;
use App\Portals\Ocadmin\Modules\Org\Department\DepartmentController;
use App\Portals\Ocadmin\Modules\Org\Employee\EmployeeController;
use App\Portals\Ocadmin\Core\Controllers\System\Acl\AccessTokenController;
use App\Portals\Ocadmin\Core\Controllers\System\MenuController;
use App\Portals\Ocadmin\Core\Controllers\System\MenuTreeController;
use App\Portals\Ocadmin\Core\Controllers\System\SettingController;
use App\Portals\Ocadmin\Core\Controllers\System\LogController;
use App\Portals\Ocadmin\Core\Controllers\System\SchemaController;
use App\Portals\Ocadmin\Core\Controllers\System\TransitionController;
use App\Portals\Ocadmin\Modules\Catalog\Option\OptionController;
use App\Portals\Ocadmin\Modules\Catalog\OptionValueGroup\OptionValueGroupController;
use App\Portals\Ocadmin\Modules\Catalog\OptionValueLink\OptionValueLinkController;
use App\Portals\Ocadmin\Modules\Catalog\Product\ProductController;
use App\Portals\Ocadmin\Modules\Member\Member\MemberController;
use App\Portals\Ocadmin\Core\Controllers\ImageManagerController;
use App\Portals\Ocadmin\Core\Controllers\Account\ProfileController;
use App\Portals\Ocadmin\Core\Controllers\System\Acl\UserDeviceController;
use App\Portals\Ocadmin\Modules\Account\UserDeviceController as AccountUserDeviceController;
use App\Helpers\Classes\CrudRoutesHelper;

/*
|--------------------------------------------------------------------------
| Ocadmin Routes
|--------------------------------------------------------------------------
|
| Permission 強制檢查策略（見 docs/common/10007_權限機制.md §六）：
|   - 標準 8 routes CRUD resource：用 CrudRoutesHelper::register() 一行帶 .access/.modify/.delete
|   - 非標準 / 部分 CRUD：手動 ->middleware('can:' . permName(...))
|   - 豁免：base / login / dashboard / account self-service / schema (super_admin 硬鎖)
|
*/

Route::group([
    'prefix' => '{locale}/admin',
    'as' => 'lang.ocadmin.',
    'middleware' => 'setLocale',
], function () {

    // 認證路由 (Guest)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login.form');
        Route::post('/login', [LoginController::class, 'login'])->name('login');
    });

    // 登出
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // 需要登入的路由
    Route::middleware(['auth', 'requirePortalRole:' . config('portals.ocadmin.role_prefix'), 'logRequest'])->group(function () {

        // Dashboard（豁免：任何 admin role 都可進首頁）
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Dashboard API（豁免：dashboard widget 資料）
        Route::get('/dashboard/chart-sales', [DashboardController::class, 'chartSales'])->name('dashboard.chart-sales');
        Route::get('/dashboard/map-data', [DashboardController::class, 'mapData'])->name('dashboard.map-data');

        // 個人帳號（豁免：self-service，登入即可）
        Route::prefix('account')->name('account.')->group(function () {
            Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
            Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

            // 我的裝置
            Route::prefix('user-devices')->name('user-devices.')->group(function () {
                Route::get('/', [AccountUserDeviceController::class, 'index'])->name('index');
                Route::get('/list', [AccountUserDeviceController::class, 'list'])->name('list');
                Route::post('/revoke', [AccountUserDeviceController::class, 'revoke'])->name('revoke');
                Route::post('/revoke-others', [AccountUserDeviceController::class, 'revokeOthers'])->name('revoke-others');
            });
        });

        // 共用工具（Mode A 鎖 permission；Mode B 建議衍生專案直接不註冊本段，見 10007）
        Route::prefix('common')->name('common.')->group(function () {
            Route::prefix('image-manager')->name('image-manager.')->group(function () {
                Route::get('/',         [ImageManagerController::class, 'index'])->middleware('can:' . permName('common.image_manager.access'))->name('index');
                Route::get('/list',     [ImageManagerController::class, 'list'])->middleware('can:' . permName('common.image_manager.access'))->name('list');
                Route::post('/upload',  [ImageManagerController::class, 'upload'])->middleware('can:' . permName('common.image_manager.modify'))->name('upload');
                Route::post('/folder',  [ImageManagerController::class, 'folder'])->middleware('can:' . permName('common.image_manager.modify'))->name('folder');
                Route::post('/delete',  [ImageManagerController::class, 'delete'])->middleware('can:' . permName('common.image_manager.delete'))->name('delete');
            });
        });

        // 會員管理
        Route::prefix('member')->name('member.')->group(function () {
            CrudRoutesHelper::register('members', MemberController::class, 'member.member', 'user');
        });

        // 組織管理
        Route::prefix('org')->name('org.')->group(function () {
            CrudRoutesHelper::register('organizations', OrganizationController::class, 'org.organization', 'organization');
            CrudRoutesHelper::register('companies',     CompanyController::class,      'org.company',      'company');
            CrudRoutesHelper::register('departments',   DepartmentController::class,   'org.department',   'department', [
                ['get', '/by-company', 'byCompany', 'by-company', 'access'],
            ]);
            CrudRoutesHelper::register('employees',     EmployeeController::class,     'org.employee',     'employee', [
                ['get', '/search-users', 'searchUsers', 'search-users', 'access'],
            ]);
        });

        // 系統管理
        Route::prefix('system')->name('system.')->group(function () {

            // ACL：權限 / 角色 / 使用者 / Access Token / 裝置
            Route::prefix('acl')->name('acl.')->group(function () {

                CrudRoutesHelper::register('permissions', PermissionController::class, 'system_acl.permission', 'permission');

                CrudRoutesHelper::register('roles', RoleController::class, 'system_acl.role', 'role', [
                    ['get', '/search', 'search', 'search', 'access'],
                ]);

                CrudRoutesHelper::register('users', UserController::class, 'system_acl.user', 'user');

                // Access Token（非標準 CRUD：form / save / revoke 模式）
                Route::prefix('access-tokens')->name('access-tokens.')->group(function () {
                    Route::get('/',                  [AccessTokenController::class, 'index'])->middleware('can:' . permName('system_acl.access_token.access'))->name('index');
                    Route::get('/list',              [AccessTokenController::class, 'list'])->middleware('can:' . permName('system_acl.access_token.access'))->name('list');
                    Route::get('/form/{id?}',        [AccessTokenController::class, 'form'])->middleware('can:' . permName('system_acl.access_token.access'))->name('form');
                    Route::post('/save/{id?}',       [AccessTokenController::class, 'save'])->middleware('can:' . permName('system_acl.access_token.modify'))->name('save');
                    Route::post('/revoke',           [AccessTokenController::class, 'revoke'])->middleware('can:' . permName('system_acl.access_token.delete'))->name('revoke');
                    Route::get('/search-users',      [AccessTokenController::class, 'searchUsers'])->middleware('can:' . permName('system_acl.access_token.access'))->name('search-users');
                });

                // 裝置管理（只有 index / list / forceRevoke）
                Route::prefix('user-devices')->name('user-devices.')->group(function () {
                    Route::get('/',              [UserDeviceController::class, 'index'])->middleware('can:' . permName('system_acl.user_device.access'))->name('index');
                    Route::get('/list',          [UserDeviceController::class, 'list'])->middleware('can:' . permName('system_acl.user_device.access'))->name('list');
                    Route::post('/force-revoke', [UserDeviceController::class, 'forceRevoke'])->middleware('can:' . permName('system_acl.user_device.delete'))->name('force-revoke');
                    Route::get('/search-users',  [UserDeviceController::class, 'searchUsers'])->middleware('can:' . permName('system_acl.user_device.access'))->name('search-users');
                });

            });

            // 日誌管理（read-only：index / list / form 都只 .access）
            Route::prefix('logs')->name('logs.')->group(function () {
                Route::get('/',                  [LogController::class, 'index'])->middleware('can:' . permName('system.log.access'))->name('index');
                Route::get('/list',              [LogController::class, 'list'])->middleware('can:' . permName('system.log.access'))->name('list');
                Route::get('/form/{requestLog}', [LogController::class, 'form'])->middleware('can:' . permName('system.log.access'))->name('form');
            });

            // 資料表結構管理（super_admin 已在 controller 內 hasRole 硬鎖，route 層豁免）
            Route::prefix('schemas')->name('schemas.')->group(function () {
                Route::get('/',                  [SchemaController::class, 'index'])->name('index');
                Route::get('/{table}/edit',      [SchemaController::class, 'edit'])->name('edit');
                Route::post('/{table}/preview',  [SchemaController::class, 'preview'])->name('preview');
                Route::put('/{table}',           [SchemaController::class, 'update'])->name('update');
            });

            // 遷移功能（執行 database/transitions/；權限不指派 role → 實質 super_admin-only）
            Route::prefix('transition')->name('transition.')->group(function () {
                Route::get('/',        [TransitionController::class, 'index'])->middleware('can:' . permName('system.transition.access'))->name('index');
                Route::post('/preview', [TransitionController::class, 'preview'])->middleware('can:' . permName('system.transition.access'))->name('preview');
                Route::post('/run',     [TransitionController::class, 'run'])->middleware('can:' . permName('system.transition.run'))->name('run');
            });

            // 選單樹狀結構（共用 system.menu.* permission）
            Route::prefix('menu-tree')->name('menu-tree.')->group(function () {
                Route::get('/',         [MenuTreeController::class, 'index'])->middleware('can:' . permName('system.menu.access'))->name('index');
                Route::post('/reorder', [MenuTreeController::class, 'reorder'])->middleware('can:' . permName('system.menu.modify'))->name('reorder');
            });

            // 選單設定
            CrudRoutesHelper::register('menus', MenuController::class, 'system.menu', 'menu');

            // 參數設定
            CrudRoutesHelper::register('settings', SettingController::class, 'system.setting', 'setting', [
                ['post', '/parse-serialize', 'parseSerialize', 'parse-serialize', 'modify'],
                ['post', '/to-serialize',    'toSerialize',    'to-serialize',    'modify'],
            ]);

            // 分類管理
            CrudRoutesHelper::register('taxonomies', TaxonomyController::class, 'system.taxonomy', 'taxonomy');

            // 詞彙項目
            CrudRoutesHelper::register('terms', TermController::class, 'system.term', 'term', [
                ['get', '/by-taxonomy/{taxonomy}', 'byTaxonomy', 'by-taxonomy', 'access'],
            ]);

        });

        // 商品型錄
        Route::prefix('catalog')->name('catalog.')->group(function () {

            CrudRoutesHelper::register('products',            ProductController::class,           'catalog.product',            'product');
            CrudRoutesHelper::register('options',             OptionController::class,            'catalog.option',             'option');
            CrudRoutesHelper::register('option-value-groups', OptionValueGroupController::class,  'catalog.option_value_group', 'option_value_group');

            // 選項值連動（非標準 CRUD：index / links / saveLinks / children）
            Route::prefix('option-value-links')->name('option-value-links.')->group(function () {
                Route::get('/',                            [OptionValueLinkController::class, 'index'])->middleware('can:' . permName('catalog.option_value_link.access'))->name('index');
                Route::get('/links/{parentValueId}',       [OptionValueLinkController::class, 'links'])->middleware('can:' . permName('catalog.option_value_link.access'))->name('links');
                Route::post('/save-links',                 [OptionValueLinkController::class, 'saveLinks'])->middleware('can:' . permName('catalog.option_value_link.modify'))->name('save-links');
                Route::get('/children/{optionValueId}',    [OptionValueLinkController::class, 'children'])->middleware('can:' . permName('catalog.option_value_link.access'))->name('children');
            });

        });

    });

});

/*
|--------------------------------------------------------------------------
| 無語系前綴的重導向
|--------------------------------------------------------------------------
*/
Route::get('/admin/{any?}', function ($any = '') {
    $defaultLocale = config('localization.default_locale', 'zh_Hant');
    $urlMapping = config('localization.url_mapping', []);
    $flipped = array_flip($urlMapping);
    $urlLocale = $flipped[$defaultLocale] ?? 'zh-hant';

    $path = $any ? "/admin/{$any}" : '/admin';
    $queryString = request()->getQueryString();

    $redirectUrl = "/{$urlLocale}{$path}";
    if ($queryString) {
        $redirectUrl .= '?' . $queryString;
    }

    return redirect($redirectUrl);
})->where('any', '.*');
