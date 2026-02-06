<?php

namespace App\Portals\Ocadmin\Core\Controllers\Config;

use App\Helpers\Classes\LocaleHelper;
use App\Models\Config\Taxonomy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;

class TaxonomyController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['common', 'config/taxonomy'];
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
                'href' => route('lang.ocadmin.config.taxonomy.index'),
            ],
        ];
    }

    public function index(Request $request): View
    {
        $query = Taxonomy::with('translations');

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
        $data['taxonomies'] = $query->withCount('terms')->paginate(20)->withQueryString();

        return view('ocadmin::config.taxonomy.index', $data);
    }

    public function create(): View
    {
        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['taxonomy'] = new Taxonomy();

        return view('ocadmin::config.taxonomy.form', $data);
    }

    public function store(Request $request): JsonResponse
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

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_add,
            'replace_url' => route('lang.ocadmin.config.taxonomy.edit', $taxonomy),
            'form_action' => route('lang.ocadmin.config.taxonomy.update', $taxonomy),
        ]);
    }

    public function edit(Taxonomy $taxonomy): View
    {
        $taxonomy->load('translations');

        $data['lang'] = $this->lang;
        $data['breadcrumbs'] = $this->breadcrumbs;
        $data['taxonomy'] = $taxonomy;

        return view('ocadmin::config.taxonomy.form', $data);
    }

    public function update(Request $request, Taxonomy $taxonomy): JsonResponse
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

        return response()->json([
            'success' => true,
            'message' => $this->lang->text_success_edit,
        ]);
    }

    public function destroy(Taxonomy $taxonomy): JsonResponse
    {
        if ($taxonomy->terms()->exists()) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_has_terms,
            ]);
        }

        $taxonomy->delete();

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }

    public function batchDelete(Request $request): JsonResponse
    {
        $ids = $request->input('selected', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => $this->lang->error_select_delete]);
        }

        $hasTerms = Taxonomy::whereIn('id', $ids)->whereHas('terms')->exists();
        if ($hasTerms) {
            return response()->json([
                'success' => false,
                'message' => $this->lang->error_batch_has_terms,
            ]);
        }

        Taxonomy::whereIn('id', $ids)->delete();

        return response()->json(['success' => true, 'message' => $this->lang->text_success_delete]);
    }
}
