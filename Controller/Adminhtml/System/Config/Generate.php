<?php
/**
 * Copyright Aligent All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Aligent\LlmsTxt\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Aligent\LlmsTxt\Model\Generator;
use Psr\Log\LoggerInterface;

class Generate extends Action
{
    public const string ADMIN_RESOURCE = 'Aligent_LlmsTxt::config';

    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;

    /**
     * @var Generator
     */
    private Generator $generator;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Generator $generator
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Generator $generator,
        LoggerInterface $logger
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->generator = $generator;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        try {
            $storeId = $this->getRequest()->getParam('store');
            $websiteId = $this->getRequest()->getParam('website');

            $scope = 'default';
            $scopeId = 0;

            if ($storeId) {
                $scope = 'store';
                $scopeId = (int)$storeId;
            } elseif ($websiteId) {
                $scope = 'website';
                $scopeId = (int)$websiteId;
            }

            $result = $this->generator->generate($scope, $scopeId);

            if ($result) {
                return $resultJson->setData([
                    'success' => true,
                    'message' => __('LLMs.txt file has been generated successfully.')
                ]);
            } else {
                return $resultJson->setData([
                    'success' => false,
                    'message' => __('Failed to generate LLMs.txt file. Please check the logs.')
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error generating LLMs.txt file: ' . $e->getMessage());
            return $resultJson->setData([
                'success' => false,
                'message' => __('An error occurred: %1', $e->getMessage())
            ]);
        }
    }
}
