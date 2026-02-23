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
     * Bypassing image processing for SVG
     *
     * @param AdapterInterface $subject
     * @param string|null $filename
     * @return void
     */
    public function beforeOpen(AdapterInterface $subject, ?string $filename): void
    {
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
        return array_unique(array_merge($result, $this->imageHelper->getAllowedExtensions()));
    }
}
