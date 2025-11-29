<?php

namespace App\Traits;

use App\Models\System\Database\MetaKey;

trait HasMetas
{
    /**
     * 取得單一 meta 值
     */
    public function getMeta(string $key, mixed $default = null): mixed
    {
        $keyId = MetaKey::getId($key);
        if (!$keyId) {
            return $default;
        }

        $meta = $this->metas()->where('key_id', $keyId)->first();
        return $meta ? $this->castMetaValue($meta->value) : $default;
    }

    /**
     * 設定單一 meta 值
     */
    public function setMeta(string $key, mixed $value): void
    {
        $metaKey = MetaKey::firstOrCreate(
            ['name' => $key],
            ['table_name' => $this->getTable()]
        );

        MetaKey::clearCache();

        $this->metas()->updateOrCreate(
            ['key_id' => $metaKey->id],
            ['value' => $this->serializeMetaValue($value)]
        );
    }

    /**
     * 批次設定多個 meta
     */
    public function setMetas(array $metas): void
    {
        foreach ($metas as $key => $value) {
            if ($value !== null) {
                $this->setMeta($key, $value);
            }
        }
    }

    /**
     * 刪除 meta
     */
    public function deleteMeta(string $key): bool
    {
        $keyId = MetaKey::getId($key);
        if (!$keyId) {
            return false;
        }

        return $this->metas()->where('key_id', $keyId)->delete() > 0;
    }

    /**
     * 取得所有 meta（key-value 陣列）
     */
    public function getAllMetas(): array
    {
        $metaTable = $this->metas()->getRelated()->getTable();

        return $this->metas()
            ->join('meta_keys', 'meta_keys.id', '=', "{$metaTable}.key_id")
            ->pluck("{$metaTable}.value", 'meta_keys.name')
            ->map(fn($value) => $this->castMetaValue($value))
            ->toArray();
    }

    /**
     * 檢查 meta 是否存在
     */
    public function hasMeta(string $key): bool
    {
        $keyId = MetaKey::getId($key);
        if (!$keyId) {
            return false;
        }

        return $this->metas()->where('key_id', $keyId)->exists();
    }

    /**
     * 序列化值（陣列/物件轉 JSON）
     */
    protected function serializeMetaValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }

    /**
     * 反序列化值（嘗試 JSON 解碼）
     */
    protected function castMetaValue(?string $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $value;
    }

    /**
     * Scope: 依 meta 值篩選
     */
    public function scopeWhereMeta($query, string $key, mixed $value)
    {
        $keyId = MetaKey::getId($key);
        if (!$keyId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('metas', function ($q) use ($keyId, $value) {
            $q->where('key_id', $keyId)->where('value', $value);
        });
    }
}
