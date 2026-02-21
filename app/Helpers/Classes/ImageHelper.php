<?php

/**
 * 圖片縮圖快取
 *
 * 輸入相對路徑（如 'image/catalog/SZ25.jpg'），
 * 產生指定尺寸的快取縮圖，回傳完整 URL。
 *
 * 檔案基底：storage/app/public/
 * 快取位置：storage/app/public/cache/
 */

namespace App\Helpers\Classes;

use App\Libraries\ImageLibrary;

class ImageHelper
{
    /**
     * Resize
     *
     * @param string $filename 相對路徑，如 'image/catalog/SZ25.jpg'
     * @param int    $width
     * @param int    $height
     * @param bool   $force 是否強制指定尺寸（false 時保持比例）
     *
     * @return string 完整 URL 或空字串
     */
    public static function resize(string $filename, int $width, int $height, bool $force = false): string
    {
        if (empty($filename)) {
            return '';
        }

        $storagePath = storage_path('app/public/');
        $filePath = $storagePath . $filename;

        if (!is_file($filePath)) {
            return '';
        }

        // 安全檢查：防止路徑遍歷
        $realPath = realpath($filePath);
        $realStorage = realpath($storagePath);

        if (!$realPath || !$realStorage) {
            return '';
        }

        if (!str_starts_with(
            str_replace('\\', '/', $realPath),
            str_replace('\\', '/', $realStorage)
        )) {
            return '';
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        // 計算縮圖尺寸
        if (is_file($filePath)) {
            [$widthOrig, $heightOrig, $imageType] = getimagesize($filePath);

            if (!$force && $widthOrig && $heightOrig) {
                if ($widthOrig > $heightOrig) {
                    $height = intval($width * ($heightOrig / $widthOrig));
                } else {
                    $width = intval($height * ($widthOrig / $heightOrig));
                }
            }
        }

        // 快取路徑：cache/{原路徑去副檔名}-{W}x{H}.{ext}
        $imageNew = 'cache/' . mb_substr($filename, 0, mb_strrpos($filename, '.')) . '-' . $width . 'x' . $height . '.' . $extension;

        // 快取存在且較新，直接回傳
        if (is_file($storagePath . $imageNew) && filemtime($filePath) <= filemtime($storagePath . $imageNew)) {
            return url('/storage/' . $imageNew);
        }

        // 驗證圖片類型
        [$widthOrig, $heightOrig, $imageType] = getimagesize($filePath);

        if (!in_array($imageType, [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_WEBP])) {
            return url('/storage/' . $filename);
        }

        // 建立快取目錄
        $path = '';
        $directories = explode('/', dirname($imageNew));

        foreach ($directories as $directory) {
            $path = $path ? $path . '/' . $directory : $directory;

            if (!is_dir($storagePath . $path)) {
                @mkdir($storagePath . $path, 0777);
            }
        }

        // 產生縮圖
        if ($widthOrig != $width || $heightOrig != $height) {
            $image = new ImageLibrary($filePath);
            $image->resize($width, $height);
            $image->save($storagePath . $imageNew);
        } else {
            copy($filePath, $storagePath . $imageNew);
        }

        return url('/storage/' . $imageNew);
    }
}
