<?php

declare(strict_types=1);

namespace Aceextension\ImageOptmizer\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Aceextension\ImageOptmizer\Helper\Data as ImageHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Backend\Block\Template\Context;

class ImageMagickWarning extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Aceextension_ImageOptmizer::system/config/imagemagick_warning.phtml';

    /**
     * @param Context $context
     * @param ImageHelper $imageHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        private readonly ImageHelper $imageHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Render entire row
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        if (!$this->shouldDisplayWarning()) {
            return '';
        }

        return '<tr id="row_' . $element->getHtmlId() . '">'
            . '<td colspan="3" style="padding: 1.5rem 0;">' . $this->_toHtml() . '</td>'
            . '</tr>';
    }

    /**
     * Render element value
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Check if ImageMagick is configured as the default adapter
     *
     * @return bool
     */
    public function isImageMagickConfigured(): bool
    {
        $adapter = $this->_scopeConfig->getValue('dev/image/default_adapter', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return strtoupper((string)$adapter) === 'IMAGEMAGICK';
    }

    /**
     * Check if warning should be displayed
     *
     * @return bool
     */
    public function shouldDisplayWarning(): bool
    {
        return $this->imageHelper->isEnabled() && !$this->isImageMagickConfigured();
    }
}
