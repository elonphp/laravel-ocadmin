<?php

namespace App\Portals\Ocadmin\Core\Controllers\Config;

use App\Helpers\Classes\LocaleHelper;
use App\Models\Config\Taxonomy;
use Illuminate\Http\Request;
use App\Portals\Ocadmin\Core\Controllers\Controller;

class TaxonomyController extends Controller
{
    protected function setBreadcrumbs(): void
    {
        $this->breadcrumbs = [
            (object)[
                'text' => '首頁',
                'href' => route('lang.ocadmin.dashboard'),
            ],
            (object)[
                'text' => '系統管理',
                'href' => 'javascript:void(0)',
            ],
            (object)[
                'text' => '分類管理',
                'href' => route('lang.ocadmin.config.taxonomy.index'),
            ],
        ];
    }

    public function index(Request $request)
    {
        $query = Taxonomy::with('translations');

        if ($request->filled('filter_code')) {
            $query->where('code', 'like', '%' . $request->filter_code . '%');
        }

        if ($request->filled('filter_name')) {
            $query->whereTranslationLike('name', '%' . $request->filter_name . '%');
        }

        if ($request->filled('filter_is_active')) {
            $query->where('is_active', $request->filter_is_active);
        }

        $sortBy = $request->get('sort', 'sort_order');
        $order = $request->get('order', 'asc');
        $query->orderBy($sortBy, $order);

        $taxonomies = $query->withCount('terms')->paginate(20)->withQueryString();

        return view('ocadmin::config.taxonomy.index', [
            'taxonomies' => $taxonomies,
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    public function create()
    {
        return view('ocadmin::config.taxonomy.form', [
            'taxonomy' => new Taxonomy(),
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'code' => 'required|string|max:50|unique:taxonomies,code|regex:/^[a-z][a-z0-9_]*$/',
            'description' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        foreach (LocaleHelper::getSupportedLocales() as $locale) {
            $rules["translations.{$locale}.name"] = 'required|string|max:100';
        }

        $validated = $request->validate($rules);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->has('is_active');

        $taxonomy = Taxonomy::create($validated);
        $taxonomy->saveTranslations($validated['translations']);

        return redirect()
            ->route('lang.ocadmin.config.taxonomy.index')
            ->with('success', '分類新增成功！');
    }

    public function edit(Taxonomy $taxonomy)
    {
        $taxonomy->load('translations');

        return view('ocadmin::config.taxonomy.form', [
            'taxonomy' => $taxonomy,
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    public function update(Request $request, Taxonomy $taxonomy)
    {
        $rules = [
            'code' => 'required|string|max:50|unique:taxonomies,code,' . $taxonomy->id . '|regex:/^[a-z][a-z0-9_]*$/',
            'description' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        foreach (LocaleHelper::getSupportedLocales() as $locale) {
            $rules["translations.{$locale}.name"] = 'required|string|max:100';
        }

        $validated = $request->validate($rules);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->has('is_active');

        $taxonomy->update($validated);
        $taxonomy->saveTranslations($validated['translations']);

        return redirect()
            ->route('lang.ocadmin.config.taxonomy.index')
            ->with('success', '分類更新成功！');
    }

    public function destroy(Taxonomy $taxonomy)
    {
        if ($taxonomy->terms()->exists()) {
            return response()->json([
                'success' => false,
                'message' => '此分類下仍有詞彙項目，請先刪除詞彙項目',
            ]);
        }

        $taxonomy->delete();

        return response()->json(['success' => true]);
    }

    public function batchDelete(Request $request)
    {
        $ids = $request->input('selected', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => '請選擇要刪除的項目']);
        }

        $hasTerms = Taxonomy::whereIn('id', $ids)->whereHas('terms')->exists();
        if ($hasTerms) {
            return response()->json([
                'success' => false,
                'message' => '部分分類下仍有詞彙項目，請先刪除詞彙項目',
            ]);
        }

        Taxonomy::whereIn('id', $ids)->delete();

        return response()->json(['success' => true]);
    }
}
