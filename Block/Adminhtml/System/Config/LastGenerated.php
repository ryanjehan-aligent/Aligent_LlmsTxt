<?php
/**
 * Copyright Aligent All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aligent\LlmsTxt\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class LastGenerated extends Field
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param TimezoneInterface $timezone
     * @param array $data
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        TimezoneInterface $timezone,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->timezone = $timezone;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $storeId = $this->getRequest()->getParam('store');
        $websiteId = $this->getRequest()->getParam('website');

        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $scopeId = null;

        if ($storeId) {
            $scope = ScopeInterface::SCOPE_STORE;
            $scopeId = $storeId;
        } elseif ($websiteId) {
            $scope = ScopeInterface::SCOPE_WEBSITE;
            $scopeId = $websiteId;
        }

        $lastGenerated = $this->scopeConfig->getValue(
            'aligent_llmstxt/status/last_generated_time',
            $scope,
            $scopeId
        );

        if ($lastGenerated) {
            $date = $this->timezone->date($lastGenerated);
            $formattedDate = $date->format('Y-m-d H:i:s');
            $html = '<span>' . __('Generated on: %1', $formattedDate) . '</span>';
        } else {
            $html = '<span style="color: #666;">' . __('Never generated') . '</span>';
        }

        return $html;
    }
}
