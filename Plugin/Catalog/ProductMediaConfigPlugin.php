<?php

declare(strict_types=1);

namespace Aceextension\ImageOptmizer\Plugin\Catalog;

use Magento\Catalog\Model\Product\Media\Config;
use Aceextension\ImageOptmizer\Helper\Data as ImageHelper;

class ProductMediaConfigPlugin
{
    /**
     * @param ImageHelper $imageHelper
     */
    public function __construct(
        private readonly ImageHelper $imageHelper
    ) {}

    /**
     * Add modern formats to the product gallery allowed extensions
     *
     * @param Config $subject
     * @param array $result
     * @return array
     */
    public function afterGetAllowedExtensions(Config $subject, array $result): array
    {
        if (!$this->imageHelper->isEnabled()) {
            return $result;
        }
        return array_unique(array_merge($result, $this->imageHelper->getAllowedExtensions()));
    }
}
