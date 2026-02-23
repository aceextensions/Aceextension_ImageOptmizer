<?php

declare(strict_types=1);

namespace Aceextension\ImageOptmizer\Plugin\Frontend;

use Magento\Catalog\Model\View\Asset\Image;
use Aceextension\ImageOptmizer\Helper\Data as ImageHelper;

class AssetImagePlugin
{
    /**
     * @param ImageHelper $imageHelper
     */
    public function __construct(
        private readonly ImageHelper $imageHelper
    ) {}

    /**
     * Rewrite all frontend catalog image URLs to .webp
     *
     * @param Image $subject
     * @param string $result
     * @return string
     */
    public function afterGetUrl(Image $subject, string $result): string
    {
        if ($this->imageHelper->isWebpReplacementEnabled() && !str_contains($result, '/placeholder/')) {
            return (string)preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $result);
        }
        return $result;
    }
}
