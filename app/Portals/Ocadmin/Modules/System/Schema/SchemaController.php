<?php

namespace App\Portals\Ocadmin\Modules\System\Schema;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;

class SchemaController extends OcadminController
{
    protected SchemaService $service;

    public function __construct(SchemaService $service)
    {
        $this->service = $service;
        parent::__construct();

        // 僅 super_admin 可存取
        $this->middleware(function ($request, $next) {
            abort_unless(auth()->user()?->hasRole('super_admin') ?? false, 404);
            return $next($request);
        });
    }

    protected function setLangFiles(): array
    {
        return ['system/schema'];
    }

    /**
     * 列表：所有業務表
     */
    public function index(): View
    {
        $data['lang']   = $this->lang;
        $data['tables'] = $this->service->getTableList();

        return view('ocadmin::system.schema.index', $data);
    }

    /**
     * 編輯單表：扁平表格，原名明示
     */
    public function edit(string $table): View
    {
        $structure = $this->service->getTableStructure($table);

        $data['lang']           = $this->lang;
        $data['table_name']     = $table;
        $data['table_comment']  = $structure['comment'];
        $data['columns']        = $structure['columns'];
        $data['supportedTypes'] = $this->getSupportedTypes();

        $data['preview_url'] = route('lang.ocadmin.system.schemas.preview', $table);
        $data['update_url']  = route('lang.ocadmin.system.schemas.update', $table);
        $data['back_url']    = route('lang.ocadmin.system.schemas.index');

        return view('ocadmin::system.schema.form', $data);
    }

    /**
     * 預覽 SQL（不執行）
     */
    public function preview(Request $request, string $table): JsonResponse
    {
        $columns = $request->input('columns', []);
        $sqls = $this->service->buildAlterSql($table, $columns);

        return response()->json([
            'success' => true,
            'sqls'    => $sqls,
        ]);
    }

    /**
     * 執行 ALTER
     */
    public function update(Request $request, string $table): JsonResponse
    {
        $columns = $request->input('columns', []);

        try {
            $result = $this->service->applyAlter($table, $columns);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success'  => true,
            'message'  => $this->lang->text_success_apply,
            'executed' => $result['executed'],
        ]);
    }

    /**
     * 支援的欄位類型（給下拉選單用）
     */
    protected function getSupportedTypes(): array
    {
        return [
            'Integer' => ['tinyint', 'smallint', 'mediumint', 'int', 'bigint'],
            'Decimal' => ['decimal', 'float', 'double'],
            'String'  => ['char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext'],
            'Date'    => ['date', 'time', 'datetime', 'timestamp', 'year'],
            'Other'   => ['json', 'boolean', 'enum', 'binary', 'varbinary'],
        ];
    }
}
