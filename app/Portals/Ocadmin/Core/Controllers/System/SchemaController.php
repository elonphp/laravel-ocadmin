<?php

namespace App\Portals\Ocadmin\Core\Controllers\System;

use App\Services\System\Database\SchemaDiffService;
use App\Services\System\Database\SchemaExportService;
use App\Services\System\Database\SchemaParserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;

class SchemaController extends OcadminController
{
    protected SchemaParserService $parser;
    protected SchemaExportService $exporter;
    protected SchemaDiffService $differ;

    public function __construct(
        SchemaParserService $parser,
        SchemaExportService $exporter,
        SchemaDiffService $differ
    ) {
        $this->parser = $parser;
        $this->exporter = $exporter;
        $this->differ = $differ;
        parent::__construct();
    }

    protected function setLangFiles(): array
    {
        return ['common', 'system/schema'];
    }

    protected function setBreadcrumbs(): void
    {
        $this->breadcrumbs = [
            (object)[
                'text' => $this->lang->text_home,
                'href' => route('lang.ocadmin.dashboard'),
            ],
            (object)[
                'text' => $this->lang->text_system,
                'href' => 'javascript:void(0)',
            ],
            (object)[
                'text' => $this->lang->heading_title,
                'href' => route('lang.ocadmin.system.schema.index'),
            ],
        ];
    }

    /**
     * 列表頁
     */
    public function index(Request $request): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['list'] = $this->getList($request);

        return view('ocadmin::system.schema.index', $data);
    }

    /**
     * AJAX 列表刷新
     */
    public function list(Request $request): string
    {
        return $this->getList($request);
    }

    /**
     * 核心列表查詢
     */
    protected function getList(Request $request): string
    {
        $overview = $this->differ->getStatusOverview();

        // 篩選：表名
        if ($request->filled('filter_name')) {
            $search = $request->filter_name;
            $overview = array_filter($overview, fn($t) => str_contains($t['name'], $search));
        }

        // 篩選：狀態
        if ($request->filled('filter_status')) {
            $status = $request->filter_status;
            $overview = array_filter($overview, fn($t) => $t['status'] === $status);
        }

        $data['lang'] = $this->lang;
        $data['tables'] = array_values($overview);

        return view('ocadmin::system.schema.list', $data)->render();
    }

    /**
     * 新增頁面
     */
    public function create(): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['table_name'] = '';
        $data['is_new'] = true;
        $data['columns'] = [];
        $data['translations'] = [];
        $data['compositeIndexes'] = [];
        $data['comment'] = '';
        $data['supportedTypes'] = $this->getSupportedTypes();

        return view('ocadmin::system.schema.form', $data);
    }

    /**
     * 儲存新增
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'table_name' => 'required|string|regex:/^[a-z][a-z0-9_]*$/',
        ]);

        $tableName = $request->input('table_name');
        $schema = $this->buildSchemaFromRequest($request);

        $this->parser->saveSchemaFile($tableName, $schema);

        return response()->json([
            'success'      => true,
            'message'      => $this->lang->text_success_save,
            'replace_url'  => route('lang.ocadmin.system.schema.edit', $tableName),
            'form_action'  => route('lang.ocadmin.system.schema.update', $tableName),
        ]);
    }

    /**
     * 編輯頁面
     */
    public function edit(string $table): View
    {
        $schema = $this->parser->loadSchemaFile($table);

        // 將定義字串解析為欄位屬性陣列，供表單使用
        $columns = [];
        if ($schema && !empty($schema['columns'])) {
            foreach ($schema['columns'] as $name => $definition) {
                $meta = $this->parser->parseColumnDefinition($definition);
                $meta['name'] = $name;
                $columns[] = $meta;
            }
        }

        $translations = [];
        if ($schema && !empty($schema['translations'])) {
            foreach ($schema['translations'] as $name => $definition) {
                $meta = $this->parser->parseColumnDefinition($definition);
                $meta['name'] = $name;
                $translations[] = $meta;
            }
        }

        // 複合索引 + 複合唯一 → 合併為統一陣列
        $compositeIndexes = [];
        foreach ($schema['indexes'] ?? [] as $name => $cols) {
            $compositeIndexes[] = [
                'name'    => $name,
                'type'    => 'INDEX',
                'columns' => implode(', ', $cols),
            ];
        }
        foreach ($schema['unique'] ?? [] as $name => $cols) {
            $compositeIndexes[] = [
                'name'    => $name,
                'type'    => 'UNIQUE',
                'columns' => implode(', ', $cols),
            ];
        }

        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['table_name'] = $table;
        $data['is_new'] = false;
        $data['columns'] = $columns;
        $data['translations'] = $translations;
        $data['compositeIndexes'] = $compositeIndexes;
        $data['comment'] = $schema['comment'] ?? '';
        $data['supportedTypes'] = $this->getSupportedTypes();

        return view('ocadmin::system.schema.form', $data);
    }

    /**
     * 儲存更新
     */
    public function update(Request $request, string $table): JsonResponse
    {
        $schema = $this->buildSchemaFromRequest($request);
        $this->parser->saveSchemaFile($table, $schema);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_save,
        ]);
    }

    /**
     * 差異比對（AJAX）
     */
    public function diff(string $table): JsonResponse
    {
        $result = $this->differ->diff($table);
        $sqls = $this->differ->generateSql($table);

        return response()->json([
            'success' => true,
            'diff'    => $result,
            'sqls'    => $sqls,
        ]);
    }

    /**
     * 執行同步
     */
    public function sync(string $table): JsonResponse
    {
        $result = $this->differ->apply($table);

        return response()->json([
            'success'  => true,
            'message'  => $this->lang->text_success_sync,
            'executed' => $result['executed'],
        ]);
    }

    /**
     * 從 DB 匯出 schema 檔
     */
    public function export(string $table): JsonResponse
    {
        $this->exporter->exportToSchemaFile($table);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_export,
        ]);
    }

    /**
     * 匯出所有表
     */
    public function exportAll(): JsonResponse
    {
        $exported = $this->exporter->exportAll();

        return response()->json([
            'success' => true,
            'message' => sprintf($this->lang->text_success_export_all, count($exported)),
            'tables'  => $exported,
        ]);
    }

    /**
     * 從 request 建構 schema 陣列
     */
    protected function buildSchemaFromRequest(Request $request): array
    {
        $schema = [];

        if ($request->filled('comment')) {
            $schema['comment'] = $request->input('comment');
        }

        // 主欄位
        $schema['columns'] = [];
        foreach ($request->input('columns', []) as $col) {
            if (empty($col['name']) || empty($col['type'])) {
                continue;
            }

            $meta = [
                'type'           => $col['type'],
                'length'         => $col['length'] ?? null,
                'unsigned'       => !empty($col['unsigned']),
                'nullable'       => !empty($col['nullable']),
                'default'        => $col['default'] ?? null,
                'has_default'    => isset($col['default']) && $col['default'] !== '',
                'auto_increment' => !empty($col['auto_increment']),
                'primary'        => !empty($col['primary']),
                'index'          => !empty($col['index']),
                'unique'         => !empty($col['unique']),
                'foreign'        => $col['foreign'] ?? null,
                'comment'        => $col['comment'] ?? null,
                'after'          => null,
            ];

            $schema['columns'][$col['name']] = $this->parser->buildColumnDefinition($meta);
        }

        // 翻譯欄位
        $translations = [];
        foreach ($request->input('translations', []) as $col) {
            if (empty($col['name']) || empty($col['type'])) {
                continue;
            }

            $meta = [
                'type'           => $col['type'],
                'length'         => $col['length'] ?? null,
                'unsigned'       => false,
                'nullable'       => !empty($col['nullable']),
                'default'        => null,
                'has_default'    => false,
                'auto_increment' => false,
                'index'          => false,
                'unique'         => false,
                'foreign'        => null,
                'comment'        => $col['comment'] ?? null,
                'after'          => null,
            ];

            $translations[$col['name']] = $this->parser->buildColumnDefinition($meta);
        }

        // 複合索引
        $indexes = [];
        $uniques = [];
        foreach ($request->input('composite_indexes', []) as $idx) {
            if (empty($idx['name']) || empty($idx['columns'])) {
                continue;
            }
            $cols = array_map('trim', explode(',', $idx['columns']));
            $cols = array_filter($cols);
            if (empty($cols)) {
                continue;
            }

            if (($idx['type'] ?? 'INDEX') === 'UNIQUE') {
                $uniques[$idx['name']] = array_values($cols);
            } else {
                $indexes[$idx['name']] = array_values($cols);
            }
        }

        if (!empty($indexes)) {
            $schema['indexes'] = $indexes;
        }
        if (!empty($uniques)) {
            $schema['unique'] = $uniques;
        }

        if (!empty($translations)) {
            $schema['translations'] = $translations;
        }

        return $schema;
    }

    /**
     * 支援的欄位類型（分群組）
     */
    protected function getSupportedTypes(): array
    {
        return [
            'Integer' => ['tinyint', 'smallint', 'mediumint', 'int', 'bigint'],
            'Decimal' => ['decimal', 'float', 'double'],
            'String'  => ['char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext'],
            'Date'    => ['date', 'time', 'datetime', 'timestamp', 'year'],
            'Other'   => ['json', 'boolean', 'enum', 'set', 'binary', 'varbinary'],
        ];
    }
}
