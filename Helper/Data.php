<?php

declare(strict_types=1);

/**
 * Aceextension_ImageOptmizer
 *
 * @category    Aceextension
 * @package     Aceextension_ImageOptmizer
 * @author      Aceextension
 */

namespace Aceextension\ImageOptmizer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http as HttpRequest;

class Data extends AbstractHelper
{
    /**
     * @param Context $context
     * @param HttpRequest $request
     */
    public function __construct(
        Context $context,
        private readonly HttpRequest $request
    ) {
        parent::__construct($context);
    }

    /**
     * Check if module is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag('ace_image_optimizer/general/enabled');
    }

    /**
     * Check if catalog WebP replacement is enabled
     *
     * @return bool
     */
    public function isWebpReplacementEnabled(): bool
    {
        // Only works if the whole module is enabled
        return $this->isEnabled() && $this->scopeConfig->isSetFlag('ace_image_optimizer/general/replace_catalog_images_with_webp');
    }

    /**
     * Check if debug mode is enabled
     *
     * @return bool
     */
    public function isDebugEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag('ace_image_optimizer/general/debug');
    }

    /**
     * Custom logging
     *
     * @param string $message
     * @return void
     */
    public function log(string $message): void
    {
        if (!$this->isDebugEnabled()) {
            return;
        }

        $this->_logger->info('[Aceextension_ImageOptmizer] ' . $message);
    }

    /**
     * @return string[]
     */
    public function getAllowedExtensions(): array
    {
        return ['svg', 'webp', 'avif'];
    }

    /**
     * Check if extension is a vector
     *
     * @param string $extension
     * @return bool
     */
    public function isVector(string $extension): bool
    {
        return strtolower($extension) === 'svg';
    }

    /**
     * Convert image to WebP
     *
     * @param string $sourcePath
     * @param string $destinationPath
     * @param int $quality
     * @return bool
     */
    public function convertToWebp(string $sourcePath, string $destinationPath, int $quality = 80): bool
    {
        if (!file_exists($sourcePath)) {
            return false;
        }

        $info = getimagesize($sourcePath);
        if (!$info) {
            return false;
        }

        $image = match ($info['mime']) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png' => (function () use ($sourcePath) {
                $img = imagecreatefrompng($sourcePath);
                if ($img === false) {
                    return false;
                }
                imagepalettetotruecolor($img);
                imagealphablending($img, true);
                imagesavealpha($img, true);
                return $img;
            })(),
            default => null
        };

        if (!$image) {
            return false;
        }

        $result = imagewebp($image, $destinationPath, $quality);
        imagedestroy($image);
        return $result;
    }

    /**
     * Convert image to AVIF (requires PHP 8.1+ and GD with AVIF support)
     *
     * @param string $sourcePath
     * @param string $destinationPath
     * @param int $quality
     * @return bool
     */
    public function convertToAvif(string $sourcePath, string $destinationPath, int $quality = 30): bool
    {
        if (!function_exists('imageavif') || !file_exists($sourcePath)) {
            return false;
        }

        $info = getimagesize($sourcePath);
        if (!$info) {
            return false;
        }

        $image = match ($info['mime']) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png' => (function () use ($sourcePath) {
                $img = imagecreatefrompng($sourcePath);
                if ($img === false) {
                    return false;
                }
                imagepalettetotruecolor($img);
                return $img;
            })(),
            default => null
        };

        if (!$image) {
            return false;
        }

        $result = imageavif($image, $destinationPath, $quality);
        imagedestroy($image);
        return $result;
    }

    /**
     * Check if we are in materialization mode (pub/get.php)
     *
     * @return bool
     */
    public function isMaterializationMode(): bool
    {
        $requestUri = $this->request->getRequestUri();
        return str_contains($requestUri, 'get.php');
    }
}
