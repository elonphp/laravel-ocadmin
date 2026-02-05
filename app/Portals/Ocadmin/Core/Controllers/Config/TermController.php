<?php

namespace App\Portals\Ocadmin\Core\Controllers\Config;

use App\Helpers\Classes\LocaleHelper;
use App\Models\Config\Taxonomy;
use App\Models\Config\Term;
use Illuminate\Http\Request;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;

class TermController extends OcadminController
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
                'text' => '詞彙項目',
                'href' => route('lang.ocadmin.config.term.index'),
            ],
        ];
    }

    public function index(Request $request)
    {
        $query = Term::with('taxonomy.translations', 'parent.translations', 'translations');

        if ($request->filled('filter_taxonomy_id')) {
            $query->where('taxonomy_id', $request->filter_taxonomy_id);
        }

        if ($request->filled('filter_code')) {
            $query->where('code', 'like', '%' . $request->filter_code . '%');
        }

        if ($request->filled('filter_name')) {
            $query->whereTranslationLike('name', '%' . $request->filter_name . '%');
        }

        if ($request->filled('equal_is_active')) {
            $query->where('is_active', $request->equal_is_active);
        } elseif (!$request->has('equal_is_active')) {
            $query->where('is_active', 1);
        }

        $sortBy = $request->get('sort', 'sort_order');
        $order = $request->get('order', 'asc');
        $query->orderBy($sortBy, $order);

        $terms = $query->paginate(20)->withQueryString();
        $taxonomies = Taxonomy::with('translations')->orderBy('sort_order')->get();

        return view('ocadmin::config.term.index', [
            'terms' => $terms,
            'taxonomies' => $taxonomies,
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    public function create(Request $request)
    {
        $taxonomies = Taxonomy::with('translations')->orderBy('sort_order')->get();

        $parentTerms = [];
        if ($request->filled('taxonomy_id')) {
            $parentTerms = Term::with('translations')
                ->where('taxonomy_id', $request->taxonomy_id)
                ->orderBy('sort_order')
                ->get();
        }

        $term = new Term();
        $term->taxonomy_id = $request->taxonomy_id;

        return view('ocadmin::config.term.form', [
            'term' => $term,
            'taxonomies' => $taxonomies,
            'parentTerms' => $parentTerms,
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'taxonomy_id' => 'required|exists:taxonomies,id',
            'parent_id' => 'nullable|exists:terms,id',
            'code' => 'required|string|max:50|regex:/^[a-z][a-z0-9_]*$/',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        foreach (LocaleHelper::getSupportedLocales() as $locale) {
            $rules["translations.{$locale}.name"] = 'required|string|max:100';
        }

        $validated = $request->validate($rules);

        $exists = Term::where('taxonomy_id', $validated['taxonomy_id'])
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['code' => '此代碼在該分類下已存在']);
        }

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->has('is_active');

        $term = Term::create($validated);
        $term->saveTranslations($validated['translations']);

        return redirect()
            ->route('lang.ocadmin.config.term.index', ['filter_taxonomy_id' => $validated['taxonomy_id']])
            ->with('success', '詞彙項目新增成功！');
    }

    public function edit(Term $term)
    {
        $term->load('translations');
        $taxonomies = Taxonomy::with('translations')->orderBy('sort_order')->get();

        $parentTerms = Term::with('translations')
            ->where('taxonomy_id', $term->taxonomy_id)
            ->where('id', '!=', $term->id)
            ->orderBy('sort_order')
            ->get();

        return view('ocadmin::config.term.form', [
            'term' => $term,
            'taxonomies' => $taxonomies,
            'parentTerms' => $parentTerms,
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    public function update(Request $request, Term $term)
    {
        $rules = [
            'taxonomy_id' => 'required|exists:taxonomies,id',
            'parent_id' => 'nullable|exists:terms,id',
            'code' => 'required|string|max:50|regex:/^[a-z][a-z0-9_]*$/',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];

        foreach (LocaleHelper::getSupportedLocales() as $locale) {
            $rules["translations.{$locale}.name"] = 'required|string|max:100';
        }

        $validated = $request->validate($rules);

        $exists = Term::where('taxonomy_id', $validated['taxonomy_id'])
            ->where('code', $validated['code'])
            ->where('id', '!=', $term->id)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['code' => '此代碼在該分類下已存在']);
        }

        if ($validated['parent_id'] == $term->id) {
            return back()
                ->withInput()
                ->withErrors(['parent_id' => '不能將自己設為父項目']);
        }

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->has('is_active');

        $term->update($validated);
        $term->saveTranslations($validated['translations']);

        return redirect()
            ->route('lang.ocadmin.config.term.index', ['filter_taxonomy_id' => $validated['taxonomy_id']])
            ->with('success', '詞彙項目更新成功！');
    }

    public function destroy(Term $term)
    {
        if ($term->children()->exists()) {
            return response()->json([
                'success' => false,
                'message' => '此項目下仍有子項目，請先刪除子項目',
            ]);
        }

        $term->delete();

        return response()->json(['success' => true]);
    }

    public function batchDelete(Request $request)
    {
        $ids = $request->input('selected', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => '請選擇要刪除的項目']);
        }

        $hasChildren = Term::whereIn('id', $ids)->whereHas('children')->exists();
        if ($hasChildren) {
            return response()->json([
                'success' => false,
                'message' => '部分項目下仍有子項目，請先刪除子項目',
            ]);
        }

        Term::whereIn('id', $ids)->delete();

        return response()->json(['success' => true]);
    }

    /**
     * JSON：取得某分類下的詞彙（供 AJAX 下拉選單）
     */
    public function byTaxonomy(Taxonomy $taxonomy)
    {
        $terms = $taxonomy->terms()
            ->with('translations')
            ->select('id', 'parent_id', 'code', 'taxonomy_id')
            ->get()
            ->map(fn ($term) => [
                'id' => $term->id,
                'parent_id' => $term->parent_id,
                'code' => $term->code,
                'name' => $term->name,
            ]);

        return response()->json($terms);
    }
}
