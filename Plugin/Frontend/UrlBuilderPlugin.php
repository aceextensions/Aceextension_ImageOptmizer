<?php

declare(strict_types=1);

namespace Aceextension\ImageOptmizer\Plugin\Frontend;

use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Aceextension\ImageOptmizer\Helper\Data as ImageHelper;

class UrlBuilderPlugin
{
    /**
     * @param ImageHelper $imageHelper
     */
    public function __construct(
        private readonly ImageHelper $imageHelper
    ) {}

    /**
     * Replace JPG/PNG with WebP in generated URLs
     *
     * @param UrlBuilder $subject
     * @param string $result
     * @return string
     */
    public function afterGetUrl(UrlBuilder $subject, string $result): string
    {
        if ($this->imageHelper->isWebpReplacementEnabled() && !str_contains($result, '/placeholder/')) {
            return (string)preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $result);
        }
        return $result;
    }
}
