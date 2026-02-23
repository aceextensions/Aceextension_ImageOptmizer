<?php

declare(strict_types=1);

namespace Aceextension\ImageOptmizer\Plugin\Cms;

use Magento\Cms\Model\Wysiwyg\Images\Storage;
use Aceextension\ImageOptmizer\Helper\Data as ImageHelper;

class WysiwygStoragePlugin
{
    /**
     * @param ImageHelper $imageHelper
     */
    public function __construct(
        private readonly ImageHelper $imageHelper
    ) {}

    /**
     * Add modern formats to allowed extensions in WYSIWYG
     *
     * @param Storage $subject
     * @param array $result
     * @return array
     */
    public function afterGetAllowedExtensions(Storage $subject, array $result): array
    {
        if (!$this->imageHelper->isEnabled()) {
            return $result;
        }
        return array_unique(array_merge($result, $this->imageHelper->getAllowedExtensions()));
    }
}
