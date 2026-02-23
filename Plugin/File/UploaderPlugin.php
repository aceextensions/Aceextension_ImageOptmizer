<?php

declare(strict_types=1);

namespace Aceextension\ImageOptmizer\Plugin\File;

use Magento\MediaStorage\Model\File\Uploader;
use Aceextension\ImageOptmizer\Helper\Data as ImageHelper;

class UploaderPlugin
{
    /**
     * @param ImageHelper $imageHelper
     */
    public function __construct(
        private readonly ImageHelper $imageHelper
    ) {}

    /**
     * Add modern image formats to allowed extension list
     *
     * @param Uploader $subject
     * @param array|string $extensions
     * @return array
     */
    public function beforeSetAllowedExtensions(Uploader $subject, $extensions = []): array
    {
        if (!$this->imageHelper->isEnabled()) {
            return [$extensions];
        }

        if (is_array($extensions)) {
            $extensions = array_merge($extensions, $this->imageHelper->getAllowedExtensions());
            $extensions = array_unique($extensions);
        } elseif (is_string($extensions)) {
            $extArray = explode(',', $extensions);
            $extArray = array_merge($extArray, $this->imageHelper->getAllowedExtensions());
            $extensions = implode(',', array_unique($extArray));
        }

        return [$extensions];
    }

    /**
     * Fail-safe check for allowed extensions
     *
     * @param Uploader $subject
     * @param callable $proceed
     * @param string $extension
     * @return bool
     */
    public function aroundCheckAllowedExtension(Uploader $subject, callable $proceed, $extension): bool
    {
        if ($this->imageHelper->isEnabled()) {
            if (in_array(strtolower((string)$extension), $this->imageHelper->getAllowedExtensions(), true)) {
                return true;
            }
        }

        return (bool)$proceed($extension);
    }
}
