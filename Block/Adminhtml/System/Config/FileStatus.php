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
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\App\Filesystem\DirectoryList as DirList;
use Exception;

class FileStatus extends Field
{
    /**
     * @var DirectoryList
     */
    private DirectoryList $directoryList;

    /**
     * @param Context $context
     * @param DirectoryList $directoryList
     * @param array $data
     */
    public function __construct(
        Context $context,
        DirectoryList $directoryList,
        array $data = []
    ) {
        $this->directoryList = $directoryList;
        parent::__construct($context, $data);
    }

    /**
     * Return element html
     *
     * @param AbstractElement $element
     * @return string
     * @throws Exception
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        $websiteId = $this->getRequest()->getParam('website', 0);

        $scope = 'default';
        $scopeId = 0;

        if ($storeId) {
            $scope = 'store';
            $scopeId = $storeId;
        } elseif ($websiteId) {
            $scope = 'website';
            $scopeId = $websiteId;
        }

        $fileName = $this->getFileName($scope, (int) $scopeId);
        $filePath = $this->directoryList->getPath(DirList::PUB) . '/' . $fileName;

        if (file_exists($filePath)) {
            $fileSize = $this->formatFileSize(filesize($filePath));
            $html = '<span style="color: green;">' . __('File exists') . '</span>';
            $html .= '<br/><small>' . __('File: %1', $fileName) . '</small>';
            $html .= '<br/><small>' . __('Size: %1', $fileSize) . '</small>';
        } else {
            $html = '<span style="color: red;">' . __('File does not exist') . '</span>';
            $html .= '<br/><small>' . __('Expected location: %1', $fileName) . '</small>';
        }

        return $html;
    }

    /**
     * Get file name based on scope
     *
     * @param string $scope
     * @param int $scopeId
     * @return string
     */
    private function getFileName(string $scope, int $scopeId): string
    {
        if ($scope === 'store' && $scopeId > 0) {
            return 'llms_store_' . $scopeId . '.txt';
        } elseif ($scope === 'website' && $scopeId > 0) {
            return 'llms_website_' . $scopeId . '.txt';
        }
        return 'llms.txt';
    }

    /**
     * Format file size in human readable format
     *
     * @param int $bytes
     * @return string
     */
    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
