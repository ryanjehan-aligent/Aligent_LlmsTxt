<?php
/**
 * Copyright Aligent All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aligent\LlmsTxt\Model\DataProvider;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Psr\Log\LoggerInterface;

class ProductProvider
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Get products for the specified scope
     *
     * @param string $scope
     * @param int $scopeId
     * @param int $limit
     * @return array
     */
    public function getProducts(string $scope, int $scopeId, int $limit = 100): array
    {
        $products = [];

        try {
            $storeId = $this->getStoreId($scope, $scopeId);

            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('status', Status::STATUS_ENABLED)
                ->addFilter('visibility', [
                    Visibility::VISIBILITY_IN_CATALOG,
                    Visibility::VISIBILITY_IN_SEARCH,
                    Visibility::VISIBILITY_BOTH
                ], 'in')
                ->setPageSize($limit)
                ->create();

            $productList = $this->productRepository->getList($searchCriteria);

            foreach ($productList->getItems() as $product) {
                $products[] = [
                    'name' => $product->getName(),
                    'sku' => $product->getSku(),
                    'url' => $this->getProductUrl($product, $storeId),
                    'description' => $this->getProductDescription($product),
                    'price' => $this->getProductPrice($product),
                    'in_stock' => $this->isProductInStock($product)
                ];
            }
        } catch (\Exception $e) {
            $this->logger->error('Error getting products: ' . $e->getMessage());
        }

        return $products;
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
     * Get product URL
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param int $storeId
     * @return string
     */
    private function getProductUrl($product, int $storeId): string
    {
        try {
            $store = $this->storeManager->getStore($storeId);
            $product->setStoreId($storeId);
            return $product->getProductUrl();
        } catch (\Exception $e) {
            $this->logger->error('Error getting product URL: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Get product description
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return string
     */
    private function getProductDescription($product): string
    {
        $description = $product->getDescription() ?? $product->getShortDescription() ?? '';

        // Clean HTML
        $description = strip_tags($description);
        $description = html_entity_decode($description, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $description = preg_replace('/\s+/', ' ', $description);
        $description = trim($description);

        // Limit length
        if (strlen($description) > 500) {
            $description = substr($description, 0, 497) . '...';
        }

        return $description;
    }

    /**
     * Get product price
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return string
     */
    private function getProductPrice($product): string
    {
        $price = $product->getPrice();
        if ($price) {
            return number_format((float)$price, 2);
        }
        return '';
    }

    /**
     * Check if product is in stock
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return bool
     */
    private function isProductInStock($product): bool
    {
        $stockItem = $product->getExtensionAttributes()->getStockItem();
        if ($stockItem) {
            return $stockItem->getIsInStock();
        }
        return false;
    }
}
