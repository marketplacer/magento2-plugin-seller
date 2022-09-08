<?php

namespace Marketplacer\Seller\Test\Unit\Model\UrlProcessor;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlFactory as MagentoUrlFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\StorageInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Api\SellerRepositoryInterface;
use Marketplacer\Seller\Helper\Config as ConfigHelper;
use Marketplacer\Seller\Model\Seller;
use Marketplacer\Seller\Model\UrlProcessor\SellerProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SellerProcessorTest extends TestCase
{
    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var UrlRewriteFactory|MockObject
     */
    private $urlRewriteFactoryMock;

    /**
     * @var StorageInterface|MockObject
     */
    private $storageMock;

    /**
     * @var MagentoUrlFactory|MockObject
     */
    private $urlFactoryMock;

    /**
     * @var ConfigHelper|MockObject
     */
    private $configHelperMock;

    /**
     * @var SellerRepositoryInterface|MockObject
     */
    private $sellerRepositoryMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var SellerProcessor
     */
    private $sellerProcessor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->storeManagerMock->method('getStores')->willReturn([
            1 => $this->objectManager->getObject(\Magento\Store\Model\Store::class)->setData('store_id', 1),
            2 => $this->objectManager->getObject(\Magento\Store\Model\Store::class)->setData('store_id', 2),
        ]);

        $this->storageMock = $this->createMock(\Magento\UrlRewrite\Model\StorageInterface::class);
        $this->sellerRepositoryMock = $this->createMock(\Marketplacer\Seller\Model\SellerRepository::class);;

        $this->configHelperMock = $this->createMock(\Marketplacer\Seller\Helper\Config::class);;
        $this->configHelperMock->method('getBaseUrlKey')->withConsecutive([1], [2])->willReturnOnConsecutiveCalls('sellers_1', 'sellers_2');
        $this->configHelperMock->method('getUrlSuffix')->withConsecutive([1], [2])->willReturnOnConsecutiveCalls('-1.html', '-2.html');

        $this->urlRewriteFactoryMock = $this->createMock(\Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory::class);;
        $this->urlRewriteFactoryMock->method('create')->willReturnCallback(function ($data) {
            return $this->objectManager->getObject(UrlRewrite::class, $data);
        });

        $this->sellerProcessor = $this->objectManager->getObject(
            SellerProcessor::class,
            [
                'storeManager'      => $this->storeManagerMock,
                'urlRewriteFactory' => $this->urlRewriteFactoryMock,
                'storage'           => $this->storageMock,
                'urlFactory'        => $this->urlFactoryMock,
                'configHelper'      => $this->configHelperMock,
                'sellerRepository'  => $this->sellerRepositoryMock,
            ]
        );
    }

    public function testProcessSellerUrlRewrites()
    {
        $sellerId = '5';
        /** @var \Marketplacer\Seller\Model\Seller $seller */
        $seller = $this->objectManager->getObject(Seller::class);
        $seller->setData([
            SellerInterface::SELLER_ID => $sellerId,
            SellerInterface::URL_KEY   => 'test-seller'
        ]);

        $this->sellerRepositoryMock
            ->method('getAllStoreRecordsById')
            ->willReturn([$seller]);

        /** @var UrlRewrite $urlRewrite1 */
        $urlRewrite1 = $this->objectManager->getObject(
            UrlRewrite::class,
            [
                'data' => [
                    UrlRewrite::ENTITY_TYPE => SellerProcessor::URL_ENTITY_TYPE,
                    UrlRewrite::ENTITY_ID => $sellerId,
                    UrlRewrite::REQUEST_PATH => 'sellers_1/test-seller-1.html',
                    UrlRewrite::TARGET_PATH => sprintf(SellerProcessor::SELLER_VIEW_TARGET_PATH_PATTERN, $sellerId),
                    UrlRewrite::STORE_ID => 1
                ]
            ]
        );

        /** @var UrlRewrite $urlRewrite2 */
        $urlRewrite2 = $this->objectManager->getObject(
            UrlRewrite::class,
            [
                'data' => [
                    UrlRewrite::ENTITY_TYPE => SellerProcessor::URL_ENTITY_TYPE,
                    UrlRewrite::ENTITY_ID => $sellerId,
                    UrlRewrite::REQUEST_PATH => 'sellers_2/test-seller-2.html',
                    UrlRewrite::TARGET_PATH => sprintf(SellerProcessor::SELLER_VIEW_TARGET_PATH_PATTERN, $sellerId),
                    UrlRewrite::STORE_ID => 2
                ]
            ]
        );

        $this->storageMock->method('replace')->with([$urlRewrite1, $urlRewrite2]);
        
        static::assertEquals(true, $this->sellerProcessor->processSellerUrlRewrites($seller));
    }

    public function testProcessSellerUrlRewritesWithMissingSeller()
    {
        $sellerId = '5';
        /** @var \Marketplacer\Seller\Model\Seller $seller */
        $seller = $this->objectManager->getObject(Seller::class);
        $seller->setData([
            SellerInterface::SELLER_ID => $sellerId,
            SellerInterface::URL_KEY   => 'test-seller'
        ]);

        $this->sellerRepositoryMock
            ->method('getAllStoreRecordsById')
            ->willThrowException(new NoSuchEntityException());

        $this->expectException(NoSuchEntityException::class);

        $this->sellerProcessor->processSellerUrlRewrites($seller);
    }

    public function testDeleteUrlRewrites()
    {
        $sellerId = '5';
        $storeId = '1';

        /** @var \Marketplacer\Seller\Model\Seller $seller */
        $seller = $this->objectManager->getObject(Seller::class);
        $seller->setData([
            SellerInterface::SELLER_ID => $sellerId,
            SellerInterface::URL_KEY   => 'test-seller'
        ]);

        $deletionFilterData = [
            UrlRewrite::ENTITY_ID   => $sellerId,
            UrlRewrite::ENTITY_TYPE => SellerProcessor::URL_ENTITY_TYPE,
            UrlRewrite::STORE_ID => $storeId,
        ];
        $this->storageMock->method('deleteByData')->with($deletionFilterData);

        static::assertEquals(true, $this->sellerProcessor->deleteUrlRewrites($seller, $storeId));
    }

    public function testProcessSellerListingUrlRewrites()
    {
        /** @var UrlRewrite $urlRewrite1 */
        $urlRewrite1 = $this->objectManager->getObject(
            UrlRewrite::class,
            [
                'data' => [
                    UrlRewrite::ENTITY_TYPE => SellerProcessor::URL_ENTITY_TYPE,
                    UrlRewrite::ENTITY_ID => 0,
                    UrlRewrite::REQUEST_PATH => 'sellers_1-1.html',
                    UrlRewrite::TARGET_PATH => SellerProcessor::SELLER_LIST_TARGET_PATH_PATTERN,
                    UrlRewrite::STORE_ID => 1
                ]
            ]
        );

        /** @var UrlRewrite $urlRewrite2 */
        $urlRewrite2 = $this->objectManager->getObject(
            UrlRewrite::class,
            [
                'data' => [
                    UrlRewrite::ENTITY_TYPE => SellerProcessor::URL_ENTITY_TYPE,
                    UrlRewrite::ENTITY_ID => 0,
                    UrlRewrite::REQUEST_PATH => 'sellers_2-2.html',
                    UrlRewrite::TARGET_PATH => SellerProcessor::SELLER_LIST_TARGET_PATH_PATTERN,
                    UrlRewrite::STORE_ID => 2
                ]
            ]
        );

        $this->storageMock->method('replace')->with([$urlRewrite1, $urlRewrite2]);

        static::assertEquals(true, $this->sellerProcessor->processSellerListingUrlRewrites());
    }
}
