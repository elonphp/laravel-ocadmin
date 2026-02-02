<?php

namespace App\Libraries;

use Illuminate\Support\Facades\Lang;

/**
 * TranslationLibrary
 *
 * OpenCart 風格的多語服務，支援層疊覆蓋
 *
 * 用法：
 *   $lang = app(TranslationLibrary::class)->load(['common', 'admin/order']);
 *   $lang->button_save          // 取值
 *   $lang->button_save = '儲存' // 覆寫
 *   $lang->get('button.save')   // 巢狀 key
 */
class TranslationLibrary
{
    /**
     * 載入多個語言檔並合併
     *
     * @param string|array $groups 語言檔路徑，後者覆蓋前者
     * @param string|null $locale 語系，預設使用當前語系
     * @return TranslationBag
     */
    public function load(string|array $groups, ?string $locale = null): TranslationBag
    {
        $groups = is_array($groups) ? $groups : [$groups];
        $locale = $locale ?? app()->getLocale();
        $fallbackLocale = config('app.fallback_locale');

        // 先載入 fallback，再載入當前語系覆蓋
        $translations = [];

        if ($locale !== $fallbackLocale) {
            $translations = $this->loadGroups($groups, $fallbackLocale);
        }

        $currentTranslations = $this->loadGroups($groups, $locale);
        $translations = array_replace_recursive($translations, $currentTranslations);

        return new TranslationBag($translations);
    }

    /**
     * 載入多個語言檔群組
     */
    protected function loadGroups(array $groups, string $locale): array
    {
        $data = [];

        foreach ($groups as $group) {
            $groupData = Lang::get($group, [], $locale);

            if (is_array($groupData)) {
                $flattened = $this->flattenArray($groupData);
                $data = array_replace($data, $flattened);
            }
        }

        return $data;
    }

    /**
     * 攤平巢狀陣列
     *
     * ['button' => ['save' => '儲存']]
     * 轉為
     * ['button_save' => '儲存']
     */
    protected function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix ? "{$prefix}_{$key}" : $key;

            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }
}

/**
 * TranslationBag
 *
 * 翻譯資料容器，支援屬性存取
 */
class TranslationBag
{
    protected array $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * 魔術方法：取值
     * $lang->button_save
     */
    public function __get(string $key): string
    {
        return $this->data[$key] ?? $key;
    }

    /**
     * 魔術方法：設值（覆寫）
     * $lang->button_save = '儲存'
     */
    public function __set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * 魔術方法：檢查是否存在
     */
    public function __isset(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * 取值
     */
    public function get(string $key, ?string $default = null): string
    {
        return $this->data[$key] ?? $default ?? $key;
    }

    /**
     * 設值
     */
    public function set(string $key, mixed $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * 批次設值
     */
    public function merge(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * 取得所有翻譯（用於傳給前端）
     */
    public function all(): array
    {
        return $this->data;
    }
}
