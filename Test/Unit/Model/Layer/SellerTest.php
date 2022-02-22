<?php

namespace Marketplacer\Seller\Test\Unit\Model\Layer;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\Category\CollectionFilter;
use Magento\Catalog\Model\Layer\ContextInterface;
use Magento\Catalog\Model\Layer\ItemCollectionProviderInterface;
use Magento\Catalog\Model\Layer\State;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Model\Seller;
use Marketplacer\SellerApi\Api\Data\MarketplacerSellerInterface;
use Marketplacer\SellerApi\Api\SellerAttributeRetrieverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class SellerTest
 * @package Marketplacer\Seller\Test\Unit\Model\Layer
 */
class SellerTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var SellerAttributeRetrieverInterface
     */
    private $sellerAttributeRetrieverMock;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var Seller
     */
    private $sellerObject;

    /**
     * @var Registry|MockObject
     */
    private $registry;

    /**
     * @var Collection|MockObject
     */
    private $collection;

    /**
     * @var ItemCollectionProviderInterface|MockObject
     */
    private $collectionProvider;

    /**
     * @var ContextInterface|MockObject
     */
    private $context;

    /**
     * @var CollectionFilter|MockObject
     */
    private $collectionFilter;

    /**
     * @var Category|MockObject
     */
    private $category;

    /**
     * @var object
     */
    private $model;

    /**
     * @var string
     */
    private $attributeCode = 'marketplacer_seller';

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->sellerObject = $this->objectManager->getObject(Seller::class);
        $this->sellerObject->setData([
            SellerInterface::NAME => 'Name',
            MarketplacerSellerInterface::SELLER_ID => 10,
            SellerInterface::STORE_ID => Store::DEFAULT_STORE_ID,
            SellerInterface::OPTION_ID => 5
        ]);

        $this->category = $this->getMockBuilder(Category::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry = new Registry();
        $this->registry->register('current_seller', $this->sellerObject);
        $this->registry->register('current_category', $this->category);

        $this->collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionFilter = $this->getMockBuilder(CollectionFilter::class)
            ->setMethods(['filter'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionProvider = $this->getMockBuilder(ItemCollectionProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->context = $this->getMockBuilder(ContextInterface::class)
            ->setMethods(['getStateKey', 'getCollectionFilter'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->context->expects($this->any())
            ->method('getCollectionFilter')
            ->willReturn($this->collectionFilter);
        $this->context->expects($this->any())
            ->method('getCollectionProvider')
            ->willReturn($this->collectionProvider);

        $this->state = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->categoryRepository = $this->getMockForAbstractClass(CategoryRepositoryInterface::class);
        $this->currentCategory = $this->createPartialMock(
            Category::class,
            ['getId']
        );
        $this->sellerAttributeRetrieverMock = $this->getMockBuilder(SellerAttributeRetrieverInterface::class)
            ->setMethods(['getAttributeCode'])
            ->getMockForAbstractClass();
        $this->sellerAttributeRetrieverMock->expects($this->any())->method('getAttributeCode')
            ->willReturn($this->attributeCode);

        $this->model = $this->objectManager->getObject(\Marketplacer\Seller\Model\Layer\Seller::class,
            [
            'registry'                  => $this->registry,
            'context'                   => $this->context,
            'sellerAttributeRetriever'  => $this->sellerAttributeRetrieverMock,
            ]
        );
    }

    public function testGetProductCollection()
    {
        $this->objectManager = new ObjectManager($this);

        $this->category->expects($this->any())->method('getId')->willReturn(333);
        $this->collectionFilter->expects($this->once())
            ->method('filter')
            ->with($this->collection, $this->category);
        $this->collectionProvider->expects($this->once())->method('getCollection')
            ->with($this->category)
            ->willReturn($this->collection);

        $this->collection->expects($this->once())->method('addFieldToFilter')
            ->with($this->attributeCode, 5)
            ->willReturnSelf();

        $result = $this->model->getProductCollection();
        $this->assertInstanceOf(Collection::class, $result);
    }

    public function testGetCurrentSeller()
    {
        $this->objectManager = new ObjectManager($this);
        $this->sellerObject = $this->objectManager->getObject(Seller::class);
        $this->sellerObject->setData(
            [
            SellerInterface::NAME                  => 'Name',
            MarketplacerSellerInterface::SELLER_ID => 10,
            SellerInterface::STORE_ID              => Store::DEFAULT_STORE_ID,
            ]
        );

        $sellerAttributeRetrieverMock = $this->getMockBuilder(SellerAttributeRetrieverInterface::class)
            ->getMock();
        $this->registry = new Registry();
        $this->registry->register('current_seller', $this->sellerObject);

        $seller = $this->objectManager->getObject(\Marketplacer\Seller\Model\Layer\Seller::class,
            [
                'registry'                  => $this->registry,
                'sellerAttributeRetriever'  => $sellerAttributeRetrieverMock,
            ]
        );
        $sellerInfo = $seller->getCurrentSeller();
        $this->assertEquals($this->sellerObject, $sellerInfo);
    }
}
