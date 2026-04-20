<?php

use Illuminate\Support\Facades\Route;
use App\Portals\Ocadmin\Core\Controllers\LoginController;
use App\Portals\Ocadmin\Modules\System\Term\TaxonomyController;
use App\Portals\Ocadmin\Modules\System\Term\TermController;
use App\Portals\Ocadmin\Modules\System\Acl\PermissionController;
use App\Portals\Ocadmin\Modules\System\Acl\RoleController;
use App\Portals\Ocadmin\Modules\System\Acl\UserController;
use App\Portals\Ocadmin\Modules\Dashboard\DashboardController;
use App\Portals\Ocadmin\Modules\Organization\OrganizationController;
use App\Portals\Ocadmin\Modules\Hrm\Company\CompanyController;
use App\Portals\Ocadmin\Modules\Hrm\Department\DepartmentController;
use App\Portals\Ocadmin\Modules\Hrm\Employee\EmployeeController;
use App\Portals\Ocadmin\Modules\System\Acl\AccessTokenController;
use App\Portals\Ocadmin\Modules\System\Menu\MenuController;
use App\Portals\Ocadmin\Modules\System\Menu\MenuTreeController;
use App\Portals\Ocadmin\Modules\System\Setting\SettingController;
use App\Portals\Ocadmin\Modules\System\Log\LogController;
use App\Portals\Ocadmin\Modules\System\Schema\SchemaController;
use App\Portals\Ocadmin\Modules\Catalog\Option\OptionController;
use App\Portals\Ocadmin\Modules\Catalog\OptionValueGroup\OptionValueGroupController;
use App\Portals\Ocadmin\Modules\Catalog\OptionValueLink\OptionValueLinkController;
use App\Portals\Ocadmin\Modules\Catalog\Product\ProductController;
use App\Portals\Ocadmin\Modules\Member\Member\MemberController;
use App\Portals\Ocadmin\Core\Controllers\ImageManagerController;
use App\Portals\Ocadmin\Modules\Account\ProfileController;
use App\Portals\Ocadmin\Modules\System\Acl\UserDeviceAdminController;
use App\Portals\Ocadmin\Modules\Account\UserDeviceController;

/*
|--------------------------------------------------------------------------
| Ocadmin Routes
|--------------------------------------------------------------------------
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
    Route::middleware(['auth', 'requirePortalRole:admin', 'logRequest'])->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Dashboard API
        Route::get('/dashboard/chart-sales', [DashboardController::class, 'chartSales'])->name('dashboard.chart-sales');
        Route::get('/dashboard/map-data', [DashboardController::class, 'mapData'])->name('dashboard.map-data');

        // 個人帳號
        Route::prefix('account')->name('account.')->group(function () {
            Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
            Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

            // 我的裝置
            Route::prefix('user-devices')->name('user-devices.')->group(function () {
                Route::get('/', [UserDeviceController::class, 'index'])->name('index');
                Route::get('/list', [UserDeviceController::class, 'list'])->name('list');
                Route::post('/revoke', [UserDeviceController::class, 'revoke'])->name('revoke');
                Route::post('/revoke-others', [UserDeviceController::class, 'revokeOthers'])->name('revoke-others');
            });
        });

        // 共用工具
        Route::prefix('common')->name('common.')->group(function () {
            Route::prefix('image-manager')->name('image-manager.')->group(function () {
                Route::get('/', [ImageManagerController::class, 'index'])->name('index');
                Route::get('/list', [ImageManagerController::class, 'list'])->name('list');
                Route::post('/upload', [ImageManagerController::class, 'upload'])->name('upload');
                Route::post('/folder', [ImageManagerController::class, 'folder'])->name('folder');
                Route::post('/delete', [ImageManagerController::class, 'delete'])->name('delete');
            });
        });

        // 組織管理
        Route::prefix('organizations')->name('organizations.')->group(function () {
            Route::get('/', [OrganizationController::class, 'index'])->name('index');
            Route::get('/list', [OrganizationController::class, 'list'])->name('list');
            Route::get('/create', [OrganizationController::class, 'create'])->name('create');
            Route::post('/', [OrganizationController::class, 'store'])->name('store');
            Route::get('/{organization}/edit', [OrganizationController::class, 'edit'])->name('edit');
            Route::put('/{organization}', [OrganizationController::class, 'update'])->name('update');
            Route::delete('/{organization}', [OrganizationController::class, 'destroy'])->name('destroy');
            Route::post('/batch-delete', [OrganizationController::class, 'batchDelete'])->name('batch-delete');
        });

        // 會員管理
        Route::prefix('member')->name('member.')->group(function () {

            // 會員
            Route::prefix('members')->name('members.')->group(function () {
                Route::get('/', [MemberController::class, 'index'])->name('index');
                Route::get('/list', [MemberController::class, 'list'])->name('list');
                Route::get('/create', [MemberController::class, 'create'])->name('create');
                Route::post('/', [MemberController::class, 'store'])->name('store');
                Route::get('/{user}/edit', [MemberController::class, 'edit'])->name('edit');
                Route::put('/{user}', [MemberController::class, 'update'])->name('update');
                Route::delete('/{user}', [MemberController::class, 'destroy'])->name('destroy');
                Route::post('/batch-delete', [MemberController::class, 'batchDelete'])->name('batch-delete');
            });

        });

        // 人資管理
        Route::prefix('hrm')->name('hrm.')->group(function () {

            // 公司管理
            Route::prefix('companies')->name('companies.')->group(function () {
                Route::get('/', [CompanyController::class, 'index'])->name('index');
                Route::get('/list', [CompanyController::class, 'list'])->name('list');
                Route::get('/create', [CompanyController::class, 'create'])->name('create');
                Route::post('/', [CompanyController::class, 'store'])->name('store');
                Route::get('/{company}/edit', [CompanyController::class, 'edit'])->name('edit');
                Route::put('/{company}', [CompanyController::class, 'update'])->name('update');
                Route::delete('/{company}', [CompanyController::class, 'destroy'])->name('destroy');
                Route::post('/batch-delete', [CompanyController::class, 'batchDelete'])->name('batch-delete');
            });

            // 部門管理
            Route::prefix('departments')->name('departments.')->group(function () {
                Route::get('/', [DepartmentController::class, 'index'])->name('index');
                Route::get('/list', [DepartmentController::class, 'list'])->name('list');
                Route::get('/create', [DepartmentController::class, 'create'])->name('create');
                Route::post('/', [DepartmentController::class, 'store'])->name('store');
                Route::get('/{department}/edit', [DepartmentController::class, 'edit'])->name('edit');
                Route::put('/{department}', [DepartmentController::class, 'update'])->name('update');
                Route::delete('/{department}', [DepartmentController::class, 'destroy'])->name('destroy');
                Route::post('/batch-delete', [DepartmentController::class, 'batchDelete'])->name('batch-delete');
                Route::get('/by-company', [DepartmentController::class, 'byCompany'])->name('by-company');
            });

            // 員工管理
            Route::prefix('employees')->name('employees.')->group(function () {
                Route::get('/', [EmployeeController::class, 'index'])->name('index');
                Route::get('/list', [EmployeeController::class, 'list'])->name('list');
                Route::get('/create', [EmployeeController::class, 'create'])->name('create');
                Route::post('/', [EmployeeController::class, 'store'])->name('store');
                Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('edit');
                Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update');
                Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy');
                Route::post('/batch-delete', [EmployeeController::class, 'batchDelete'])->name('batch-delete');
                Route::get('/search-users', [EmployeeController::class, 'searchUsers'])->name('search-users');
            });

        });

        // 系統管理
        Route::prefix('system')->name('system.')->group(function () {

            // 權限管理
            Route::prefix('permissions')->name('permissions.')->group(function () {
                Route::get('/', [PermissionController::class, 'index'])->name('index');
                Route::get('/list', [PermissionController::class, 'list'])->name('list');
                Route::get('/create', [PermissionController::class, 'create'])->name('create');
                Route::post('/', [PermissionController::class, 'store'])->name('store');
                Route::get('/{permission}/edit', [PermissionController::class, 'edit'])->name('edit');
                Route::put('/{permission}', [PermissionController::class, 'update'])->name('update');
                Route::delete('/{permission}', [PermissionController::class, 'destroy'])->name('destroy');
                Route::post('/batch-delete', [PermissionController::class, 'batchDelete'])->name('batch-delete');
            });

            // 角色管理
            Route::prefix('roles')->name('roles.')->group(function () {
                Route::get('/', [RoleController::class, 'index'])->name('index');
                Route::get('/list', [RoleController::class, 'list'])->name('list');
                Route::get('/search', [RoleController::class, 'search'])->name('search');
                Route::get('/create', [RoleController::class, 'create'])->name('create');
                Route::post('/', [RoleController::class, 'store'])->name('store');
                Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('edit');
                Route::put('/{role}', [RoleController::class, 'update'])->name('update');
                Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
                Route::post('/batch-delete', [RoleController::class, 'batchDelete'])->name('batch-delete');
            });

            // 使用者管理
            Route::prefix('users')->name('users.')->group(function () {
                Route::get('/', [UserController::class, 'index'])->name('index');
                Route::get('/list', [UserController::class, 'list'])->name('list');
                Route::get('/create', [UserController::class, 'create'])->name('create');
                Route::post('/', [UserController::class, 'store'])->name('store');
                Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
                Route::put('/{user}', [UserController::class, 'update'])->name('update');
                Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
                Route::post('/batch-delete', [UserController::class, 'batchDelete'])->name('batch-delete');
            });

            // Access Token 管理
            Route::prefix('access-tokens')->name('access-tokens.')->group(function () {
                Route::get('/', [AccessTokenController::class, 'index'])->name('index');
                Route::get('/list', [AccessTokenController::class, 'list'])->name('list');
                Route::get('/form/{id?}', [AccessTokenController::class, 'form'])->name('form');
                Route::post('/save/{id?}', [AccessTokenController::class, 'save'])->name('save');
                Route::post('/revoke', [AccessTokenController::class, 'revoke'])->name('revoke');
                Route::get('/search-users', [AccessTokenController::class, 'searchUsers'])->name('search-users');
            });

            // 裝置管理
            Route::prefix('user-devices')->name('user-devices.')->group(function () {
                Route::get('/', [UserDeviceAdminController::class, 'index'])->name('index');
                Route::get('/list', [UserDeviceAdminController::class, 'list'])->name('list');
                Route::post('/force-revoke', [UserDeviceAdminController::class, 'forceRevoke'])->name('force-revoke');
                Route::get('/search-users', [UserDeviceAdminController::class, 'searchUsers'])->name('search-users');
            });

            // 日誌管理
            Route::prefix('logs')->name('logs.')->group(function () {
                Route::get('/', [LogController::class, 'index'])->name('index');
                Route::get('/list', [LogController::class, 'list'])->name('list');
                Route::get('/form/{requestLog}', [LogController::class, 'form'])->name('form');
            });

            // 資料表結構管理（super_admin 即時 UI 變更）
            Route::prefix('schemas')->name('schemas.')->group(function () {
                Route::get('/', [SchemaController::class, 'index'])->name('index');
                Route::get('/{table}/edit', [SchemaController::class, 'edit'])->name('edit');
                Route::post('/{table}/preview', [SchemaController::class, 'preview'])->name('preview');
                Route::put('/{table}', [SchemaController::class, 'update'])->name('update');
            });

            // 選單樹狀結構
            Route::prefix('menu-tree')->name('menu-tree.')->group(function () {
                Route::get('/', [MenuTreeController::class, 'index'])->name('index');
                Route::post('/reorder', [MenuTreeController::class, 'reorder'])->name('reorder');
            });

            // 選單設定
            Route::prefix('menus')->name('menus.')->group(function () {
                Route::get('/', [MenuController::class, 'index'])->name('index');
                Route::get('/list', [MenuController::class, 'list'])->name('list');
                Route::get('/create', [MenuController::class, 'create'])->name('create');
                Route::post('/', [MenuController::class, 'store'])->name('store');
                Route::get('/{menu}/edit', [MenuController::class, 'edit'])->name('edit');
                Route::put('/{menu}', [MenuController::class, 'update'])->name('update');
                Route::delete('/{menu}', [MenuController::class, 'destroy'])->name('destroy');
                Route::post('/batch-delete', [MenuController::class, 'batchDelete'])->name('batch-delete');
            });

            // 參數設定
            Route::prefix('settings')->name('settings.')->group(function () {
                Route::get('/', [SettingController::class, 'index'])->name('index');
                Route::get('/list', [SettingController::class, 'list'])->name('list');
                Route::get('/create', [SettingController::class, 'create'])->name('create');
                Route::post('/', [SettingController::class, 'store'])->name('store');
                Route::get('/{setting}/edit', [SettingController::class, 'edit'])->name('edit');
                Route::put('/{setting}', [SettingController::class, 'update'])->name('update');
                Route::delete('/{setting}', [SettingController::class, 'destroy'])->name('destroy');
                Route::post('/batch-delete', [SettingController::class, 'batchDelete'])->name('batch-delete');
                Route::post('/parse-serialize', [SettingController::class, 'parseSerialize'])->name('parse-serialize');
                Route::post('/to-serialize', [SettingController::class, 'toSerialize'])->name('to-serialize');
            });

        });

        // 商品型錄
        Route::prefix('catalog')->name('catalog.')->group(function () {

            // 商品管理
            Route::prefix('products')->name('products.')->group(function () {
                Route::get('/', [ProductController::class, 'index'])->name('index');
                Route::get('/list', [ProductController::class, 'list'])->name('list');
                Route::get('/create', [ProductController::class, 'create'])->name('create');
                Route::post('/', [ProductController::class, 'store'])->name('store');
                Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
                Route::put('/{product}', [ProductController::class, 'update'])->name('update');
                Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
                Route::post('/batch-delete', [ProductController::class, 'batchDelete'])->name('batch-delete');
            });

            // 選項管理
            Route::prefix('options')->name('options.')->group(function () {
                Route::get('/', [OptionController::class, 'index'])->name('index');
                Route::get('/list', [OptionController::class, 'list'])->name('list');
                Route::get('/create', [OptionController::class, 'create'])->name('create');
                Route::post('/', [OptionController::class, 'store'])->name('store');
                Route::get('/{option}/edit', [OptionController::class, 'edit'])->name('edit');
                Route::put('/{option}', [OptionController::class, 'update'])->name('update');
                Route::delete('/{option}', [OptionController::class, 'destroy'])->name('destroy');
                Route::post('/batch-delete', [OptionController::class, 'batchDelete'])->name('batch-delete');
            });

            // 選項連動群組
            Route::prefix('option-value-groups')->name('option-value-groups.')->group(function () {
                Route::get('/', [OptionValueGroupController::class, 'index'])->name('index');
                Route::get('/list', [OptionValueGroupController::class, 'list'])->name('list');
                Route::get('/create', [OptionValueGroupController::class, 'create'])->name('create');
                Route::post('/', [OptionValueGroupController::class, 'store'])->name('store');
                Route::get('/{option_value_group}/edit', [OptionValueGroupController::class, 'edit'])->name('edit');
                Route::put('/{option_value_group}', [OptionValueGroupController::class, 'update'])->name('update');
                Route::delete('/{option_value_group}', [OptionValueGroupController::class, 'destroy'])->name('destroy');
                Route::post('/batch-delete', [OptionValueGroupController::class, 'batchDelete'])->name('batch-delete');
            });

            // 選項值連動
            Route::prefix('option-value-links')->name('option-value-links.')->group(function () {
                Route::get('/', [OptionValueLinkController::class, 'index'])->name('index');
                Route::get('/links/{parentValueId}', [OptionValueLinkController::class, 'links'])->name('links');
                Route::post('/save-links', [OptionValueLinkController::class, 'saveLinks'])->name('save-links');
                Route::get('/children/{optionValueId}', [OptionValueLinkController::class, 'children'])->name('children');
            });

        });

        // 組態管理
        Route::prefix('config')->name('config.')->group(function () {

            // 分類管理
            Route::prefix('taxonomies')->name('taxonomies.')->group(function () {
                Route::get('/', [TaxonomyController::class, 'index'])->name('index');
                Route::get('/list', [TaxonomyController::class, 'list'])->name('list');
                Route::get('/create', [TaxonomyController::class, 'create'])->name('create');
                Route::post('/', [TaxonomyController::class, 'store'])->name('store');
                Route::get('/{taxonomy}/edit', [TaxonomyController::class, 'edit'])->name('edit');
                Route::put('/{taxonomy}', [TaxonomyController::class, 'update'])->name('update');
                Route::delete('/{taxonomy}', [TaxonomyController::class, 'destroy'])->name('destroy');
                Route::post('/batch-delete', [TaxonomyController::class, 'batchDelete'])->name('batch-delete');
            });

            // 詞彙項目
            Route::prefix('terms')->name('terms.')->group(function () {
                Route::get('/', [TermController::class, 'index'])->name('index');
                Route::get('/list', [TermController::class, 'list'])->name('list');
                Route::get('/create', [TermController::class, 'create'])->name('create');
                Route::post('/', [TermController::class, 'store'])->name('store');
                Route::get('/{term}/edit', [TermController::class, 'edit'])->name('edit');
                Route::put('/{term}', [TermController::class, 'update'])->name('update');
                Route::delete('/{term}', [TermController::class, 'destroy'])->name('destroy');
                Route::post('/batch-delete', [TermController::class, 'batchDelete'])->name('batch-delete');
                Route::get('/by-taxonomy/{taxonomy}', [TermController::class, 'byTaxonomy'])->name('by-taxonomy');
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
