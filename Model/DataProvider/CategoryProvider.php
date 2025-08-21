<?php
/**
 * Copyright Aligent All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aligent\LlmsTxt\Model\DataProvider;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

class CategoryProvider
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CollectionFactory $categoryCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        CollectionFactory $categoryCollectionFactory,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Get categories for the specified scope
     *
     * @param string $scope
     * @param int $scopeId
     * @return array
     */
    public function getCategories(string $scope, int $scopeId): array
    {
        $categories = [];

        try {
            $storeId = $this->getStoreId($scope, $scopeId);
            $rootCategoryId = $this->getRootCategoryId($storeId);

            $collection = $this->categoryCollectionFactory->create();
            $collection->setStoreId($storeId);
            $collection->addAttributeToSelect(['name', 'description', 'meta_description', 'url_key']);
            $collection->addAttributeToFilter('is_active', 1);
            $collection->addAttributeToFilter('include_in_menu', 1);
            $collection->addAttributeToFilter('path', ['like' => "1/{$rootCategoryId}/%"]);
            $collection->addAttributeToFilter('level', ['gt' => 2]); // Skip root and default categories

            foreach ($collection as $category) {
                $categories[] = [
                    'name' => $category->getName(),
                    'url' => $this->getCategoryUrl($category),
                    'description' => $this->getCategoryDescription($category),
                    'product_count' => $this->getProductCount($category),
                    'path' => $this->getCategoryPath($category)
                ];
            }
        } catch (\Exception $e) {
            $this->logger->error('Error getting categories: ' . $e->getMessage());
        }

        return $categories;
    }

    /**
     * Get store ID based on scope
     *
     * @param string $scope
     * @param int $scopeId
     * @return int
     */
    private function getStoreId(string $scope, int $scopeId): int
    {
        if ($scope === 'store') {
            return $scopeId;
        } elseif ($scope === 'website') {
            $website = $this->storeManager->getWebsite($scopeId);
            $defaultStore = $website->getDefaultStore();
            return (int)$defaultStore->getId();
        }

        // Default scope - use default store
        return (int)$this->storeManager->getDefaultStoreView()->getId();
    }

    /**
     * Get root category ID for store
     *
     * @param int $storeId
     * @return int
     */
    private function getRootCategoryId(int $storeId): int
    {
        try {
            $store = $this->storeManager->getStore($storeId);
            return (int)$store->getRootCategoryId();
        } catch (\Exception $e) {
            $this->logger->error('Error getting root category: ' . $e->getMessage());
            return 2; // Default root category
        }
    }

    /**
     * Get category URL
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     */
    private function getCategoryUrl($category): string
    {
        try {
            return $category->getUrl();
        } catch (\Exception $e) {
            $this->logger->error('Error getting category URL: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Get category description
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     */
    private function getCategoryDescription($category): string
    {
        $description = $category->getDescription() ?? $category->getMetaDescription() ?? '';

        // Clean HTML
        $description = strip_tags($description);
        $description = html_entity_decode($description, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $description = preg_replace('/\s+/', ' ', $description);
        $description = trim($description);

        // Limit length
        if (strlen($description) > 300) {
            $description = substr($description, 0, 297) . '...';
        }

        return $description;
    }

    /**
     * Get product count for category
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return int
     */
    private function getProductCount($category): int
    {
        return (int)$category->getProductCount();
    }

    /**
     * Get category path as breadcrumb
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     */
    private function getCategoryPath($category): string
    {
        $path = [];
        $pathIds = explode('/', $category->getPath());

        // Skip first two levels (root and default category)
        $pathIds = array_slice($pathIds, 2);

        foreach ($pathIds as $categoryId) {
            try {
                $parentCategory = $this->categoryRepository->get($categoryId);
                $path[] = $parentCategory->getName();
            } catch (\Exception $e) {
                // Skip if category not found
            }
        }

        return implode(' > ', $path);
    }
}
