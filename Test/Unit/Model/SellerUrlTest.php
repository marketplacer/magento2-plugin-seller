<?php

namespace Marketplacer\Seller\Test\Unit\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Helper\Url as UrlHelper;
use Marketplacer\Seller\Model\SellerUrl;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SellerUrlTest extends TestCase
{
    /**
     * @var UrlHelper|MockObject
     */
    protected $urlHelperMock;

    /**
     * @var SellerUrl
     */
    private $sellerUrl;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->urlHelperMock = $this->createMock(\Marketplacer\Seller\Helper\Url::class);

        $this->sellerUrl = $this->objectManager->getObject(
            \Marketplacer\Seller\Model\SellerUrl::class,
            [
                'urlHelper'                  => $this->urlHelperMock,
            ]
        );
    }

    public function testGetSellerUrlExisting()
    {
        $sellerId = 5;
        $storeId = 1;
        $url = 'https://test-seller.url';

        /** @var \Marketplacer\Seller\Model\Seller $seller */
        $seller = $this->objectManager->getObject(\Marketplacer\Seller\Model\Seller::class);
        $seller->setData(
            [
                SellerInterface::SELLER_ID => $sellerId,
                SellerInterface::STORE_ID => $storeId,
            ]
        );
        $this->urlHelperMock->method('getSellerUrlById')->willReturn($url);

        $this->assertEquals($url, $this->sellerUrl->getSellerUrl($seller));
    }

    public function testGetSellerUrlMissing()
    {
        $sellerId = 5;
        $storeId = 1;
        $url = 'https://test-seller.url';

        /** @var \Marketplacer\Seller\Model\Seller $seller */
        $seller = $this->objectManager->getObject(\Marketplacer\Seller\Model\Seller::class);
        $seller->setData(
            [
                SellerInterface::SELLER_ID => $sellerId,
                SellerInterface::STORE_ID => $storeId,
            ]
        );
        $this->urlHelperMock->method('getSellerUrlById')->willThrowException(new NoSuchEntityException());

        $this->assertNull($this->sellerUrl->getSellerUrl($seller));
    }
}
