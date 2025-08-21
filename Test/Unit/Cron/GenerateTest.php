<?php
/**
 * Copyright Aligent All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Aligent\LlmsTxt\Test\Unit\Cron;

use Aligent\LlmsTxt\Cron\Generate;
use Aligent\LlmsTxt\Model\Generator;
use Aligent\LlmsTxt\Model\Config\Source\Frequency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class GenerateTest extends TestCase
{
    /**
     * @var Generate
     */
    private Generate $cronGenerate;

    /**
     * @var Generator|MockObject
     */
    private $generatorMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var DateTime|MockObject
     */
    private $dateTimeMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->generatorMock = $this->createMock(Generator::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->dateTimeMock = $this->createMock(DateTime::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->cronGenerate = new Generate(
            $this->generatorMock,
            $this->scopeConfigMock,
            $this->storeManagerMock,
            $this->dateTimeMock,
            $this->loggerMock
        );
    }

    public function testExecuteLogsStartAndEnd(): void
    {
        $this->loggerMock
            ->expects($this->exactly(2))
            ->method('info')
            ->withConsecutive(
                ['Starting LLMs.txt cron generation'],
                ['Completed LLMs.txt cron generation']
            );

        $this->storeManagerMock
            ->expects($this->once())
            ->method('getWebsites')
            ->willReturn([]);

        $this->storeManagerMock
            ->expects($this->once())
            ->method('getStores')
            ->willReturn([]);

        $this->cronGenerate->execute();
    }

    public function testExecuteProcessesWebsites(): void
    {
        $websiteMock = $this->createMock(Website::class);
        $websiteMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn('1');

        $this->storeManagerMock
            ->expects($this->once())
            ->method('getWebsites')
            ->willReturn([$websiteMock]);

        $this->storeManagerMock
            ->expects($this->once())
            ->method('getStores')
            ->willReturn([]);

        $this->loggerMock
            ->expects($this->atLeastOnce())
            ->method('info');

        $this->cronGenerate->execute();
    }

    public function testExecuteProcessesStores(): void
    {
        $storeMock = $this->createMock(Store::class);
        $storeMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn('1');

        $this->storeManagerMock
            ->expects($this->once())
            ->method('getWebsites')
            ->willReturn([]);

        $this->storeManagerMock
            ->expects($this->once())
            ->method('getStores')
            ->willReturn([$storeMock]);

        $this->loggerMock
            ->expects($this->atLeastOnce())
            ->method('info');

        $this->cronGenerate->execute();
    }

    public function testShouldGenerateReturnsFalseForNeverFrequency(): void
    {
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with('aligent_llmstxt/schedule/frequency')
            ->willReturn(Frequency::FREQUENCY_NEVER);

        $reflection = new \ReflectionClass($this->cronGenerate);
        $method = $reflection->getMethod('shouldGenerate');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->cronGenerate, ['default', 0]);

        $this->assertFalse($result);
    }

    public function testShouldGenerateReturnsTrueWhenNeverGeneratedBefore(): void
    {
        $this->scopeConfigMock
            ->expects($this->exactly(2))
            ->method('getValue')
            ->withConsecutive(
                ['aligent_llmstxt/schedule/frequency'],
                ['aligent_llmstxt/status/last_generated_time']
            )
            ->willReturnOnConsecutiveCalls(
                Frequency::FREQUENCY_HOURLY,
                null
            );

        $reflection = new \ReflectionClass($this->cronGenerate);
        $method = $reflection->getMethod('shouldGenerate');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->cronGenerate, ['default', 0]);

        $this->assertTrue($result);
    }
}