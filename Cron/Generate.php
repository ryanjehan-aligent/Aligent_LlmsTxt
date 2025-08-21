<?php
/**
 * Copyright Aligent All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aligent\LlmsTxt\Cron;

use Aligent\LlmsTxt\Model\Generator;
use Aligent\LlmsTxt\Model\Config\Source\Frequency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class Generate
{
    /**
     * @var Generator
     */
    private $generator;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Generator $generator
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param DateTime $dateTime
     * @param LoggerInterface $logger
     */
    public function __construct(
        Generator $generator,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        DateTime $dateTime,
        LoggerInterface $logger
    ) {
        $this->generator = $generator;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
    }

    /**
     * Execute cron job
     *
     * @return void
     */
    public function execute(): void
    {
        $this->logger->info('Starting LLMs.txt cron generation');

        // Process default scope
        if ($this->shouldGenerate('default', 0)) {
            $this->generator->generate('default', 0);
        }

        // Process all websites
        foreach ($this->storeManager->getWebsites() as $website) {
            if ($this->shouldGenerate('website', (int)$website->getId())) {
                $this->generator->generate('website', (int)$website->getId());
            }
        }

        // Process all stores
        foreach ($this->storeManager->getStores() as $store) {
            if ($this->shouldGenerate('store', (int)$store->getId())) {
                $this->generator->generate('store', (int)$store->getId());
            }
        }

        $this->logger->info('Completed LLMs.txt cron generation');
    }

    /**
     * Check if generation should run based on frequency and time settings
     *
     * @param string $scope
     * @param int $scopeId
     * @return bool
     */
    private function shouldGenerate(string $scope, int $scopeId): bool
    {
        $frequency = $this->getConfigValue('schedule/frequency', $scope, $scopeId);

        // Never run automatically
        if ($frequency === Frequency::FREQUENCY_NEVER) {
            return false;
        }

        $lastGenerated = $this->getConfigValue('status/last_generated_time', $scope, $scopeId);
        if (!$lastGenerated) {
            // Never generated before, so generate now
            return true;
        }

        $currentTime = $this->dateTime->gmtTimestamp();
        $lastGeneratedTime = strtotime($lastGenerated);
        $configuredTime = $this->getConfigValue('schedule/time', $scope, $scopeId);

        // Check if it's time to generate based on frequency
        switch ($frequency) {
            case Frequency::FREQUENCY_DAILY:
                // Run if a day has passed and time matches
                return $this->isDailyScheduleTime($lastGeneratedTime, $currentTime, $configuredTime);

            case Frequency::FREQUENCY_WEEKLY:
                // Run if a week has passed and time matches
                return $this->isWeeklyScheduleTime($lastGeneratedTime, $currentTime, $configuredTime);

            case Frequency::FREQUENCY_MONTHLY:
                // Run if a month has passed and time matches
                return $this->isMonthlyScheduleTime($lastGeneratedTime, $currentTime, $configuredTime);

            case Frequency::FREQUENCY_YEARLY:
                // Run if a year has passed and time matches
                return $this->isYearlyScheduleTime($lastGeneratedTime, $currentTime, $configuredTime);
        }

        return false;
    }

    /**
     * Check if it's time for daily generation
     *
     * @param int $lastGeneratedTime
     * @param int $currentTime
     * @param string|null $configuredTime
     * @return bool
     */
    private function isDailyScheduleTime(int $lastGeneratedTime, int $currentTime, ?string $configuredTime): bool
    {
        $daysSinceLastRun = floor(($currentTime - $lastGeneratedTime) / 86400);
        if ($daysSinceLastRun < 1) {
            return false;
        }

        return $this->isScheduledTime($configuredTime);
    }

    /**
     * Check if it's time for weekly generation
     *
     * @param int $lastGeneratedTime
     * @param int $currentTime
     * @param string|null $configuredTime
     * @return bool
     */
    private function isWeeklyScheduleTime(int $lastGeneratedTime, int $currentTime, ?string $configuredTime): bool
    {
        $weeksSinceLastRun = floor(($currentTime - $lastGeneratedTime) / 604800);
        if ($weeksSinceLastRun < 1) {
            return false;
        }

        return $this->isScheduledTime($configuredTime);
    }

    /**
     * Check if it's time for monthly generation
     *
     * @param int $lastGeneratedTime
     * @param int $currentTime
     * @param string|null $configuredTime
     * @return bool
     */
    private function isMonthlyScheduleTime(int $lastGeneratedTime, int $currentTime, ?string $configuredTime): bool
    {
        $lastDate = date('Y-m', $lastGeneratedTime);
        $currentDate = date('Y-m', $currentTime);

        if ($lastDate === $currentDate) {
            return false;
        }

        return $this->isScheduledTime($configuredTime);
    }

    /**
     * Check if it's time for yearly generation
     *
     * @param int $lastGeneratedTime
     * @param int $currentTime
     * @param string|null $configuredTime
     * @return bool
     */
    private function isYearlyScheduleTime(int $lastGeneratedTime, int $currentTime, ?string $configuredTime): bool
    {
        $lastYear = date('Y', $lastGeneratedTime);
        $currentYear = date('Y', $currentTime);

        if ($lastYear === $currentYear) {
            return false;
        }

        return $this->isScheduledTime($configuredTime);
    }

    /**
     * Check if current time matches scheduled time
     *
     * @param string|null $configuredTime
     * @return bool
     */
    private function isScheduledTime(?string $configuredTime): bool
    {
        if (!$configuredTime) {
            return true; // No specific time configured, run anytime
        }

        $currentTime = $this->dateTime->gmtDate('H:i:s');
        $scheduledTime = date('H:i:s', strtotime($configuredTime));

        // Check if we're within 59 minutes of the scheduled time (since cron runs hourly)
        $currentMinutes = (int)date('H') * 60 + (int)date('i');
        $scheduledMinutes = (int)date('H', strtotime($configuredTime)) * 60 + (int)date('i', strtotime($configuredTime));

        return abs($currentMinutes - $scheduledMinutes) < 60;
    }

    /**
     * Get configuration value
     *
     * @param string $path
     * @param string $scope
     * @param int $scopeId
     * @return mixed
     */
    private function getConfigValue(string $path, string $scope, int $scopeId)
    {
        $fullPath = 'aligent_llmstxt/' . $path;

        if ($scope === 'store') {
            return $this->scopeConfig->getValue($fullPath, ScopeInterface::SCOPE_STORE, $scopeId);
        } elseif ($scope === 'website') {
            return $this->scopeConfig->getValue($fullPath, ScopeInterface::SCOPE_WEBSITE, $scopeId);
        }

        return $this->scopeConfig->getValue($fullPath);
    }
}
