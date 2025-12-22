<?php

namespace Elonphp\LaravelOcadminModules\Modules\SystemSetting;

class SystemSettingService
{
    /**
     * Setting model class.
     */
    protected string $model;

    /**
     * Setting type enum class.
     */
    protected string $typeEnum;

    public function __construct()
    {
        $this->model = config('ocadmin.models.setting', Setting::class);
        $this->typeEnum = config('ocadmin.enums.setting_type', SettingType::class);
    }

    /**
     * Get all setting types for dropdown.
     */
    public function getTypes(): array
    {
        if (!class_exists($this->typeEnum)) {
            return [];
        }

        return $this->typeEnum::cases();
    }

    /**
     * Check if a setting code exists.
     */
    public function exists(string $code, string $locale = '', ?int $excludeId = null): bool
    {
        $query = $this->model::where('code', $code)
            ->where('locale', $locale);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Create a new setting.
     */
    public function create(array $data): mixed
    {
        $data['locale'] = $data['locale'] ?? '';

        return $this->model::create($data);
    }

    /**
     * Update a setting.
     */
    public function update(int $id, array $data): bool
    {
        $setting = $this->model::findOrFail($id);

        $data['locale'] = $data['locale'] ?? '';

        return $setting->update($data);
    }

    /**
     * Delete a setting.
     */
    public function delete(int $id): bool
    {
        $setting = $this->model::findOrFail($id);

        return $setting->delete();
    }

    /**
     * Delete multiple settings.
     */
    public function deleteMultiple(array $ids): int
    {
        return $this->model::whereIn('id', $ids)->delete();
    }

    /**
     * Find a setting by ID.
     */
    public function find(int $id): mixed
    {
        return $this->model::findOrFail($id);
    }

    /**
     * Get a new model instance with defaults.
     */
    public function withDefaults(): mixed
    {
        return new $this->model([
            'code' => '',
            'locale' => '',
            'group' => '',
            'content' => '',
            'type' => 'text',
            'note' => '',
        ]);
    }
}
