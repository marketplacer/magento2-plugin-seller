<?php

namespace Marketplacer\Seller\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlFactory as MagentoUrlFactory;
use Magento\Store\Model\Store;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Marketplacer\Seller\Helper\Url;
use Marketplacer\Seller\Model\Seller;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Marketplacer\Seller\Model\SellerRepository;
use Marketplacer\SellerApi\Api\Data\MarketplacerSellerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class UrlTest
 * @package Marketplacer\Seller\Test\Unit\Helper
 */
class UrlTest extends TestCase
{
    private $objectManager;

    /**
     * @var Seller
     */
    private $sellerObject;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManagerMock;

    /**
     * @var MagentoUrlFactory|MockObject
     */
    private $urlFactoryMock;

    /**
     * @var UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @var Url
     */
    private $sellerUrlHelper;

    /**
     * @var SellerRepository|MockObject
     */
    private $sellerRepositoryMock;

    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->storeManagerMock->method('getStore')->willReturn(
            $this->objectManager->getObject(Store::class)->setData('store_id', 1),
        );
        $this->sellerObject = $this->objectManager->getObject(Seller::class);
        $this->sellerObject = $this->sellerObject->setData([
            SellerInterface::STORE_ID => 1,
            MarketplacerSellerInterface::SELLER_ID => 10,
        ]);
        $this->sellerRepositoryMock = $this->getSellerRepositoryMock($this->sellerObject);
        $this->urlFinder = $this->getMockForAbstractClass(UrlFinderInterface::class);

        $this->urlFactoryMock = $this->getMockBuilder(MagentoUrlFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
    }

    public function testGetSellerUrlById()
    {
        $url = 'http://localhost.com/dev/';
        $sellerId = 10;
        $storeId = 1;
        $routeParams['_direct'] = 'marketplacer/seller/view/seller_id/10';
        $routeParams['_query'] = [];
        $routePath = '';
        $urlMock = $this->getMockBuilder(\Magento\Framework\Url::class)
            ->disableOriginalConstructor()
            ->setMethods(['setScope', 'getUrl'])
            ->getMock();
        $urlMock->expects($this->any())
            ->method('setScope')
            ->will($this->returnValue($urlMock));

        $urlMock->expects($this->once())
            ->method('getUrl')
            ->with($routePath, $routeParams)
            ->willReturn($url);
        $this->urlFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($urlMock));
        $this->sellerUrlHelper = $this->objectManager->getObject(Url::class,
        [
            'sellerRepositoryInterface' =>  $this->sellerRepositoryMock,
            'storeManager' => $this->storeManagerMock,
            'urlFinder' => $this->urlFinder,
            'urlFactory' => $this->urlFactoryMock,
        ]
        );
        $sellerUrl = $this->sellerUrlHelper->getSellerUrlById($sellerId, $storeId);
        $this->assertEquals($url, $sellerUrl);
    }

    public function testGetSellerUrlByIdWithoutSellerId()
    {
        $sellerId = 10;
        $storeId = 1;
        $this->sellerObject = $this->sellerObject->setData([
            SellerInterface::STORE_ID => 1,
        ]);
        $this->sellerUrlHelper = $this->objectManager->getObject(Url::class,
            [
                'sellerRepositoryInterface' =>  $this->sellerRepositoryMock,
                'storeManager' => $this->storeManagerMock,
                'urlFinder' => $this->urlFinder,
                'urlFactory' => $this->urlFactoryMock,
            ]
        );
        $sellerUrl = $this->sellerUrlHelper->getSellerUrlById($sellerId, $storeId);
        $this->assertEquals(null, $sellerUrl);
    }

    public function testGetSellerListingUrl()
    {
        $url = 'http://localhost.com/dev/';
        $routeParams['_direct'] = 'marketplacer/seller/index';
        $routeParams['_query'] = [];
        $routePath = '';
        $urlMock = $this->getMockBuilder(\Magento\Framework\Url::class)
            ->disableOriginalConstructor()
            ->setMethods(['setScope', 'getUrl'])
            ->getMock();
        $urlMock->expects($this->once())
            ->method('setScope')
            ->will($this->returnValue($urlMock));

        $urlMock->expects($this->once())
            ->method('getUrl')
            ->with($routePath, $routeParams)
            ->willReturn($url);
        $this->urlFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($urlMock));
        $this->sellerUrlHelper = $this->objectManager->getObject(Url::class,
            [
                'sellerRepositoryInterface' =>  $this->sellerRepositoryMock,
                'storeManager' => $this->storeManagerMock,
                'urlFinder' => $this->urlFinder,
                'urlFactory' => $this->urlFactoryMock,
            ]
        );
        $sellerUrl = $this->sellerUrlHelper->getSellerListingUrl();
        $this->assertEquals($url, $sellerUrl);
    }

    public function testGetSellerUrl()
    {
        $url = 'http://localhost.com/dev/';
        $routeParams['_direct'] = 'marketplacer/seller/view/seller_id/10';
        $routeParams['_query'] = [];
        $routePath = '';
        $urlMock = $this->getMockBuilder(\Magento\Framework\Url::class)
            ->disableOriginalConstructor()
            ->setMethods(['setScope', 'getUrl'])
            ->getMock();
        $urlMock->expects($this->any())
            ->method('setScope')
            ->will($this->returnValue($urlMock));

        $urlMock->expects($this->once())
            ->method('getUrl')
            ->with($routePath, $routeParams)
            ->willReturn($url);
        $this->urlFactoryMock->expects($this->any())
            ->method('create')
            ->will($this->returnValue($urlMock));
        $this->sellerUrlHelper = $this->objectManager->getObject(Url::class,
            [
                'sellerRepositoryInterface' =>  $this->sellerRepositoryMock,
                'storeManager' => $this->storeManagerMock,
                'urlFinder' => $this->urlFinder,
                'urlFactory' => $this->urlFactoryMock,
            ]
        );
        $sellerUrl = $this->sellerUrlHelper->getSellerUrl($this->sellerObject,[]);
        $this->assertEquals($url, $sellerUrl);
    }

    /**
     * @return SellerRepository|MockObject
     */
    private function getSellerRepositoryMock($seller)
    {
        $sellerRepoMock = $this->getMockBuilder(SellerRepository::class)
            ->setMethods(['getById'])
            ->disableOriginalConstructor()
            ->getMock();
        $sellerRepoMock->expects($this->any())
            ->method('getById')
            ->willReturn($seller);

        return $sellerRepoMock;
    }
}
