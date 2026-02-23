<?php

declare(strict_types=1);

namespace Aceextension\ImageOptmizer\Plugin\Catalog;

use Magento\Catalog\Model\Category\Attribute\Backend\Image;
use Aceextension\ImageOptmizer\Helper\Data as ImageHelper;

class CategoryImagePlugin
{
    /**
     * @param ImageHelper $imageHelper
     */
    public function __construct(
        private readonly ImageHelper $imageHelper
    ) {}

    /**
     * Add modern formats to the category image allowed extensions
     *
     * @param Image $subject
     * @param array $result
     * @return array
     */
    public function afterGetAllowedExtensions(Image $subject, array $result): array
    {
        if (!$this->imageHelper->isEnabled()) {
            return $result;
        }
        return array_unique(array_merge($result, $this->imageHelper->getAllowedExtensions()));
    }
}
