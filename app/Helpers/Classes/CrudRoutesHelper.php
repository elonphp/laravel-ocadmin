<?php

namespace App\Helpers\Classes;

use Illuminate\Support\Facades\Route;

use function permName;

/**
 * 註冊標準 CRUD 8 routes + 對應 can: middleware，並可串 ad-hoc action。
 *
 * 在 outer Route::prefix(...)->name(...)->group() 內呼叫；本類別開自己的
 * `prefix($urlPrefix)->name($urlPrefix . '.')` 子層。
 *
 * 8 條標準 routes 與權限對應：
 *
 *   GET    /                      index        .access
 *   GET    /list                  list         .access
 *   GET    /create                create       .modify   ← 開「新增表單」前就鎖（gatekeep early，避免填完才 403）
 *   POST   /                      store        .modify
 *   GET    /{binding}/edit        edit         .access   ← 編輯表單可看，但下方 update 才會擋
 *   PUT    /{binding}             update       .modify
 *   DELETE /{binding}             destroy      .delete
 *   POST   /batch-delete          batchDelete  .delete
 *
 * Ad-hoc action 透過 $adhoc 陣列傳入，每筆格式：
 *   [$httpMethod, $url, $controllerMethod, $routeName, $permAction]
 *
 * 範例：
 *   CrudRoutesHelper::register('departments', DepartmentController::class, 'org.department', 'department', [
 *       ['get', '/by-company', 'byCompany', 'by-company', 'access'],
 *   ]);
 *
 * @see docs/common/10007_權限機制.md
 */
class CrudRoutesHelper
{
    /**
     * @param  string  $urlPrefix     URL 路徑段（kebab-case 複數，如 'products'）
     * @param  string  $controller    Controller class 名（如 ProductController::class）
     * @param  string  $permResource  Permission 中段（不含 prefix 與 action，如 'catalog.product'）
     * @param  string  $routeBinding  Route Model Binding 變數名（如 'product' 對應 {product}）
     * @param  array   $adhoc         Ad-hoc routes（見上）
     */
    public static function register(
        string $urlPrefix,
        string $controller,
        string $permResource,
        string $routeBinding,
        array $adhoc = []
    ): void {
        Route::prefix($urlPrefix)
            ->name($urlPrefix . '.')
            ->group(function () use ($controller, $permResource, $routeBinding, $adhoc) {
                $access = 'can:' . permName("{$permResource}.access");
                $modify = 'can:' . permName("{$permResource}.modify");
                $delete = 'can:' . permName("{$permResource}.delete");

                // ad-hoc 先註冊：避免同 method 字面路徑（如 PUT /promote）被後面的 PUT /{binding} 吞掉
                foreach ($adhoc as [$method, $url, $action, $name, $permAction]) {
                    Route::$method($url, [$controller, $action])
                        ->middleware('can:' . permName("{$permResource}.{$permAction}"))
                        ->name($name);
                }

                Route::get('/',                          [$controller, 'index'])->middleware($access)->name('index');
                Route::get('/list',                      [$controller, 'list'])->middleware($access)->name('list');
                Route::get('/create',                    [$controller, 'create'])->middleware($modify)->name('create');
                Route::post('/',                         [$controller, 'store'])->middleware($modify)->name('store');
                Route::get("/{{$routeBinding}}/edit",    [$controller, 'edit'])->middleware($access)->name('edit');
                Route::put("/{{$routeBinding}}",         [$controller, 'update'])->middleware($modify)->name('update');
                Route::delete("/{{$routeBinding}}",      [$controller, 'destroy'])->middleware($delete)->name('destroy');
                Route::post('/batch-delete',             [$controller, 'batchDelete'])->middleware($delete)->name('batch-delete');
            });
    }
}
