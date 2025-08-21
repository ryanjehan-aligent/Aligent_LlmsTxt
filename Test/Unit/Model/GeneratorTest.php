<?php
/**
 * Copyright Aligent All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Aligent\LlmsTxt\Test\Unit\Model;

use Aligent\LlmsTxt\Model\Generator;
use Aligent\LlmsTxt\Model\DataProvider\CmsPageProvider;
use Aligent\LlmsTxt\Model\DataProvider\ProductProvider;
use Aligent\LlmsTxt\Model\DataProvider\CategoryProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class GeneratorTest extends TestCase
{
    /**
     * @var Generator
     */
    private Generator $generator;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var WriterInterface|MockObject
     */
    private $configWriterMock;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystemMock;

    /**
     * @var WriteInterface|MockObject
     */
    private $pubDirectoryMock;

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
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->configWriterMock = $this->createMock(WriterInterface::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->pubDirectoryMock = $this->createMock(WriteInterface::class);
        $storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->dateTimeMock = $this->createMock(DateTime::class);
        $cmsPageProviderMock = $this->createMock(CmsPageProvider::class);
        $productProviderMock = $this->createMock(ProductProvider::class);
        $categoryProviderMock = $this->createMock(CategoryProvider::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->filesystemMock
            ->expects($this->once())
            ->method('getDirectoryWrite')
            ->willReturn($this->pubDirectoryMock);

        $this->generator = new Generator(
            $this->scopeConfigMock,
            $this->configWriterMock,
            $this->filesystemMock,
            $storeManagerMock,
            $this->dateTimeMock,
            $cmsPageProviderMock,
            $productProviderMock,
            $categoryProviderMock,
            $this->loggerMock
        );
    }

    public function testGenerateReturnsFalseWhenDisabled(): void
    {
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with(Generator::XML_PATH_ENABLED)
            ->willReturn(false);

        $result = $this->generator->generate();

        $this->assertFalse($result);
    }

    public function testGetFilenameForDefaultScope(): void
    {
        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('getFilename');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->generator, ['default', 0]);

        $this->assertEquals('llms.txt', $result);
    }

    public function testGetFilenameForStoreScope(): void
    {
        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('getFilename');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->generator, ['store', 1]);

        $this->assertEquals('llms_store_1.txt', $result);
    }

    public function testGetFilenameForWebsiteScope(): void
    {
        $reflection = new \ReflectionClass($this->generator);
        $method = $reflection->getMethod('getFilename');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->generator, ['website', 2]);

        $this->assertEquals('llms_website_2.txt', $result);
    }

    public function testFileExistsReturnsTrueWhenFileExists(): void
    {
        $this->pubDirectoryMock
            ->expects($this->once())
            ->method('isExist')
            ->with('llms.txt')
            ->willReturn(true);

        $result = $this->generator->fileExists();

        $this->assertTrue($result);
    }

    public function testFileExistsReturnsFalseWhenFileDoesNotExist(): void
    {
        $this->pubDirectoryMock
            ->expects($this->once())
            ->method('isExist')
            ->with('llms.txt')
            ->willReturn(false);

        $result = $this->generator->fileExists();

        $this->assertFalse($result);
    }
}