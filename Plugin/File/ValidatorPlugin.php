<?php

declare(strict_types=1);

namespace Aceextension\ImageOptmizer\Plugin\File;

use Magento\MediaStorage\Model\File\Validator\NotProtectedExtension;
use Aceextension\ImageOptmizer\Helper\Data as ImageHelper;

class ValidatorPlugin
{
    /**
     * @param ImageHelper $imageHelper
     */
    public function __construct(
        private readonly ImageHelper $imageHelper
    ) {}

    /**
     * This is more effective: intercept the protected extensions
     *
     * @param NotProtectedExtension $subject
     * @param array $result
     * @return array
     */
    public function afterGetProtectedExtensions(NotProtectedExtension $subject, array $result): array
    {
        if (!$this->imageHelper->isEnabled()) {
            return $result;
        }

        $allowed = $this->imageHelper->getAllowedExtensions();
        foreach ($allowed as $ext) {
            if (($key = array_search($ext, $result)) !== false) {
                unset($result[$key]);
            }
        }
        return $result;
    }
}
