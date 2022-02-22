<?php

namespace Marketplacer\Seller\Test\Unit\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Helper\Data;
use Marketplacer\Seller\Helper\Url as UrlHelper;
use Marketplacer\Seller\Model\Seller;
use Marketplacer\Seller\Model\SellerRepository;
use Marketplacer\SellerApi\Api\Data\MarketplacerSellerInterface;
use Marketplacer\SellerApi\Api\SellerAttributeRetrieverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class DataTest
 * @package Marketplacer\Seller\Test\Unit\Helper
 */
class DataTest extends TestCase
{
    private $objectManager;
    /**
     * @var Product
     */
    protected $product;

    /**
     * @var Seller
     */
    private $sellerObject;

    /**
     * @var SellerAttributeRetrieverInterface
     */
    private $sellerAttributeRetrieverMock;

    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->sellerObject = $this->objectManager->getObject(Seller::class);
        $this->sellerAttributeRetrieverMock = $this->getMockBuilder(
            SellerAttributeRetrieverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGetSellerUrl()
    {
        $url = 'http://localhost.com/dev/';
        $this->sellerObject = $this->sellerObject->setData([
            SellerInterface::STORE_ID => 1,
            MarketplacerSellerInterface::SELLER_ID => 10,
        ]);
        $sellerRepositoryMock = $this->getSellerRepositoryMock(
            $this->sellerObject[SellerInterface::STORE_ID],
            $this->sellerObject
        );
        $sellerRepositoryMock->method('getById')->willReturn($this->sellerObject);
        $urlHelper = $this->getUrlHelper($url);

        $seller = $this->objectManager->getObject(Data::class,
            [
                'sellerAttributeRetriever' => $this->sellerAttributeRetrieverMock,
                'sellerRepository'         => $sellerRepositoryMock,
                'urlHelper'                => $urlHelper,
            ]
        );
        $sellerUrl = $seller->getSellerUrl($this->sellerObject);
        $this->assertEquals($url, $sellerUrl);
    }

    public function testGetSellerEmptyUrl()
    {
        $url = '';
        $this->sellerObject = $this->sellerObject->setData([
        ]);
        $sellerRepositoryMock = $this->getSellerRepositoryMock(
            $this->sellerObject[SellerInterface::STORE_ID],
            $this->sellerObject
        );
        $urlHelper = $this->getUrlHelper($url);

        $seller = $this->objectManager->getObject(Data::class,
            [
                'sellerAttributeRetriever' => $this->sellerAttributeRetrieverMock,
                'sellerRepository'         => $sellerRepositoryMock,
                'urlHelper'                => $urlHelper,
            ]
        );
        $sellerUrl = $seller->getSellerUrl($this->sellerObject);
        $this->assertEquals('', $sellerUrl);
    }

    public function testGetSellerByProduct()
    {
        $this->sellerObject = $this->sellerObject->setData([
            SellerInterface::STORE_ID => 1,
            MarketplacerSellerInterface::SELLER_ID => 10,
        ]);
        $sellerRepoMock = $this->getMockBuilder(SellerRepository::class)
            ->setMethods(['getById'])
            ->disableOriginalConstructor()
            ->getMock();
        $sellerRepoMock->expects($this->once())
            ->method('getById')
            ->willReturn($this->sellerObject);

        $this->sellerAttributeRetrieverMock = $this->getMockBuilder(SellerAttributeRetrieverInterface::class)
            ->setMethods(['getAttributeCode'])
            ->getMockForAbstractClass();
        $this->sellerAttributeRetrieverMock->expects($this->once())->method('getAttributeCode')
            ->willReturn('seller_id');
        $urlHelper = $this->getMockBuilder(UrlHelper::class)
            ->disableOriginalConstructor()->getMock();

        $seller = $this->objectManager->getObject(Data::class,
            [
                'sellerAttributeRetriever'  => $this->sellerAttributeRetrieverMock,
                'sellerRepository'          => $sellerRepoMock,
                'urlHelper'                 => $urlHelper,
            ]
        );
        $this->product = $this->createMock(Product::class);
        $this->product->method('getData')->willReturn(10);
        $this->product->method('getStoreId')->willReturn(1);
        $this->product->setData('seller_id', 10);

        $sellerInfo = $seller->getSellerByProduct($this->product);
        $this->assertEquals($this->sellerObject->getData(), $sellerInfo->getData());
    }

    public function testGetSellerWithEmptySellerId()
    {
        $sellerRepoMock = $this->getMockBuilder(SellerRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sellerAttributeRetrieverMock = $this->getMockBuilder(SellerAttributeRetrieverInterface::class)
            ->setMethods(['getAttributeCode'])
            ->getMockForAbstractClass();
        $this->sellerAttributeRetrieverMock->expects($this->once())->method('getAttributeCode')
            ->willReturn('seller_id');
        $urlHelper = $this->getMockBuilder(UrlHelper::class)
            ->disableOriginalConstructor()->getMock();

        $seller = $this->objectManager->getObject(Data::class,
            [
                'sellerAttributeRetriever'  => $this->sellerAttributeRetrieverMock,
                'sellerRepository'          => $sellerRepoMock,
                'urlHelper'                 => $urlHelper,
            ]
        );
        $this->product = $this->createMock(Product::class);
        $this->product->method('getData')->willReturn(null);
        $sellerInfo = $seller->getSellerByProduct($this->product);
        $this->assertEquals(null, $sellerInfo);
    }

    /**
     * @return UrlHelper|MockObject
     */
    private function getUrlHelper($url)
    {
        $urlHelper = $this->getMockBuilder(UrlHelper::class)
            ->disableOriginalConstructor()->setMethods(['getSellerUrlById'])->getMock();
        $urlHelper->expects($this->once())->method($this->anything())->willReturn($url);
        return $urlHelper;
    }

    /**
     * @param int $storeId
     * @param $seller
     * @return SellerRepository|MockObject
     */
    private function getSellerRepositoryMock($storeId, $seller)
    {
        $productStoreId = 1;
        $sellerRepoMock = $this->getMockBuilder(SellerRepository::class)
            ->setMethods(['getById'])
            ->disableOriginalConstructor()
            ->getMock();
        $sellerRepoMock->expects($this->any())
            ->method('getById')->with($storeId, $productStoreId)
            ->willReturn($seller);

        return $sellerRepoMock;
    }
}
