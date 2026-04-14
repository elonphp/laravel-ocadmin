<?php

namespace App\Portals\Ocadmin\Core\Controllers;

use App\Helpers\Classes\ImageHelper;
use App\Portals\Ocadmin\Core\Controllers\OcadminController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 圖片管理器（Image Browser Modal）
 *
 * 處理圖片瀏覽、上傳、刪除、建立資料夾。
 * 只操作檔案系統，不涉及資料庫。
 */
class ImageManagerController extends OcadminController
{
    protected function setLangFiles(): array
    {
        return ['common/imgmanager'];
    }

    /**
     * Modal 外殼（AJAX 載入，append 到 body）
     */
    public function index(Request $request): string
    {
        $data['lang'] = $this->lang;

        $data['config_file_max_size'] = ((int) config('settings.config_file_max_size', 2)) * 1024 * 1024;

        $data['target'] = $request->query('target', '');
        $data['thumb'] = $request->query('thumb', '');
        $data['ckeditor'] = $request->query('ckeditor', '');

        return view('ocadmin::common.imgmanager.index', $data)->render();
    }

    /**
     * 檔案瀏覽器內容（AJAX partial）
     */
    public function list(Request $request): string
    {
        $data['lang'] = $this->lang;

        $base = storage_path('app/public/image/');

        // 確保基底目錄存在
        if (!is_dir($base)) {
            @mkdir($base, 0777, true);
        }

        // 當前目錄
        if ($request->filled('directory')) {
            $directory = $base . urldecode($request->query('directory')) . '/';
        } else {
            $directory = $base;
        }

        $filterName = $request->query('filter_name', '');

        $page = (int) $request->query('page', 1);
        if ($page < 1) $page = 1;

        $allowed = ['.ico', '.jpg', '.jpeg', '.png', '.gif', '.webp', '.JPG', '.JPEG', '.PNG', '.GIF'];

        $data['directories'] = [];
        $data['images'] = [];

        // glob 掃描目錄 + 檔案
        $paths = array_merge(
            glob($directory . '*' . $filterName . '*', GLOB_ONLYDIR),
            glob($directory . '*' . $filterName . '*{' . implode(',', $allowed) . '}', GLOB_BRACE)
        );

        // 排序
        $sort = $request->query('sort', 'mtime');
        $order = $request->query('order', 'desc');

        // 目錄優先，再按欄位排序
        usort($paths, function ($a, $b) use ($sort, $order) {
            $aIsDir = is_dir($a);
            $bIsDir = is_dir($b);

            // 目錄永遠排在檔案前面
            if ($aIsDir !== $bIsDir) {
                return $aIsDir ? -1 : 1;
            }

            if ($sort === 'size' && !$aIsDir) {
                $cmp = filesize($a) - filesize($b);
            } elseif ($sort === 'mtime') {
                $cmp = filemtime($a) - filemtime($b);
            } else {
                $cmp = strcmp(basename($a), basename($b));
            }

            return $order === 'desc' ? -$cmp : $cmp;
        });

        // 分頁
        $total = count($paths);
        $limit = 16;
        $start = ($page - 1) * $limit;

        // 額外 URL 參數（target/thumb/ckeditor）
        $extraParams = '';
        if ($request->filled('target')) $extraParams .= '&target=' . $request->query('target');
        if ($request->filled('thumb')) $extraParams .= '&thumb=' . $request->query('thumb');
        if ($request->filled('ckeditor')) $extraParams .= '&ckeditor=' . $request->query('ckeditor');

        // 縮圖尺寸
        $thumbWidth = (int) config('settings.config_image_default_width', 100);
        $thumbHeight = (int) config('settings.config_image_default_height', 100);

        foreach (array_slice($paths, $start, $limit) as $path) {
            $path = str_replace('\\', '/', realpath($path));
            $name = basename($path);
            $relativePath = 'image/' . mb_substr($path, mb_strlen(str_replace('\\', '/', $base)));

            if (is_dir($path)) {
                $dirParam = urlencode(mb_substr($path, mb_strlen(str_replace('\\', '/', $base))));

                $data['directories'][] = [
                    'name'  => $name,
                    'path'  => $relativePath . '/',
                    'href'  => route('lang.ocadmin.common.image-manager.list') . '?directory=' . $dirParam . $extraParams,
                    'mtime' => date('Y-m-d H:i', filemtime($path)),
                ];
            }

            if (is_file($path) && in_array(substr($path, strrpos($path, '.')), $allowed)) {
                $filesize = filesize($path);

                $data['images'][] = [
                    'name'  => $name,
                    'path'  => $relativePath,
                    'href'  => url('/storage/' . $relativePath),
                    'thumb' => ImageHelper::resize($relativePath, $thumbWidth, $thumbHeight),
                    'size'  => $this->formatFileSize($filesize),
                    'mtime' => date('Y-m-d H:i', filemtime($path)),
                ];
            }
        }

        // 當前目錄值
        $data['directory'] = $request->filled('directory') ? urldecode($request->query('directory')) : '';
        $data['filter_name'] = $filterName;
        $data['sort'] = $sort;
        $data['order'] = $order;

        // Parent URL（上一層）
        $parentUrl = route('lang.ocadmin.common.image-manager.list') . '?';
        if ($request->filled('directory')) {
            $pos = strrpos($request->query('directory'), '/');
            if ($pos) {
                $parentUrl .= 'directory=' . urlencode(substr($request->query('directory'), 0, $pos));
            }
        }
        $parentUrl .= $extraParams;
        $data['parent'] = $parentUrl;

        // Refresh URL
        $refreshUrl = route('lang.ocadmin.common.image-manager.list') . '?';
        $refreshParts = [];
        if ($request->filled('directory')) $refreshParts[] = 'directory=' . urlencode($request->query('directory'));
        if ($request->filled('filter_name')) $refreshParts[] = 'filter_name=' . urlencode($request->query('filter_name'));
        $refreshParts[] = 'sort=' . $sort;
        $refreshParts[] = 'order=' . $order;
        $refreshUrl .= implode('&', $refreshParts) . $extraParams;
        $data['refresh'] = $refreshUrl;

        // 分頁 HTML
        $data['pagination'] = $this->buildPagination($total, $page, $limit, $request, $extraParams);

        return view('ocadmin::common.imgmanager.list', $data)->render();
    }

    /**
     * 上傳檔案
     */
    public function upload(Request $request): JsonResponse
    {
        $json = [];

        $base = storage_path('app/public/image/');

        // 目標目錄
        if ($request->filled('directory')) {
            $directory = $base . html_entity_decode($request->input('directory'), ENT_QUOTES, 'UTF-8') . '/';
        } else {
            $directory = $base;
        }

        // 安全檢查
        if (!is_dir($directory) || !str_starts_with(
            str_replace('\\', '/', realpath($directory)) . '/',
            str_replace('\\', '/', $base)
        )) {
            $json['error'] = $this->lang->error_directory;
        }

        if (!$json && $request->hasFile('file')) {
            $allowedExt = ['ico', 'jpg', 'jpeg', 'png', 'gif', 'webp'];
            $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/x-icon', 'image/vnd.microsoft.icon'];

            $files = $request->file('file');
            if (!is_array($files)) {
                $files = [$files];
            }

            foreach ($files as $file) {
                $filename = preg_replace('/[\/\\\\?%*:|"<>]/', '', basename(html_entity_decode($file->getClientOriginalName(), ENT_QUOTES, 'UTF-8')));

                if (mb_strlen($filename) < 4 || mb_strlen($filename) > 255) {
                    $json['error'] = $this->lang->error_filename;
                    break;
                }

                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExt)) {
                    $json['error'] = $this->lang->error_filetype;
                    break;
                }

                if (!in_array($file->getClientMimeType(), $allowedMime)) {
                    $json['error'] = $this->lang->error_mime;
                    break;
                }

                if ($file->getError() !== UPLOAD_ERR_OK) {
                    $json['error'] = $this->lang->error_upload;
                    break;
                }

                if (!$json) {
                    $file->move($directory, $filename);
                }
            }
        }

        if (!$json) {
            $json['success'] = $this->lang->text_uploaded;
        }

        return response()->json($json);
    }

    /**
     * 建立資料夾
     */
    public function folder(Request $request): JsonResponse
    {
        $json = [];

        $base = storage_path('app/public/image/catalog/');

        // 確保 catalog 目錄存在
        if (!is_dir($base)) {
            @mkdir($base, 0777, true);
        }

        // 目標目錄
        if ($request->filled('directory')) {
            $directory = $base . html_entity_decode($request->query('directory'), ENT_QUOTES, 'UTF-8') . '/';
        } else {
            $directory = $base;
        }

        // 安全檢查
        if (!is_dir($directory) || !str_starts_with(
            str_replace('\\', '/', realpath($directory)) . '/',
            str_replace('\\', '/', $base)
        )) {
            $json['error'] = $this->lang->error_directory;
        }

        $folder = preg_replace('/[\/\\\\?%*&:|"<>]/', '', basename(html_entity_decode($request->input('folder', ''), ENT_QUOTES, 'UTF-8')));

        if (mb_strlen($folder) < 3 || mb_strlen($folder) > 128) {
            $json['error'] = $this->lang->error_folder_name;
        }

        if (is_dir($directory . $folder)) {
            $json['error'] = $this->lang->error_directory;
        }

        if (!$json) {
            mkdir($directory . $folder, 0777);
            @touch($directory . $folder . '/index.html');

            $json['success'] = $this->lang->text_folder_created;
        }

        return response()->json($json);
    }

    /**
     * 刪除檔案/資料夾
     */
    public function delete(Request $request): JsonResponse
    {
        $json = [];

        $base = str_replace('\\', '/', storage_path('app/public/'));

        $paths = $request->input('path', []);

        // 驗證所有路徑
        foreach ($paths as $path) {
            $path = html_entity_decode($path, ENT_QUOTES, 'UTF-8');
            $realPath = str_replace('\\', '/', realpath($base . $path));

            if ($realPath === $base || !str_starts_with($realPath . '/', $base)) {
                $json['error'] = $this->lang->error_directory;
                break;
            }
        }

        if (!$json) {
            foreach ($paths as $path) {
                $fullPath = rtrim($base . html_entity_decode($path, ENT_QUOTES, 'UTF-8'), '/');

                $files = [];
                $queue = [$fullPath];

                // 遞迴收集所有子檔案/目錄
                while (count($queue) > 0) {
                    $next = array_shift($queue);

                    if (is_dir($next)) {
                        foreach (glob(trim($next, '/') . '/{*,.[!.]*,..?*}', GLOB_BRACE) as $file) {
                            $queue[] = $file;
                        }
                    }

                    $files[] = $next;
                }

                // 反向排序，先刪子項再刪父項
                rsort($files);

                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    } elseif (is_dir($file)) {
                        rmdir($file);
                    }
                }
            }

            $json['success'] = $this->lang->text_deleted;
        }

        return response()->json($json);
    }

    /**
     * 格式化檔案大小
     */
    protected function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }

    /**
     * 產生簡易分頁 HTML
     */
    protected function buildPagination(int $total, int $page, int $limit, Request $request, string $extraParams): string
    {
        $totalPages = ceil($total / $limit);

        if ($totalPages <= 1) {
            return '';
        }

        $baseUrl = route('lang.ocadmin.common.image-manager.list') . '?';
        $urlParts = [];
        if ($request->filled('directory')) $urlParts[] = 'directory=' . urlencode($request->query('directory'));
        if ($request->filled('filter_name')) $urlParts[] = 'filter_name=' . urlencode($request->query('filter_name'));
        if ($request->filled('sort')) $urlParts[] = 'sort=' . $request->query('sort');
        if ($request->filled('order')) $urlParts[] = 'order=' . $request->query('order');
        $baseUrl .= implode('&', $urlParts) . $extraParams;

        $html = '<ul class="pagination">';

        // Previous
        if ($page > 1) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . ($page - 1) . '">&laquo;</a></li>';
        }

        // Page numbers
        $start = max(1, $page - 2);
        $end = min($totalPages, $page + 2);

        for ($i = $start; $i <= $end; $i++) {
            $active = $i === $page ? ' active' : '';
            $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $baseUrl . '&page=' . $i . '">' . $i . '</a></li>';
        }

        // Next
        if ($page < $totalPages) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . ($page + 1) . '">&raquo;</a></li>';
        }

        $html .= '</ul>';

        return $html;
    }
}
