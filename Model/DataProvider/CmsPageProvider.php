<?php
/**
 * Copyright Aligent All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aligent\LlmsTxt\Model\DataProvider;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Cms\Helper\Page as PageHelper;
use Psr\Log\LoggerInterface;

class CmsPageProvider
{
    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var PageHelper
     */
    private $pageHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param PageRepositoryInterface $pageRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManagerInterface $storeManager
     * @param PageHelper $pageHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        PageRepositoryInterface $pageRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager,
        PageHelper $pageHelper,
        LoggerInterface $logger
    ) {
        $this->pageRepository = $pageRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->pageHelper = $pageHelper;
        $this->logger = $logger;
    }

    /**
     * Get CMS pages for the specified scope
     *
     * @param string $scope
     * @param int $scopeId
     * @return array
     */
    public function getPages(string $scope, int $scopeId): array
    {
        $pages = [];

        try {
            $storeIds = $this->getStoreIds($scope, $scopeId);

            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('is_active', '1')
                ->addFilter('store_id', $storeIds, 'in')
                ->create();

            $pageList = $this->pageRepository->getList($searchCriteria);

            foreach ($pageList->getItems() as $page) {
                $storeId = $this->getStoreIdForPage($page, $storeIds);

                $pages[] = [
                    'title' => $page->getTitle(),
                    'url' => $this->getPageUrl($page, $storeId),
                    'content' => $this->cleanContent($page->getContent()),
                    'meta_description' => $page->getMetaDescription() ?? ''
                ];
            }
        } catch (\Exception $e) {
            $this->logger->error('Error getting CMS pages: ' . $e->getMessage());
        }

        return $pages;
    }

    /**
     * Get store IDs based on scope
     *
     * @param string $scope
     * @param int $scopeId
     * @return array
     */
    private function getStoreIds(string $scope, int $scopeId): array
    {
        if ($scope === 'store') {
            return [$scopeId];
        } elseif ($scope === 'website') {
            $website = $this->storeManager->getWebsite($scopeId);
            return $website->getStoreIds();
        }

        // Default scope - all stores
        $storeIds = [];
        foreach ($this->storeManager->getStores() as $store) {
            $storeIds[] = $store->getId();
        }
        return $storeIds;
    }

    /**
     * Get store ID for a page
     *
     * @param \Magento\Cms\Api\Data\PageInterface $page
     * @param array $storeIds
     * @return int
     */
    private function getStoreIdForPage($page, array $storeIds): int
    {
        $pageStoreIds = $page->getStoreId();
        if (!is_array($pageStoreIds)) {
            $pageStoreIds = [$pageStoreIds];
        }

        // Find first matching store ID
        foreach ($storeIds as $storeId) {
            if (in_array($storeId, $pageStoreIds) || in_array(0, $pageStoreIds)) {
                return (int)$storeId;
            }
        }

        return (int)reset($storeIds);
    }

    /**
     * Get full URL for CMS page
     *
     * @param \Magento\Cms\Api\Data\PageInterface $page
     * @param int $storeId
     * @return string
     */
    private function getPageUrl($page, int $storeId): string
    {
        try {
            $store = $this->storeManager->getStore($storeId);
            return $store->getBaseUrl(UrlInterface::URL_TYPE_WEB) . $page->getIdentifier();
        } catch (\Exception $e) {
            $this->logger->error('Error getting page URL: ' . $e->getMessage());
            return $page->getIdentifier();
        }
    }

    /**
     * Clean HTML content for text output
     *
     * @param string $content
     * @return string
     */
    private function cleanContent(string $content): string
    {
        // Remove HTML tags
        $content = strip_tags($content);

        // Convert entities
        $content = html_entity_decode($content, ENT_QUOTES | ENT_XML1, 'UTF-8');

        // Remove excessive whitespace
        $content = preg_replace('/\s+/', ' ', $content);

        // Trim
        $content = trim($content);

        return $content;
    }
}
