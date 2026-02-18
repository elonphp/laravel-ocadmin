<?php

/**
 * 產生帶有檔案修改時間版本號的 asset URL。
 * 檔案一改，時間戳就變，瀏覽器視為新 URL，自動下載新版。
 */
function versioned_asset(string $path): string
{
    $fullPath = public_path($path);
    $version = file_exists($fullPath) ? filemtime($fullPath) : '0';

    return asset($path) . '?v=' . $version;
}
