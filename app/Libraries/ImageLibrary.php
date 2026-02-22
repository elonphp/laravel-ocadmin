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

        // 無 gAMA chunk 表示乾淨，直接回傳
        $chunk = file_get_contents($path, false, null, 0, 4096);
        if (strpos($chunk, 'gAMA') === false) {
            return $src;
        }

        // 有 gAMA：重建像素，剝掉會導致跨瀏覽器色彩異常的 metadata
        $width = imagesx($src);
        $height = imagesy($src);
        $new = imagecreatetruecolor($width, $height);
        imagealphablending($new, false);
        imagesavealpha($new, true);
        imagecopy($new, $src, 0, 0, 0, 0, $width, $height);
        unset($src);

        return $new;
    }

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

            unset($this->image);
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
        unset($image_old);

        $this->width = $width;
        $this->height = $height;
    }

}