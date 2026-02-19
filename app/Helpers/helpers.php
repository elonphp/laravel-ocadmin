<?php

if (! function_exists('versioned_asset')) {
    /**
     * Generate a versioned asset URL using file modification time.
     * Appends ?v={filemtime} to bust browser cache when the file changes.
     */
    function versioned_asset(string $path): string
    {
        $fullPath = public_path($path);
        $version = file_exists($fullPath) ? filemtime($fullPath) : '0';

        return asset($path) . '?v=' . $version;
    }
}
