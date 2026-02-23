<?php

declare(strict_types=1);

namespace Aceextension\ImageOptmizer\Plugin\Image;

use Magento\Framework\Image\Adapter\AdapterInterface;
use Aceextension\ImageOptmizer\Helper\Data as ImageHelper;

class AdapterPlugin
{
    /**
     * @param ImageHelper $imageHelper
     */
    public function __construct(
        private readonly ImageHelper $imageHelper
    ) {}

    /**
     * Bypassing image processing for SVG and injecting modern format support
     *
     * @param AdapterInterface $subject
     * @param string|null $filename
     * @return void
     */
    public function beforeOpen(AdapterInterface $subject, ?string $filename): void
    {
        $this->injectModernFormatSupport($subject);

        if (!$filename) {
            return;
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        if ($this->imageHelper->isVector($extension)) {
            // Prevent GD/Imagick from trying to open SVG as a raster image
        }
    }

    /**
     * Bypass validation for allowed custom formats
     *
     * @param AdapterInterface $subject
     * @param callable $proceed
     * @param string $filePath
     * @return bool
     */
    public function aroundValidateUploadFile(AdapterInterface $subject, callable $proceed, string $filePath): bool
    {
        if (!$this->imageHelper->isEnabled()) {
            return (bool)$proceed($filePath);
        }

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        if (in_array(strtolower((string)$extension), $this->imageHelper->getAllowedExtensions(), true)) {
            return true;
        }

        try {
            return (bool)$proceed($filePath);
        } catch (\Exception $e) {
            // Fallback for some environments where getimagesize fails but the format is allowed
            $mimeType = @mime_content_type($filePath);

            if (
                in_array(strtolower((string)$extension), ['webp', 'avif', 'svg'], true) ||
                ($mimeType && (str_contains($mimeType, 'webp') || str_contains($mimeType, 'avif') || str_contains($mimeType, 'svg')))
            ) {
                return true;
            }
            throw $e;
        }
    }

    /**
     * Add modern formats to supported formats
     *
     * @param AdapterInterface $subject
     * @param array $result
     * @return array
     */
    public function afterGetSupportedFormats(AdapterInterface $subject, array $result): array
    {
        $this->injectModernFormatSupport($subject);
        return array_unique(array_merge($result, $this->imageHelper->getAllowedExtensions()));
    }

    /**
     * Inject WebP/AVIF callbacks into GD2/Imagick adapters
     *
     * @param AdapterInterface $subject
     * @return void
     */
    private function injectModernFormatSupport(AdapterInterface $subject): void
    {
        try {
            if ($subject instanceof \Magento\Framework\Image\Adapter\Gd2) {
                $reflection = new \ReflectionClass(\Magento\Framework\Image\Adapter\Gd2::class);
                if ($reflection->hasProperty('_callbacks')) {
                    $property = $reflection->getProperty('_callbacks');
                    $property->setAccessible(true);
                    $callbacks = $property->getValue();

                    if (defined('IMAGETYPE_WEBP') && !isset($callbacks[IMAGETYPE_WEBP])) {
                        $callbacks[IMAGETYPE_WEBP] = ['output' => 'imagewebp', 'create' => 'imagecreatefromwebp'];
                    }
                    if (defined('IMAGETYPE_AVIF') && !isset($callbacks[IMAGETYPE_AVIF])) {
                        $callbacks[IMAGETYPE_AVIF] = ['output' => 'imageavif', 'create' => 'imagecreatefromavif'];
                    }

                    $property->setValue(null, $callbacks);
                }
            } elseif ($subject instanceof \Magento\Framework\Image\Adapter\ImageMagick) {
                // ImageMagick usually supports them natively if installed, 
                // but we can ensure they are in supported list via getSupportedFormats
            }
        } catch (\Exception $e) {
            // Silence is golden
        }
    }
}
