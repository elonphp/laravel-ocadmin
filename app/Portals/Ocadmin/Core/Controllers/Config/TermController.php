<?php

namespace App\Portals\Ocadmin\Core\Controllers\Config;

use App\Helpers\Classes\LocaleHelper;
use App\Models\Config\Taxonomy;
use App\Models\Config\Term;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;

class TermController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['common', 'config/term'];
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
                'href' => route('lang.ocadmin.config.term.index'),
            ],
        ];
    }

    public function index(Request $request): View
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

        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['terms'] = $query->paginate(20)->withQueryString();
        $data['taxonomies'] = Taxonomy::with('translations')->orderBy('sort_order')->get();

        return view('ocadmin::config.term.index', $data);
    }

    public function create(Request $request): View
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

        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['term'] = $term;
        $data['taxonomies'] = $taxonomies;
        $data['parentTerms'] = $parentTerms;

        return view('ocadmin::config.term.form', $data);
    }

    public function store(Request $request): JsonResponse
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
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_code_exists,
                'errors'  => ['code' => $this->lang->error_code_exists],
            ], 422);
        }

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->has('is_active');

        $term = Term::create($validated);
        $term->saveTranslations($validated['translations']);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_add,
            'replace_url' => route('lang.ocadmin.config.term.edit', $term),
            'form_action' => route('lang.ocadmin.config.term.update', $term),
        ]);
    }

    public function edit(Term $term): View
    {
        $term->load('translations');
        $taxonomies = Taxonomy::with('translations')->orderBy('sort_order')->get();

        $parentTerms = Term::with('translations')
            ->where('taxonomy_id', $term->taxonomy_id)
            ->where('id', '!=', $term->id)
            ->orderBy('sort_order')
            ->get();

        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['term'] = $term;
        $data['taxonomies'] = $taxonomies;
        $data['parentTerms'] = $parentTerms;

        return view('ocadmin::config.term.form', $data);
    }

    public function update(Request $request, Term $term): JsonResponse
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
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_code_exists,
                'errors'  => ['code' => $this->lang->error_code_exists],
            ], 422);
        }

        if ($validated['parent_id'] == $term->id) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_parent_self,
                'errors'  => ['parent_id' => $this->lang->error_parent_self],
            ], 422);
        }

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = $request->has('is_active');

        $term->update($validated);
        $term->saveTranslations($validated['translations']);

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_edit,
        ]);
    }

    public function destroy(Term $term): JsonResponse
    {
        if ($term->children()->exists()) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_has_children,
            ]);
        }

        $term->delete();

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }

    public function batchDelete(Request $request): JsonResponse
    {
        $ids = $request->input('selected', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => $this->lang->error_select_delete]);
        }

        $hasChildren = Term::whereIn('id', $ids)->whereHas('children')->exists();
        if ($hasChildren) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_batch_has_children,
            ]);
        }

        Term::whereIn('id', $ids)->delete();

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }

    /**
     * JSON：取得某分類下的詞彙（供 AJAX 下拉選單）
     */
    public function byTaxonomy(Taxonomy $taxonomy): JsonResponse
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
