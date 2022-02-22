<?php

namespace Marketplacer\Seller\Test\Unit\Model\Sitemap\ItemProvider;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sitemap\Model\ItemProvider\CategoryConfigReader;
use Magento\Sitemap\Model\SitemapItem;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Helper\Config as ConfigHelper;
use Marketplacer\Seller\Helper\Url as UrlHelper;
use Marketplacer\Seller\Model\SellerRepository;
use Marketplacer\Seller\Model\Sitemap\ItemProvider\Seller;
use Marketplacer\SellerApi\Api\Data\MarketplacerSellerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class SellerTest
 * @package Marketplacer\Seller\Test\Unit\Model\Sitemap\ItemProvider
 */
class SellerTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    public function testSellerConfigDisabled() {
        $storeId = 1;
        $url = '';
        $this->objectManager = new ObjectManager($this);
        $this->sellerObject = $this->objectManager->getObject(\Marketplacer\Seller\Model\Seller::class);
        $sellers[] = $this->sellerObject->setData([]);

        $sellerCollectionMock = $this->getSellerCollectionMock($storeId, $sellers);
        $urlHelper = $this->getUrlHelper($url);
        $configHelperMock = $this->getDisabledConfigHelper($storeId);
        $itemFactoryMock = $this->getItemFactoryMock();
        $configReaderMock = $this->getConfigReaderMock();

        $seller = $this->objectManager->getObject(Seller::class,
            [
                'sellerRepository'     => $sellerCollectionMock,
                'urlHelper'            => $urlHelper,
                'configHelper'         => $configHelperMock,
                'sitemapItemFactory'   => $itemFactoryMock,
                'categoryConfigReader' => $configReaderMock,
            ]
        );
        $items = $seller->getItems($storeId);
        $this->assertEquals([], $items);
    }

    public function testGetItemsEmpty()
    {
        $storeId = 1;
        $url = null;
        $this->objectManager = new ObjectManager($this);
        $this->sellerObject = $this->objectManager->getObject(\Marketplacer\Seller\Model\Seller::class);
        $sellers[] = $this->sellerObject->setData([]);

        $sellerCollectionMock = $this->getSellerCollectionMock($storeId, []);
        $urlHelper = $this->getUrlHelper($url);
        $configHelperMock = $this->getConfigHelper($storeId);
        $itemFactoryMock = $this->getItemFactoryMock();
        $configReaderMock = $this->getConfigReaderMock();
        $seller = $this->objectManager->getObject(Seller::class,
            [
                'sellerRepository'     => $sellerCollectionMock,
                'urlHelper'            => $urlHelper,
                'configHelper'         => $configHelperMock,
                'sitemapItemFactory'   => $itemFactoryMock,
                'categoryConfigReader' => $configReaderMock,
            ]
        );
        $items = $seller->getItems($storeId);
        $this->assertNotEquals($sellers,$items);
    }

    public function testGetItems() {
        $storeId = 1;
        $url = 'http://localhost.com/dev/';
        $this->objectManager = new ObjectManager($this);
        $this->sellerObject = $this->objectManager->getObject(\Marketplacer\Seller\Model\Seller::class);
        $sellers[] = $this->sellerObject->setData([
            SellerInterface::STORE_ID => 1,
            MarketplacerSellerInterface::SELLER_ID => 10,
            'updatedAt' => '2021-11-23 12:02:25',
            'url'=> $url,
        ]);

        $sellerCollectionMock = $this->getSellerCollectionMock($storeId, $sellers);
        $urlHelper = $this->getUrlHelper($url);
        $configHelperMock = $this->getConfigHelper($storeId);
        $itemFactoryMock = $this->getItemFactoryMock();
        $configReaderMock = $this->getConfigReaderMock();

        $seller = $this->objectManager->getObject(Seller::class,
            [
                'sellerRepository'     => $sellerCollectionMock,
                'urlHelper'            => $urlHelper,
                'configHelper'         => $configHelperMock,
                'sitemapItemFactory'   => $itemFactoryMock,
                'categoryConfigReader' => $configReaderMock,
            ]
        );
        $items = $seller->getItems($storeId);
        foreach ($sellers as $index => $seller) {
            self::assertSame($seller->getUpdatedAt(), $items[$index]->getUpdatedAt());
            self::assertSame('daily', $items[$index]->getChangeFrequency());
            self::assertSame('1.0', $items[$index]->getPriority());
            self::assertSame($seller->getImages(), $items[$index]->getImages());
            self::assertSame($seller->getUrl(), $items[$index]->getUrl());
        }
    }

    /**
     * @return MockObject
     */
    private function getConfigReaderMock()
    {
        $configReaderMock = $this->getMockBuilder(CategoryConfigReader::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPriority','getChangeFrequency'])
            ->getMockForAbstractClass();
        $configReaderMock->expects($this->any())
            ->method('getPriority')
            ->willReturn('1.0');
        $configReaderMock->expects($this->any())
            ->method('getChangeFrequency')
            ->willReturn('daily');

        return $configReaderMock;
    }

    /**
     * @param int $storeId
     * @param array $sellers
     * @return SellerRepository|MockObject
     */
    private function getSellerCollectionMock($storeId, $sellers)
    {
        $sellerRepoMock = $this->getMockBuilder(SellerRepository::class)
            ->setMethods(['getAllDisplayedSellers'])
            ->disableOriginalConstructor()
            ->getMock();
        $sellerRepoMock->expects($this->any())
            ->method('getAllDisplayedSellers')->with($storeId)
            ->willReturn($sellers);

        return $sellerRepoMock;
    }

    /**
     * @param int $storeId
     * @return ConfigHelper|MockObject
     */
    private function getConfigHelper($storeId)
    {
        $configHelperMock = $this->getMockBuilder(ConfigHelper::class)
            ->disableOriginalConstructor()->setMethods(['isEnabledOnStorefront'])
            ->getMockForAbstractClass();
        $configHelperMock->expects($this->once())
            ->method('isEnabledOnStorefront')
            ->with($storeId)
            ->willReturn(true);
        return $configHelperMock;
    }

    /**
     * @return UrlHelper|MockObject
     */
    private function getUrlHelper($url)
    {
        $urlHelper = $this->getMockBuilder(UrlHelper::class)
            ->disableOriginalConstructor()->setMethods(['getSellerListingUrl','getSellerUrl'])->getMock();
        $urlHelper->expects($this->any())->method($this->anything())->willReturn($url);
        return $urlHelper;
    }

    /**
     * @return MockObject
     */
    private function getItemFactoryMock()
    {
        $itemFactoryMock = $this->getMockBuilder(SitemapItemInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $itemFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(function ($data) {
                $helper = new ObjectManager($this);
                return $helper->getObject(SitemapItem::class, $data);
            });

        return $itemFactoryMock;
    }

    /**
     * @param int $storeId
     * @return ConfigHelper|MockObject
     */
    private function getDisabledConfigHelper($storeId)
    {
        $configHelperMock = $this->getMockBuilder(ConfigHelper::class)
            ->disableOriginalConstructor()->setMethods(['isEnabledOnStorefront'])
            ->getMockForAbstractClass();
        $configHelperMock->expects($this->once())
            ->method('isEnabledOnStorefront')
            ->with($storeId)
            ->willReturn(false);
        return $configHelperMock;
    }
}
