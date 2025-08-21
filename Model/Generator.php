<?php
/**
 * Copyright Aligent All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Aligent\LlmsTxt\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Aligent\LlmsTxt\Model\DataProvider\CmsPageProvider;
use Aligent\LlmsTxt\Model\DataProvider\ProductProvider;
use Aligent\LlmsTxt\Model\DataProvider\CategoryProvider;
use Magento\Framework\Exception\FileSystemException;
use Psr\Log\LoggerInterface;

class Generator
{
    /**
     * Configuration paths
     */
    public const string XML_PATH_ENABLED = 'aligent_llmstxt/general/enabled';
    public const string XML_PATH_COMPANY_NAME = 'aligent_llmstxt/general/company_name';
    public const string XML_PATH_COMPANY_DESCRIPTION = 'aligent_llmstxt/general/company_description';
    public const string XML_PATH_EXTRA_INFO = 'aligent_llmstxt/general/extra_info';
    public const string XML_PATH_INCLUDE_CMS = 'aligent_llmstxt/entities/include_cms_pages';
    public const string XML_PATH_INCLUDE_PRODUCTS = 'aligent_llmstxt/entities/include_products';
    public const string XML_PATH_INCLUDE_CATEGORIES = 'aligent_llmstxt/entities/include_categories';
    public const string XML_PATH_PRODUCT_LIMIT = 'aligent_llmstxt/entities/product_limit';
    public const string XML_PATH_LAST_GENERATED = 'aligent_llmstxt/status/last_generated_time';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var WriterInterface
     */
    private WriterInterface $configWriter;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * @var CmsPageProvider
     */
    private CmsPageProvider $cmsPageProvider;

    /**
     * @var ProductProvider
     */
    private ProductProvider $productProvider;

    /**
     * @var CategoryProvider
     */
    private CategoryProvider $categoryProvider;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var WriteInterface
     */
    private WriteInterface $pubDirectory;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @param DateTime $dateTime
     * @param CmsPageProvider $cmsPageProvider
     * @param ProductProvider $productProvider
     * @param CategoryProvider $categoryProvider
     * @param LoggerInterface $logger
     * @throws FileSystemException
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        DateTime $dateTime,
        CmsPageProvider $cmsPageProvider,
        ProductProvider $productProvider,
        CategoryProvider $categoryProvider,
        LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->cmsPageProvider = $cmsPageProvider;
        $this->productProvider = $productProvider;
        $this->categoryProvider = $categoryProvider;
        $this->logger = $logger;
        $this->pubDirectory = $filesystem->getDirectoryWrite(DirectoryList::PUB);
    }

    /**
     * Generate llms.txt file
     *
     * @param string $scope
     * @param int $scopeId
     * @return bool
     */
    public function generate(string $scope = 'default', int $scopeId = 0): bool
    {
        try {
            if (!$this->isEnabled($scope, $scopeId)) {
                $this->logger->info('LLMs.txt generation is disabled for scope: ' . $scope . ', scopeId: ' . $scopeId);
                return false;
            }

            $content = $this->buildContent($scope, $scopeId);
            $filename = $this->getFilename($scope, $scopeId);

            $this->pubDirectory->writeFile($filename, $content);

            // Update last generated timestamp
            $this->updateLastGenerated($scope, $scopeId);

            $this->logger->info('Successfully generated LLMs.txt file: ' . $filename);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error generating LLMs.txt file: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Build content for llms.txt file
     *
     * @param string $scope
     * @param int $scopeId
     * @return string
     */
    private function buildContent(string $scope, int $scopeId): string
    {
        $content = "# LLMs.txt\n\n";
        $content .= "This file contains structured information about our website to help AI assistants understand our content.\n\n";

        // Add company information
        $companyName = $this->getConfigValue(self::XML_PATH_COMPANY_NAME, $scope, $scopeId);
        if ($companyName) {
            $content .= "## Company: " . $companyName . "\n\n";
        }

        $companyDescription = $this->getConfigValue(self::XML_PATH_COMPANY_DESCRIPTION, $scope, $scopeId);
        if ($companyDescription) {
            $content .= "### About Us\n" . $companyDescription . "\n\n";
        }

        $extraInfo = $this->getConfigValue(self::XML_PATH_EXTRA_INFO, $scope, $scopeId);
        if ($extraInfo) {
            $content .= "### Additional Information\n" . $extraInfo . "\n\n";
        }

        // Add CMS pages if enabled
        if ($this->getConfigValue(self::XML_PATH_INCLUDE_CMS, $scope, $scopeId)) {
            $content .= $this->getCmsContent($scope, $scopeId);
        }

        // Add products if enabled
        if ($this->getConfigValue(self::XML_PATH_INCLUDE_PRODUCTS, $scope, $scopeId)) {
            $content .= $this->getProductContent($scope, $scopeId);
        }

        // Add categories if enabled
        if ($this->getConfigValue(self::XML_PATH_INCLUDE_CATEGORIES, $scope, $scopeId)) {
            $content .= $this->getCategoryContent($scope, $scopeId);
        }

        // Add generation timestamp
        $content .= "\n---\n";
        $content .= "Generated: " . $this->dateTime->gmtDate('Y-m-d H:i:s') . " GMT\n";

        return $content;
    }

    /**
     * Get CMS pages content
     *
     * @param string $scope
     * @param int $scopeId
     * @return string
     */
    private function getCmsContent(string $scope, int $scopeId): string
    {
        $content = "## CMS Pages\n\n";

        $pages = $this->cmsPageProvider->getPages($scope, $scopeId);

        if (empty($pages)) {
            $content .= "No CMS pages available.\n\n";
            return $content;
        }

        foreach ($pages as $page) {
            $content .= "### " . $page['title'] . "\n";
            $content .= "URL: " . $page['url'] . "\n";
            if ($page['meta_description']) {
                $content .= "Description: " . $page['meta_description'] . "\n";
            }
            if ($page['content']) {
                $content .= "Content: " . $this->truncateText($page['content'], 500) . "\n";
            }
            $content .= "\n";
        }

        return $content;
    }

    /**
     * Get products content
     *
     * @param string $scope
     * @param int $scopeId
     * @return string
     */
    private function getProductContent(string $scope, int $scopeId): string
    {
        $content = "## Products\n\n";

        $limit = (int)$this->getConfigValue(self::XML_PATH_PRODUCT_LIMIT, $scope, $scopeId) ?: 100;
        $products = $this->productProvider->getProducts($scope, $scopeId, $limit);

        if (empty($products)) {
            $content .= "No products available.\n\n";
            return $content;
        }

        foreach ($products as $product) {
            $content .= "### " . $product['name'] . "\n";
            $content .= "SKU: " . $product['sku'] . "\n";
            $content .= "URL: " . $product['url'] . "\n";
            if ($product['price']) {
                $content .= "Price: $" . $product['price'] . "\n";
            }
            $content .= "In Stock: " . ($product['in_stock'] ? 'Yes' : 'No') . "\n";
            if ($product['description']) {
                $content .= "Description: " . $product['description'] . "\n";
            }
            $content .= "\n";
        }

        return $content;
    }

    /**
     * Get categories content
     *
     * @param string $scope
     * @param int $scopeId
     * @return string
     */
    private function getCategoryContent(string $scope, int $scopeId): string
    {
        $content = "## Categories\n\n";

        $categories = $this->categoryProvider->getCategories($scope, $scopeId);

        if (empty($categories)) {
            $content .= "No categories available.\n\n";
            return $content;
        }

        foreach ($categories as $category) {
            $content .= "### " . $category['name'] . "\n";
            $content .= "Path: " . $category['path'] . "\n";
            $content .= "URL: " . $category['url'] . "\n";
            $content .= "Product Count: " . $category['product_count'] . "\n";
            if ($category['description']) {
                $content .= "Description: " . $category['description'] . "\n";
            }
            $content .= "\n";
        }

        return $content;
    }

    /**
     * Truncate text to specified length
     *
     * @param string $text
     * @param int $length
     * @return string
     */
    private function truncateText(string $text, int $length): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length - 3) . '...';
    }

    /**
     * Get filename for llms.txt based on scope
     *
     * @param string $scope
     * @param int $scopeId
     * @return string
     */
    private function getFilename(string $scope, int $scopeId): string
    {
        if ($scope === 'store') {
            return 'llms_store_' . $scopeId . '.txt';
        } elseif ($scope === 'website') {
            return 'llms_website_' . $scopeId . '.txt';
        }

        return 'llms.txt';
    }

    /**
     * Check if file exists
     *
     * @param string $scope
     * @param int $scopeId
     * @return bool
     */
    public function fileExists(string $scope = 'default', int $scopeId = 0): bool
    {
        $filename = $this->getFilename($scope, $scopeId);
        return $this->pubDirectory->isExist($filename);
    }

    /**
     * Get file size
     *
     * @param string $scope
     * @param int $scopeId
     * @return int
     */
    public function getFileSize(string $scope = 'default', int $scopeId = 0): int
    {
        $filename = $this->getFilename($scope, $scopeId);
        if ($this->pubDirectory->isExist($filename)) {
            $stat = $this->pubDirectory->stat($filename);
            return $stat['size'] ?? 0;
        }
        return 0;
    }

    /**
     * Update last generated timestamp
     *
     * @param string $scope
     * @param int $scopeId
     * @return void
     */
    private function updateLastGenerated(string $scope, int $scopeId): void
    {
        $timestamp = $this->dateTime->gmtDate('Y-m-d H:i:s');

        if ($scope === 'store') {
            $this->configWriter->save(
                self::XML_PATH_LAST_GENERATED,
                $timestamp,
                ScopeInterface::SCOPE_STORES,
                $scopeId
            );
        } elseif ($scope === 'website') {
            $this->configWriter->save(
                self::XML_PATH_LAST_GENERATED,
                $timestamp,
                ScopeInterface::SCOPE_WEBSITES,
                $scopeId
            );
        } else {
            $this->configWriter->save(
                self::XML_PATH_LAST_GENERATED,
                $timestamp
            );
        }
    }

    /**
     * Check if generation is enabled
     *
     * @param string $scope
     * @param int $scopeId
     * @return bool
     */
    private function isEnabled(string $scope, int $scopeId): bool
    {
        return (bool)$this->getConfigValue(self::XML_PATH_ENABLED, $scope, $scopeId);
    }

    /**
     * Get configuration value
     *
     * @param string $path
     * @param string $scope
     * @param int $scopeId
     * @return mixed
     */
    private function getConfigValue(string $path, string $scope, int $scopeId): mixed
    {
        if ($scope === 'store') {
            return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $scopeId);
        } elseif ($scope === 'website') {
            return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_WEBSITE, $scopeId);
        }

        return $this->scopeConfig->getValue($path);
    }
}
