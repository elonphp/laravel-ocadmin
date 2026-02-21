<?php

/**
 * Opencart\System\Library\Image
 */
namespace App\Libraries;

class ImageLibrary
{
    /**
     * @var string
     */
    private string $file;
    /**
     * @var false|\GdImage|resource
     */
    private $image;
    /**
     * @var int
     */
    private int $width;
    /**
     * @var int
     */
    private int $height;
    /**
     * @var string
     */
    private string $bits;
    /**
     * @var string
     */
    private string $mime;

    /**
     * Constructor
     *
     * @param string $file
     */
    public function __construct(string $file) {
        if (!extension_loaded('gd')) {
            exit('Error: PHP GD is not installed!');
        }

        if (is_file($file)) {
            $this->file = $file;

            $info = getimagesize($file);

            $this->width = $info[0];
            $this->height = $info[1];
            $this->bits = $info['bits'] ?? '';
            $this->mime = $info['mime'] ?? '';

            if ($this->mime == 'image/gif') {
                $this->image = imagecreatefromgif($file);
            } elseif ($this->mime == 'image/png') {
                $this->image = $this->cleanImage($file);
                imageinterlace($this->image, false);

                // $this->image = imagecreatefrompng($file);
                // imageinterlace($this->image, false);
            } elseif ($this->mime == 'image/jpeg') {
                $this->image = imagecreatefromjpeg($file);
            } elseif ($this->mime == 'image/webp') {
                $this->image = imagecreatefromwebp($file);
            }
        } else {
            throw new \Exception('Error: Could not load image ' . $file . '!');
        }
    }

    // 檢查 png
    protected function cleanImage(string $path): ?\GdImage
    {
        $defaultImagePath = storage_path('app/public/image/no_image.png');

        if (!file_exists($path) || !@getimagesize($path)) {
            return imagecreatefrompng($defaultImagePath); // 預設圖
        }

        $info = getimagesize($path);
        $mime = $info['mime'] ?? '';

        if ($mime !== 'image/png') {
            return imagecreatefromstring(file_get_contents($path)); // 非 PNG
        }

        $src = @imagecreatefrompng($path);
        if (!$src || !($src instanceof \GdImage)) {
            return imagecreatefrompng($defaultImagePath);
        }

        if ($this->isPngClean($path)) {
            return $src; // 已乾淨
        }

        $width = imagesx($src);
        $height = imagesy($src);
        $new = imagecreatetruecolor($width, $height);
        imagealphablending($new, false);
        imagesavealpha($new, true);
        imagecopy($new, $src, 0, 0, 0, 0, $width, $height);
        imagedestroy($src);

        return $new;
    }

    // ChatGPT: 檢查是否為 valid PNG + 無 gamma 資訊
    protected function isPngClean(string $path): bool
    {
        // 先確保檔案存在且是 PNG
        if (!file_exists($path) || !@getimagesize($path)) {
            return false;
        }

        $info = getimagesize($path);
        $mime = $info['mime'] ?? '';
        if ($mime !== 'image/png') {
            return false;
        }

        // 讀取檔案內容前 4KB 來檢查有沒有 gAMA (gamma) 區段
        $chunk = file_get_contents($path, false, null, 0, 4096);
        if (strpos($chunk, 'gAMA') !== false) {
            return false; // 有 gamma chunk，表示非乾淨 PNG
        }

        // 通常還會加：沒有太多 metadata、大小不異常、透明資訊正常
        return true;
    }

    // 比較重建前後是否相同 改天把下面的用在上傳流程
    // 如果你真的想保證圖片是「重新轉存不會變化」，可以比較一次壓縮前後是否一樣大小
    // 會比較慢，不建議大量使用在 HTTP 請求中。適合用在上傳流程後處理。
    // protected function isPngClean(string $path): bool
    // {
    //     $tmp = $path . '.check.png';

    //     $src = @imagecreatefrompng($path);
    //     if (!$src || !($src instanceof \GdImage)) {
    //         return false;
    //     }

    //     $width = imagesx($src);
    //     $height = imagesy($src);

    //     $new = imagecreatetruecolor($width, $height);
    //     imagealphablending($new, false);
    //     imagesavealpha($new, true);
    //     imagecopy($new, $src, 0, 0, 0, 0, $width, $height);

    //     imagepng($new, $tmp, 9);
    //     imagedestroy($new);
    //     imagedestroy($src);

    //     // 比較檔案大小差異是否極小（可接受誤差 1%）
    //     $origSize = filesize($path);
    //     $newSize = filesize($tmp);

    //     unlink($tmp); // 清掉測試檔

    //     return abs($origSize - $newSize) < ($origSize * 0.01);
    // }

    /**
     * getFile
     *
     * @return string
     */
    public function getFile(): string {
        return $this->file;
    }

    /**
     * getImage
     *
     * @return \GdImage|resource|null
     */
    public function getImage() {
        return $this->image ?: null;
    }

    /**
     * getWidth
     *
     * @return int
     */
    public function getWidth(): int {
        return $this->width;
    }

    /**
     * getHeight
     *
     * @return int
     */
    public function getHeight(): int {
        return $this->height;
    }

    /**
     * getBits
     *
     * @return string
     */
    public function getBits(): string {
        return $this->bits;
    }

    /**
     * getMime
     *
     * @return string
     */
    public function getMime(): string {
        return $this->mime;
    }

    /**
     * Save
     *
     * @param string $file
     * @param int    $quality
     *
     * @return void
     */
    public function save(string $file, int $quality = 90): void {
        $info = pathinfo($file);
        $extension = strtolower($info['extension']);

        if (is_object($this->image) || is_resource($this->image)) {
            // 清除 alpha blending 設定，確保儲存透明背景
            if (in_array($extension, ['png', 'webp'])) {
                imagealphablending($this->image, false);
                imagesavealpha($this->image, true);
            }

            if ($extension == 'jpeg' || $extension == 'jpg') {
                imagejpeg($this->image, $file, $quality);
            } elseif ($extension == 'png') {
                // PNG 預設品質是 6（0 最快 / 9 最佳）
                imagepng($this->image, $file, 9); // 強化壓縮
            } elseif ($extension == 'gif') {
                imagegif($this->image, $file);
            } elseif ($extension == 'webp') {
                imagewebp($this->image, $file, $quality); // webp 也支援壓縮品質
            }

            imagedestroy($this->image);
        }
    }

    /**
     * Resize
     *
     * @param int    $width
     * @param int    $height
     * @param string $default
     *
     * @return void
     */
    public function resize(int $width = 0, int $height = 0, string $default = ''): void {
        if (!$this->width || !$this->height) {
            return;
        }

        $xpos = 0;
        $ypos = 0;
        $scale = 1;

        $scale_w = $width / $this->width;
        $scale_h = $height / $this->height;

        if ($default == 'w') {
            $scale = $scale_w;
        } elseif ($default == 'h') {
            $scale = $scale_h;
        } else {
            $scale = min($scale_w, $scale_h);
        }

        if ($scale == 1 && $scale_h == $scale_w && ($this->mime != 'image/png' || $this->mime != 'image/webp')) {
            return;
        }

        $new_width = (int)($this->width * $scale);
        $new_height = (int)($this->height * $scale);
        $xpos = (int)(($width - $new_width) / 2);
        $ypos = (int)(($height - $new_height) / 2);

        $image_old = $this->image;

        /** @var GdImage $new */
        $this->image = imagecreatetruecolor($width, $height);

        if ($this->mime == 'image/png') {
            imagealphablending($this->image, false);
            imagesavealpha($this->image, true);

            $background = imagecolorallocatealpha($this->image, 255, 255, 255, 127);

            imagecolortransparent($this->image, $background);
        } elseif ($this->mime == 'image/webp') {
            imagealphablending($this->image, false);
            imagesavealpha($this->image, true);

            $background = imagecolorallocatealpha($this->image, 255, 255, 255, 127);

            imagecolortransparent($this->image, $background);
        } else {
            $background = imagecolorallocate($this->image, 255, 255, 255);
        }

        imagefilledrectangle($this->image, 0, 0, $width, $height, $background);

        imagecopyresampled($this->image, $image_old, $xpos, $ypos, 0, 0, $new_width, $new_height, $this->width, $this->height);
        imagedestroy($image_old);

        $this->width = $width;
        $this->height = $height;
    }

    /**
     * Watermark
     *
     * @param self   $watermark
     * @param string $position
     *
     * @return void
     */
    public function watermark(self $watermark, string $position = 'bottomright'): void {
        switch ($position) {
            case 'topleft':
                $watermark_pos_x = 0;
                $watermark_pos_y = 0;
                break;
            case 'topcenter':
                $watermark_pos_x = (int)(($this->width - $watermark->getWidth()) / 2);
                $watermark_pos_y = 0;
                break;
            case 'topright':
                $watermark_pos_x = ($this->width - $watermark->getWidth());
                $watermark_pos_y = 0;
                break;
            case 'middleleft':
                $watermark_pos_x = 0;
                $watermark_pos_y = (int)(($this->height - $watermark->getHeight()) / 2);
                break;
            case 'middlecenter':
                $watermark_pos_x = (int)(($this->width - $watermark->getWidth()) / 2);
                $watermark_pos_y = (int)(($this->height - $watermark->getHeight()) / 2);
                break;
            case 'middleright':
                $watermark_pos_x = ($this->width - $watermark->getWidth());
                $watermark_pos_y = (int)(($this->height - $watermark->getHeight()) / 2);
                break;
            case 'bottomleft':
                $watermark_pos_x = 0;
                $watermark_pos_y = ($this->height - $watermark->getHeight());
                break;
            case 'bottomcenter':
                $watermark_pos_x = (int)(($this->width - $watermark->getWidth()) / 2);
                $watermark_pos_y = ($this->height - $watermark->getHeight());
                break;
            case 'bottomright':
                $watermark_pos_x = ($this->width - $watermark->getWidth());
                $watermark_pos_y = ($this->height - $watermark->getHeight());
                break;
        }

        imagealphablending($this->image, true);
        imagesavealpha($this->image, true);
        imagecopy($this->image, $watermark->getImage(), $watermark_pos_x, $watermark_pos_y, 0, 0, $watermark->getWidth(), $watermark->getHeight());

        imagedestroy($watermark->getImage());
    }

    /**
     * Crop
     *
     * @param int $top_x
     * @param int $top_y
     * @param int $bottom_x
     * @param int $bottom_y
     *
     * @return void
     */
    public function crop(int $top_x, int $top_y, int $bottom_x, int $bottom_y): void {
        $image_old = $this->image;
        $this->image = imagecreatetruecolor($bottom_x - $top_x, $bottom_y - $top_y);

        imagecopy($this->image, $image_old, 0, 0, $top_x, $top_y, $this->width, $this->height);
        imagedestroy($image_old);

        $this->width = $bottom_x - $top_x;
        $this->height = $bottom_y - $top_y;
    }

    /**
     * Rotate
     *
     * @param int    $degree
     * @param string $color
     *
     * @return void
     */
    public function rotate(int $degree, string $color = 'FFFFFF'): void {
        $rgb = $this->html2rgb($color);

        $this->image = imagerotate($this->image, $degree, imagecolorallocate($this->image, $rgb[0], $rgb[1], $rgb[2]));

        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);
    }

    /**
     * Filter
     *
     * @return void
     */
    private function filter(): void {
        $args = func_get_args();

        imagefilter(...$args);
    }

    /**
     * Text
     *
     * @param string $text
     * @param int    $x
     * @param int    $y
     * @param int    $size
     * @param string $color
     *
     * @return void
     */
    private function text(string $text, int $x = 0, int $y = 0, int $size = 5, string $color = '000000'): void {
        $rgb = $this->html2rgb($color);

        imagestring($this->image, $size, $x, $y, $text, imagecolorallocate($this->image, $rgb[0], $rgb[1], $rgb[2]));
    }

    /**
     * Merge
     *
     * @param self $merge
     * @param int  $x
     * @param int  $y
     * @param int  $opacity
     *
     * @return void
     */
    private function merge(self $merge, int $x = 0, int $y = 0, int $opacity = 100): void {
        imagecopymerge($this->image, $merge->getImage(), $x, $y, 0, 0, $merge->getWidth(), $merge->getHeight(), $opacity);
    }

    /**
     * HTML2RGB
     *
     * @param string $color
     *
     * @return array
     */
    private function html2rgb(string $color): array {
        if ($color[0] == '#') {
            $color = substr($color, 1);
        }

        if (strlen($color) == 6) {
            [
                $r,
                $g,
                $b
            ] = [
                $color[0] . $color[1],
                $color[2] . $color[3],
                $color[4] . $color[5]
            ];
        } elseif (strlen($color) == 3) {
            [
                $r,
                $g,
                $b
            ] = [
                $color[0] . $color[0],
                $color[1] . $color[1],
                $color[2] . $color[2]
            ];
        } else {
            return [];
        }

        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);

        return [
            $r,
            $g,
            $b
        ];
    }
}