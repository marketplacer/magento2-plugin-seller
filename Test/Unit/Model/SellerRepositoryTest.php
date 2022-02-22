<?php

namespace Marketplacer\Seller\Test\Unit\Model;

use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Marketplacer\Base\Model\Attribute\AttributeOptionHandler;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Api\Data\SellerInterfaceFactory;
use Marketplacer\SellerApi\Api\Data\MarketplacerSellerSearchResultsInterfaceFactory;
use Marketplacer\Seller\Model\ResourceModel\Seller\Collection;
use Marketplacer\Seller\Model\ResourceModel\Seller\CollectionFactory as SellerCollectionFactory;
use Marketplacer\Seller\Model\Seller;
use Marketplacer\Seller\Model\Seller\SellerDataToOptionSetter;
use Marketplacer\Seller\Model\Seller\Validator as SellerValidator;
use Marketplacer\Seller\Model\SellerRepository;
use Marketplacer\SellerApi\Api\SellerAttributeRetrieverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SellerRepositoryTest extends TestCase
{
    /**
     * @var \Marketplacer\Seller\Model\ResourceModel\Seller|MockObject
     */
    private $sellerResourceMock;

    /**
     * @var SellerAttributeRetrieverInterface|MockObject
     */
    private $sellerAttributeRetrieverMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var SellerInterfaceFactory|MockObject
     */
    private $sellerFactoryMock;

    /**
     * @var SellerValidator|MockObject
     */
    private $sellerValidatorMock;

    /**
     * @var AttributeOptionInterfaceFactory|MockObject
     */
    private $attributeOptionFactoryMock;

    /**
     * @var AttributeOptionHandler|MockObject
     */
    private $attributeOptionHandlerMock;

    /**
     * @var SellerCollectionFactory |MockObject
     */
    private $sellerCollectionFactoryMock;

    /**
     * @var MarketplacerSellerSearchResultsInterfaceFactory | MockObject
     */
    private $searchResultsFactoryMock;

    /**
     * @var CollectionProcessorInterface|MockObject
     */
    private $collectionProcessorMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Collection|MockObject
     */
    private $collectionMock;

    /**
     * @var SellerRepository|MockObject
     */
    private $repository;

    /**
     * @var Seller|mixed|MockObject
     */
    private $sellerMock;

    /**
     * @var SellerDataToOptionSetter
     */
    private $sellerDataToOptionSetter;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->sellerDataToOptionSetter = $this->objectManager->getObject(
            \Marketplacer\Seller\Model\Seller\SellerDataToOptionSetter::class,
            [
                'attributeOptionLabelFactory' => $this->createConfiguredMock(
                    \Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory::class,
                    [
                        'create' => $this->objectManager->getObject(\Magento\Eav\Model\Entity\Attribute\OptionLabel::class)
                    ]
                )
            ]
        );

        $this->sellerResourceMock = $this->createMock(\Marketplacer\Seller\Model\ResourceModel\Seller::class);
        $this->sellerAttributeRetrieverMock = $this->createMock(SellerAttributeRetrieverInterface::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->sellerFactoryMock = $this->createMock(SellerInterfaceFactory::class);
        $this->sellerValidatorMock = $this->createMock(SellerValidator::class);
        $this->attributeOptionFactoryMock = $this->createMock(AttributeOptionInterfaceFactory::class);
        $this->attributeOptionHandlerMock = $this->createMock(AttributeOptionHandler::class);
        $this->sellerCollectionFactoryMock = $this->createMock(SellerCollectionFactory::class);
        $this->searchResultsFactoryMock = $this->createMock(MarketplacerSellerSearchResultsInterfaceFactory::class);
        $this->collectionProcessorMock = $this->createMock(CollectionProcessorInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->collectionMock = $this->createPartialMock(
            Collection::class,
            [
                'addSellerIdToFilter',
                'addStatusActiveToFilter',
                'addStoreIdToFilter',
                'setOrder',
                'setCurPage',
                'setPageSize',
                'getFirstItem',
                'getItems',
                'getFlag',
                'getIterator',
                'getSize'
            ]
        );

        $this->sellerMock = $this->createMock(Seller::class);

        $this->repository = $this->objectManager->getObject(
            SellerRepository::class,
            [
                'sellerResource'             => $this->sellerResourceMock,
                'sellerFactory'              => $this->sellerFactoryMock,
                'attributeOptionHandler'     => $this->attributeOptionHandlerMock,
                'sellerAttributeRetriever'   => $this->sellerAttributeRetrieverMock,
                'storeManager'               => $this->storeManagerMock,
                'attributeOptionFactory'     => $this->attributeOptionFactoryMock,
                'sellerCollectionFactory'    => $this->sellerCollectionFactoryMock,
                'sellerValidator'            => $this->sellerValidatorMock,
                'sellerDataToOptionSetter'   => $this->sellerDataToOptionSetter,
                'sellerSearchResultsFactory' => $this->searchResultsFactoryMock,
                'collectionProcessor'        => $this->collectionProcessorMock,
                'logger'                     => $this->loggerMock,
            ]
        );
    }

    public function testGetStoreViewRecordById(/** $sellerData, $storeId*/)
    {
        $sellerId = 5;
        $requestedStoreId = 1;
        $rowId = 5;

        $this->prepareMocksToTestGetById($requestedStoreId);

        $this->sellerMock
            ->expects($this->once())
            ->method('getStoreId')
            ->willReturn($requestedStoreId);
        $this->sellerMock
            ->expects($this->once())
            ->method('getRowId')
            ->willReturn($rowId);
        $this->sellerMock
            ->expects($this->never())
            ->method('__call');
        $this->sellerMock
            ->expects($this->never())
            ->method('setStoreId');

        $this->collectionMock
            ->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($this->sellerMock);

        $this->repository->getById($sellerId, $requestedStoreId);
    }

    public function testGetGlobalStoreViewRecordById(/** $sellerData, $storeId*/)
    {
        $sellerId = 5;
        $requestedStoreId = 1;
        $rowId = 5;

        $this->prepareMocksToTestGetById($requestedStoreId);

        $this->sellerMock
            ->expects($this->once())
            ->method('getStoreId')
            ->willReturn(Store::DEFAULT_STORE_ID);
        $this->sellerMock
            ->expects($this->once())
            ->method('getRowId')
            ->willReturn($rowId);
        $this->sellerMock
            ->expects($this->once())
            ->method('__call')
            ->with('unsRowId')
            ->willReturnSelf();
        $this->sellerMock
            ->expects($this->once())
            ->method('setStoreId')
            ->willReturn($rowId)
            ->with($requestedStoreId);

        $this->collectionMock
            ->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($this->sellerMock);

        $this->repository->getById($sellerId, $requestedStoreId);
    }

    public function testGetMissingSellerById(/** $sellerData, $storeId*/)
    {
        $sellerId = 5;
        $requestedStoreId = 1;
        $rowId = 5;

        $this->prepareMocksToTestGetById($requestedStoreId);

        $this->sellerMock
            ->expects($this->never())
            ->method('getStoreId')
            ->willReturn(Store::DEFAULT_STORE_ID);
        $this->sellerMock
            ->expects($this->once())
            ->method('getRowId')
            ->willReturn(null);

        $this->collectionMock
            ->expects($this->once())
            ->method('getFirstItem')
            ->willReturn($this->sellerMock);

        $this->expectException(NoSuchEntityException::class);

        $this->repository->getById($sellerId, $requestedStoreId);
    }

    /**
     * @param $storeId
     * @return void
     */
    private function prepareMocksToTestGetById($storeId = null)
    {
        if (null !== $storeId) {
            $this->storeManagerMock
                ->expects($this->never())
                ->method('getStore');
        } else {
            $store = $this->objectManager->getObject(Store::class);
            $store->setId($storeId);

            $this->storeManagerMock
                ->expects($this->once())
                ->method('getStore')
                ->willReturn($store);
        }

        $this->sellerCollectionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->collectionMock);

        $this->collectionMock
            ->expects($this->once())
            ->method('addSellerIdToFilter')
            ->willReturnSelf();
        $this->collectionMock
            ->expects($this->once())
            ->method('addStoreIdToFilter')
            ->willReturnSelf();

        $this->collectionMock
            ->expects($this->once())
            ->method('setOrder')
            ->willReturnSelf();

        $this->collectionMock
            ->expects($this->once())
            ->method('setCurPage')
            ->willReturnSelf();
        $this->collectionMock
            ->expects($this->once())
            ->method('setCurPage')
            ->willReturnSelf();
        $this->collectionMock
            ->expects($this->once())
            ->method('setPageSize')
            ->willReturnSelf();
    }

    public function testGetAllDisplayedSellers()
    {
        $requestedStoreId = 1;

        $this->prepareMocksToTestSellerCollections($requestedStoreId);

        $this->collectionMock
            ->expects($this->once())
            ->method('addStoreIdToFilter')
            ->willReturnSelf();
        $this->collectionMock
            ->expects($this->once())
            ->method('addStatusActiveToFilter')
            ->willReturnSelf();

        $this->collectionMock
            ->expects($this->exactly(2))
            ->method('setOrder')
            ->willReturnSelf();

        $this->collectionMock
            ->expects($this->once())
            ->method('getItems')
            ->willReturn([]);

        $this->repository->getAllDisplayedSellers($requestedStoreId);
    }

    public function testGetByIds()
    {
        $requestedStoreId = 1;

        $this->prepareMocksToTestSellerCollections($requestedStoreId);

        $this->collectionMock
            ->expects($this->once())
            ->method('addSellerIdToFilter')
            ->willReturnSelf();
        $this->collectionMock
            ->expects($this->once())
            ->method('addStoreIdToFilter')
            ->willReturnSelf();

        $this->collectionMock
            ->expects($this->exactly(2))
            ->method('setOrder')
            ->willReturnSelf();

        $this->collectionMock
            ->expects($this->once())
            ->method('getItems')
            ->willReturn([]);

        $this->repository->getByIds([1,2], $requestedStoreId);
    }

    /**
     * @param $storeId
     * @return void
     */
    private function prepareMocksToTestSellerCollections($storeId = null)
    {
        $store = $this->objectManager->getObject(Store::class);
        $store->setId($storeId);

        if ($storeId) {
            $this->storeManagerMock
                ->expects($this->never())
                ->method('getStore');
        } else {
            $this->storeManagerMock
                ->expects($this->once())
                ->method('getStore')
                ->willReturn($store);
        }

        $this->sellerCollectionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->collectionMock);
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function testGetAllSellerIds()
    {
        $ids = [1, 3, 5, 10];

        $this->sellerResourceMock
            ->expects($this->once())
            ->method('getAllSellerIds')
            ->willReturn($ids);

        $this->assertSame($ids, $this->repository->getAllSellerIds());
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function testGetAllStoreRecordsByExistingId()
    {
        $sellerId = 5;

        $this->storeManagerMock
            ->expects($this->once())
            ->method('getStores')
            ->with(true, false)
            ->willReturn(
                [
                    0 => $this->objectManager->getObject(Store::class)->setId(0),
                    1 => $this->objectManager->getObject(Store::class)->setId(1),
                ]
            );

        $this->sellerCollectionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->collectionMock);

        $this->collectionMock
            ->expects($this->once())
            ->method('addSellerIdToFilter')
            ->willReturnSelf();
        $this->collectionMock
            ->expects($this->once())
            ->method('addStoreIdToFilter')
            ->willReturnSelf();

        $this->collectionMock
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(
                new \ArrayIterator(
                    [
                        0 => $this->objectManager->getObject(Seller::class)->setStoreId(0),
                        1 => $this->objectManager->getObject(Seller::class)->setStoreId(1),
                    ]
                )
            );

        $this->repository->getAllStoreRecordsById($sellerId);
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function testGetAllStoreRecordsByMissingId()
    {
        $sellerId = 5;

        $this->storeManagerMock
            ->expects($this->once())
            ->method('getStores')
            ->with(true, false)
            ->willReturn(
                [
                    0 => $this->objectManager->getObject(Store::class)->setId(0),
                    1 => $this->objectManager->getObject(Store::class)->setId(1),
                ]
            );

        $this->sellerCollectionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->collectionMock);

        $this->collectionMock
            ->expects($this->once())
            ->method('addSellerIdToFilter')
            ->willReturnSelf();

        $this->collectionMock
            ->expects($this->once())
            ->method('addStoreIdToFilter')
            ->willReturnSelf();

        $this->collectionMock
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([]));

        $this->expectException(NoSuchEntityException::class);

        $this->repository->getAllStoreRecordsById($sellerId);
    }


    public function testGetList()
    {
        /** @var \Magento\Framework\Api\SearchCriteria $searchCriteria */
        $searchCriteria = $this->objectManager->getObject(\Magento\Framework\Api\SearchCriteria::class);
        $searchCriteria->setFilterGroups([]);

        $count = 2;
        $items = [
            1 => $this->objectManager->getObject(Seller::class),
            5 => $this->objectManager->getObject(Seller::class),
        ];

        $this->storeManagerMock
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($this->objectManager->getObject(Store::class)->setId(1));

        $this->searchResultsFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->objectManager->getObject(\Marketplacer\SellerApi\Model\MarketplacerSellerSearchResults::class));

        $this->sellerCollectionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->collectionMock);

        $this->collectionMock
            ->expects($this->once())
            ->method('getItems')
            ->willReturn($items);
        $this->collectionMock
            ->expects($this->once())
            ->method('getSize')
            ->willReturn($count);

        $searchResults = $this->repository->getList($searchCriteria);

        $this->assertEquals($count, $searchResults->getTotalCount());
        $this->assertEquals($searchCriteria, $searchResults->getSearchCriteria());
        $this->assertEquals($items, $searchResults->getSellers());
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function testDeleteExistingById()
    {
        $sellerId = 5;
        $optionId = 5;
        $requestedStoreId = Store::DEFAULT_STORE_ID;
        $rowId = 5;

        $this->prepareMocksToTestGetById($requestedStoreId);

        $this->sellerMock->method('getStoreId')->willReturn($requestedStoreId);
        $this->sellerMock->method('getRowId')->willReturn($rowId);
        $this->sellerMock->method('getOptionId')->willReturn($optionId);
        $this->collectionMock->method('getFirstItem')->willReturn($this->sellerMock);

        $attributeMock = $this->objectManager->getObject(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $this->sellerAttributeRetrieverMock
            ->expects($this->once())
            ->method('getAttribute')
            ->willReturn($attributeMock);

        $this->attributeOptionHandlerMock
            ->expects($this->once())
            ->method('getAttributeOptionById')
            ->willReturn($this->objectManager->getObject(\Magento\Eav\Model\Entity\Attribute\Option::class));
        $this->attributeOptionHandlerMock
            ->expects($this->once())
            ->method('deleteOptionById');

        $this->sellerResourceMock
            ->expects($this->once())
            ->method('delete');

        $this->assertTrue($this->repository->deleteById($sellerId));
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function testDeleteMissingById()
    {
        $sellerId = 5;
        $requestedStoreId = Store::DEFAULT_STORE_ID;

        $this->prepareMocksToTestGetById($requestedStoreId);

        $this->sellerMock->method('getRowId')->willReturn(null);
        $this->collectionMock->method('getFirstItem')->willReturn($this->sellerMock);

        $attributeMock = $this->objectManager->getObject(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $this->sellerAttributeRetrieverMock
            ->expects($this->never())
            ->method('getAttribute')
            ->willReturn($attributeMock);

        $this->attributeOptionHandlerMock
            ->expects($this->never())
            ->method('isAttributeOptionIdExist');
        $this->attributeOptionHandlerMock
            ->expects($this->never())
            ->method('deleteOptionById');

        $this->sellerResourceMock
            ->expects($this->never())
            ->method('delete');

        $this->expectException(NoSuchEntityException::class);

        $this->repository->deleteById($sellerId);
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function testIsSellerExistingOptionExist()
    {
        $sellerId = 5;

        $attributeMock = $this->objectManager->getObject(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $this->sellerAttributeRetrieverMock
            ->expects($this->once())
            ->method('getAttribute')
            ->willReturn($attributeMock);

        $this->attributeOptionHandlerMock
            ->expects($this->once())
            ->method('isAttributeOptionIdExist')
            ->willReturn(true);

        $this->assertTrue($this->repository->isSellerOptionExist($sellerId));
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function testIsSellerMissingOptionExist()
    {
        $sellerId = 5;

        $attributeMock = $this->objectManager->getObject(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $this->sellerAttributeRetrieverMock
            ->expects($this->once())
            ->method('getAttribute')
            ->willReturn($attributeMock);

        $this->attributeOptionHandlerMock
            ->expects($this->once())
            ->method('isAttributeOptionIdExist')
            ->willReturn(false);

        $this->assertFalse($this->repository->isSellerOptionExist($sellerId));
    }

    public function testSaveCreateNewValidSellerOnDefaultLevel()
    {
        $sellerId = 5; // $sellerId and $optionId must be equal with current implementation
        $optionId = 5;
        $requestedStoreId = Store::DEFAULT_STORE_ID;
        $rowId = 10;
        $name = 'Test Seller';

        /** @var \Marketplacer\Seller\Model\Seller $seller */
        $seller = $this->objectManager->getObject(\Marketplacer\Seller\Model\Seller::class);
        $seller
            ->setOptionId(null)
            ->setRowId(null)
            ->setSellerId(null)
            ->setName($name)
            ->setStoreId($requestedStoreId);

        $this->sellerValidatorMock->method('validate')->willReturn(null);

        $attributeMock = $this->objectManager->getObject(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $this->sellerAttributeRetrieverMock->method('getAttribute')->willReturn($attributeMock);

        /** @var \Magento\Eav\Model\Entity\Attribute\Option $option */
        $option = $this->objectManager->getObject(\Magento\Eav\Model\Entity\Attribute\Option::class);
        $option->setIsDefault(0)->setSortOrder(0)->setValue(0);
        $this->attributeOptionHandlerMock->method('createAttributeOption')->willReturn($option);

        $this->attributeOptionHandlerMock->method('isAdminLabelUnique')->willReturn(true);

        $this->attributeOptionHandlerMock->method('saveAttributeOption')->willReturn($option);
        $option->setValue($optionId);

        $this->sellerResourceMock->expects($this->once())->method('save')->willReturnSelf();

        $seller->setRowId($rowId);

        $this->repository->save($seller);

        $this->assertEquals($optionId, $seller->getOptionId());
        $this->assertEquals($rowId, $seller->getRowId());
        $this->assertEquals($sellerId, $seller->getSellerId());
        $this->assertEquals('test-seller', $seller->getUrlKey());

    }

    public function testSaveCreateNewValidSellerOnStoreLevel()
    {
        $sellerId = 5;
        $optionId = 5;
        $requestedStoreId = 1;
        $rowId = 10;
        $name = 'Test Seller';

        /** @var \Marketplacer\Seller\Model\Seller $seller */
        $seller = $this->objectManager->getObject(\Marketplacer\Seller\Model\Seller::class);
        $seller
            ->setOptionId(null)
            ->setRowId(null)
            ->setSellerId(null)
            ->setName($name)
            ->setStoreId($requestedStoreId);

        $this->sellerValidatorMock->method('validate')->willReturn(null);

        $attributeMock = $this->objectManager->getObject(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $this->sellerAttributeRetrieverMock->method('getAttribute')->willReturn($attributeMock);

        /** @var \Magento\Eav\Model\Entity\Attribute\Option $option */
        $option = $this->objectManager->getObject(\Magento\Eav\Model\Entity\Attribute\Option::class);
        $option->setIsDefault(0)->setSortOrder(0)->setValue(0);
        $this->attributeOptionHandlerMock->method('createAttributeOption')->willReturn($option);

        $this->attributeOptionHandlerMock->method('isAdminLabelUnique')->willReturn(true);

        $this->attributeOptionHandlerMock->method('saveAttributeOption')->willReturn($option);
        $option->setValue($optionId);

        $this->sellerResourceMock->expects($this->exactly(2))->method('save')->willReturnSelf();
        $seller->setRowId($rowId);

        $this->repository->save($seller);

        $this->assertEquals($optionId, $seller->getOptionId());
        $this->assertEquals($sellerId, $seller->getSellerId());
        $this->assertEquals('test-seller', $seller->getUrlKey());
    }

    public function testSaveCreateSellerWithExistingOptionName()
    {
        $sellerId = 5;
        $optionId = 5;
        $requestedStoreId = 1;
        $rowId = 10;
        $name = 'Test Seller';

        /** @var \Marketplacer\Seller\Model\Seller $seller */
        $seller = $this->objectManager->getObject(\Marketplacer\Seller\Model\Seller::class);
        $seller
            ->setOptionId(null)
            ->setRowId(null)
            ->setSellerId(null)
            ->setName($name)
            ->setStoreId($requestedStoreId);

        $this->sellerValidatorMock->method('validate')->willReturn(null);

        $attributeMock = $this->objectManager->getObject(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $this->sellerAttributeRetrieverMock->method('getAttribute')->willReturn($attributeMock);

        /** @var \Magento\Eav\Model\Entity\Attribute\Option $option */
        $option = $this->objectManager->getObject(\Magento\Eav\Model\Entity\Attribute\Option::class);
        $option->setIsDefault(0)->setSortOrder(0)->setValue(0);
        $this->attributeOptionHandlerMock->method('createAttributeOption')->willReturn($option);

        $this->attributeOptionHandlerMock->method('isAdminLabelUnique')->willReturn(false);

        $this->attributeOptionHandlerMock->expects($this->never())->method('saveAttributeOption');

        $this->expectException(CouldNotSaveException::class);
        $this->expectExceptionMessage('Seller with this name already exists.');

        $this->repository->save($seller);
    }

    public function testSaveExistingValidSeller()
    {
        $sellerId = 5;
        $optionId = 5;
        $requestedStoreId = 1;
        $rowId = 10;
        $name = 'Test Seller';

        $option = $this->objectManager->getObject(\Magento\Eav\Model\Entity\Attribute\Option::class);
        $option->setIsDefault(0)->setSortOrder(0)->setValue(0);
        $option->setValue($optionId);

        /** @var \Marketplacer\Seller\Model\Seller $seller */
        $seller = $this->objectManager->getObject(\Marketplacer\Seller\Model\Seller::class);
        $seller
            ->setOptionId($optionId)
            ->setRowId(null)
            ->setSellerId($sellerId)
            ->setName($name)
            ->setStoreId($requestedStoreId)
            ->setAttributeOption($option);

        $attributeMock = $this->objectManager->getObject(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $this->sellerAttributeRetrieverMock->method('getAttribute')->willReturn($attributeMock);

        $this->attributeOptionHandlerMock->method('isAttributeOptionIdExist')->willReturn(true);

        $this->sellerValidatorMock->method('validate')->willReturn(null);

        /** @var \Magento\Eav\Model\Entity\Attribute\Option $option */

        $this->attributeOptionHandlerMock->method('isAdminLabelUnique')->willReturn(true);

        $this->attributeOptionHandlerMock->method('saveAttributeOption')->willReturn($option);
        $option->setValue($optionId);

        $this->sellerResourceMock->expects($this->once())->method('save')->willReturnSelf();
        $seller->setRowId($rowId);

        $this->repository->save($seller);

        $this->assertEquals($optionId, $seller->getOptionId());
        $this->assertEquals($sellerId, $seller->getSellerId());
        $this->assertEquals('test-seller', $seller->getUrlKey());
    }

    public function testSaveExistingSellerWithExistingOptionName() {
        $sellerId = 5;
        $optionId = 5;
        $requestedStoreId = 1;
        $rowId = 10;
        $name = 'Test Seller';

        $option = $this->objectManager->getObject(\Magento\Eav\Model\Entity\Attribute\Option::class);
        $option->setIsDefault(0)->setSortOrder(0)->setValue(0);
        $option->setValue($optionId);

        /** @var \Marketplacer\Seller\Model\Seller $seller */
        $seller = $this->objectManager->getObject(\Marketplacer\Seller\Model\Seller::class);
        $seller
            ->setOptionId($optionId)
            ->setRowId(null)
            ->setSellerId($sellerId)
            ->setName($name)
            ->setStoreId($requestedStoreId)
            ->setAttributeOption($option);

        $attributeMock = $this->objectManager->getObject(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $this->sellerAttributeRetrieverMock->method('getAttribute')->willReturn($attributeMock);

        $this->attributeOptionHandlerMock->method('isAttributeOptionIdExist')->willReturn(true);

        $this->sellerValidatorMock->method('validate')->willReturn(null);

        /** @var \Magento\Eav\Model\Entity\Attribute\Option $option */

        $this->attributeOptionHandlerMock->method('isAdminLabelUnique')->willReturn(false);

        $this->attributeOptionHandlerMock->expects($this->never())->method('saveAttributeOption')->willReturn($option);

        $this->sellerResourceMock->expects($this->never())->method('save')->willReturnSelf();

        $this->expectException(CouldNotSaveException::class);
        $this->expectExceptionMessage('Seller with this name already exists.');

        $this->repository->save($seller);
    }

    public function testSaveExistingSellerWithMissingOption()
    {
        $sellerId = 5;
        $optionId = 5;
        $requestedStoreId = 1;
        $rowId = 10;
        $name = 'Test Seller';

        $option = $this->objectManager->getObject(\Magento\Eav\Model\Entity\Attribute\Option::class);
        $option->setIsDefault(0)->setSortOrder(0)->setValue(0);
        $option->setValue($optionId);

        /** @var \Marketplacer\Seller\Model\Seller $seller */
        $seller = $this->objectManager->getObject(\Marketplacer\Seller\Model\Seller::class);
        $seller
            ->setOptionId($optionId)
            ->setRowId(null)
            ->setSellerId($sellerId)
            ->setName($name)
            ->setStoreId($requestedStoreId)
            ->setAttributeOption($option);

        $attributeMock = $this->objectManager->getObject(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $this->sellerAttributeRetrieverMock->method('getAttribute')->willReturn($attributeMock);

        $this->attributeOptionHandlerMock->method('isAttributeOptionIdExist')->willReturn(false);

        $this->attributeOptionHandlerMock->expects($this->never())->method('saveAttributeOption')->willReturn($option);

        $this->sellerResourceMock->expects($this->never())->method('save')->willReturnSelf();

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('Seller attribute option with id = 5 not found.');

        $this->repository->save($seller);
    }
}
