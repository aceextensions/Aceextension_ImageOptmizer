<?php

declare(strict_types=1);

/**
 * Aceextension_ImageOptmizer
 *
 * @category    Aceextension
 * @package     Aceextension_ImageOptmizer
 * @author      Aceextension
 */

namespace Aceextension\ImageOptmizer\Plugin\App;

use Magento\MediaStorage\App\Media;
use Aceextension\ImageOptmizer\Helper\Data as ImageHelper;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Response\Http as HttpResponse;

class MediaPlugin
{
    /**
     * @param ImageHelper $imageHelper
     */
    public function __construct(
        private readonly ImageHelper $imageHelper
    ) {}

    /**
     * Intercept Media application launch to handle WebP generation on the fly
     *
     * @param Media $subject
     * @param \Closure $proceed
     * @return ResponseInterface
     */
    public function aroundLaunch(Media $subject, \Closure $proceed): ResponseInterface
    {
        if (!$this->imageHelper->isEnabled()) {
            return $proceed();
        }

        if (!$this->imageHelper->isWebpReplacementEnabled()) {
            return $proceed();
        }

        try {
            $reflection = new \ReflectionClass(\Magento\MediaStorage\App\Media::class);

            $relativeFileNameProp = $reflection->getProperty('relativeFileName');
            $relativeFileNameProp->setAccessible(true);
            $requestedFile = $relativeFileNameProp->getValue($subject);

            if ($requestedFile && preg_match('/\.webp$/i', (string)$requestedFile)) {
                // Try to find the source image (jpg or png)
                $sourceFile = preg_replace('/\.webp$/i', '.jpg', (string)$requestedFile);

                // Swap the relativeFileName to source file to let Magento generate the JPG first
                $relativeFileNameProp->setValue($subject, $sourceFile);

                /** @var ResponseInterface $response */
                $response = $proceed();

                // Get the pub directory to locate physical files
                $directoryPubProp = $reflection->getProperty('directoryPub');
                $directoryPubProp->setAccessible(true);
                $directoryPub = $directoryPubProp->getValue($subject);

                $sourceAbsolutePath = $directoryPub->getAbsolutePath($sourceFile);
                $webpAbsolutePath = $directoryPub->getAbsolutePath($requestedFile);

                if (file_exists($sourceAbsolutePath)) {
                    // Convert the generated JPG to WebP
                    if ($this->imageHelper->convertToWebp($sourceAbsolutePath, $webpAbsolutePath)) {
                        // Update the response to serve the newly created WebP file
                        if ($response instanceof HttpResponse && method_exists($response, 'setFilePath')) {
                            $response->setFilePath($webpAbsolutePath);

                            // Remove existing Content-Type header and set it to image/webp
                            $response->clearHeader('Content-Type');
                            $response->setHeader('Content-Type', 'image/webp');
                        }
                    }
                }

                // Restore the original requested filename state for the subject
                $relativeFileNameProp->setValue($subject, $requestedFile);

                return $response;
            }
        } catch (\Exception $e) {
            return $proceed();
        }

        return $proceed();
    }
}
