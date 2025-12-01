<?php

/**
 * TranslationLibrary - 簡化 Laravel 多語言使用
 *
 * 允許在視圖中使用 $lang->key 語法，取代冗長的 __('path.key')
 * 支援 Cascading Override：後載入的語言檔覆蓋前面的同名 key
 */

namespace App\Libraries;

use Illuminate\Support\Facades\Lang;

class TranslationLibrary
{
    /**
     * 取得翻譯資料物件
     *
     * @param string|array $paths 語言檔路徑，可以是單一路徑或陣列
     * @return TranslationData
     */
    public function getLang(string|array $paths): TranslationData
    {
        if (!is_array($paths)) {
            $paths = [$paths];
        }

        return $this->getTranslations($paths);
    }

    /**
     * 從多個語言檔載入翻譯並合併
     *
     * @param array $paths 語言檔路徑陣列
     * @return TranslationData
     */
    public function getTranslations(array $paths): TranslationData
    {
        $locale = app()->getLocale();
        $fallbackLocale = config('app.fallback_locale');

        // 載入當前語言
        $translations = $this->loadTranslations($paths, $locale);

        // 如果當前語言與 fallback 不同，先載入 fallback 再覆蓋
        if ($locale !== $fallbackLocale) {
            $fallbackTranslations = $this->loadTranslations($paths, $fallbackLocale);
            $translations = array_replace_recursive($fallbackTranslations, $translations);
        }

        return new TranslationData($translations);
    }

    /**
     * 從指定語言檔載入翻譯
     *
     * @param array $paths 語言檔路徑陣列
     * @param string $locale 語言代碼
     * @return array
     */
    protected function loadTranslations(array $paths, string $locale): array
    {
        $data = [];

        foreach ($paths as $path) {
            $arr = Lang::get($path, [], $locale);

            if (is_array($arr)) {
                // 後載入的覆蓋前面的（Cascading Override）
                $data = array_replace_recursive($data, $arr);
            }
        }

        return $data;
    }
}

/**
 * TranslationData - 翻譯資料容器
 *
 * 透過 __get() 魔術方法支援 $lang->key 語法
 */
class TranslationData
{
    protected array $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * 魔術方法：取得翻譯值
     * 找不到時返回 key 本身，方便除錯
     */
    public function __get(string $key): string
    {
        return $this->data[$key] ?? $key;
    }

    /**
     * 取得翻譯值（方法形式）
     */
    public function trans(string $key): string
    {
        return $this->__get($key);
    }

    /**
     * 動態設定翻譯值（用於控制器覆蓋）
     */
    public function set(string $key, string $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * 檢查 key 是否存在
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * 取得所有翻譯資料
     */
    public function all(): array
    {
        return $this->data;
    }
}
