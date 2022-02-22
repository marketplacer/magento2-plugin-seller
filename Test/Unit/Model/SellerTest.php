<?php

namespace Marketplacer\Seller\Test\Unit\Model;

use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\State;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Model\ActionValidator\RemoveAction;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Marketplacer\Base\Model\Attribute\AttributeOptionHandler;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Model\Seller;
use Marketplacer\Seller\Model\UrlProcessor\SellerProcessorFactory;
use Marketplacer\SellerApi\Api\SellerAttributeRetrieverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SellerTest extends TestCase
{
    /**
     * @var RemoveAction|mixed|MockObject
     */
    private $actionValidatorMock;

    /**
     * @var Seller
     */
    private $model;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Registry|MockObject
     */
    private $registryMock;

    /**
     * @var SellerAttributeRetrieverInterface|MockObject
     */
    private $sellerAttributeRetrieverMock;

    /**
     * @var AttributeOptionHandler|MockObject
     */
    private $attributeOptionHandlerMock;

    /**
     * @var SellerProcessorFactory|MockObject
     */
    private $urlProcessorFactoryMock;

    /**
     * @var AbstractResource|MockObject
     */
    private $resourceMock;

    /**
     * @var \Magento\Framework\Data\Collection\AbstractDb |MockObject
     */
    private $resourceCollectionMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->actionValidatorMock = $this->createMock(RemoveAction::class);
        $this->contextMock = new Context(
            $this->getMockForAbstractClass(LoggerInterface::class),
            $this->getMockForAbstractClass(ManagerInterface::class),
            $this->getMockForAbstractClass(CacheInterface::class),
            $this->createMock(State::class),
            $this->actionValidatorMock
        );
        $this->registryMock = $this->createMock(Registry::class);
        $this->resourceMock = $this->createPartialMock(AbstractDb::class, [
            '_construct',
            'getConnection',
            '__wakeup',
            'commit',
            'delete',
            'getIdFieldName',
            'rollBack'
        ]);
        $this->resourceCollectionMock = $this->getMockBuilder(\Magento\Framework\Data\Collection\AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->sellerAttributeRetrieverMock = $this->getMockBuilder(SellerAttributeRetrieverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeOptionHandlerMock = $this->getMockBuilder(AttributeOptionHandler::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttributeOptionById'])
            ->getMockForAbstractClass();
        $this->urlProcessorFactoryMock = $this->getMockBuilder(SellerProcessorFactory::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->objectManager = new ObjectManager($this);

        $this->model = $this->objectManager->getObject(
            Seller::class,
            [
                'context'                  => $this->contextMock,
                'registry'                 => $this->registryMock,
                'sellerAttributeRetriever' => $this->sellerAttributeRetrieverMock,
                'attributeOptionHandler'   => $this->attributeOptionHandlerMock,
                'urlProcessorFactory'      => $this->urlProcessorFactoryMock,
                'resource'                 => $this->resourceMock,
                'resourceCollection'       => $this->resourceCollectionMock,
            ]
        );
    }

    public function testGetRowId()
    {
        $value = '5';
        $this->model->setRowId($value);
        $this->assertEquals($value, $this->model->getRowId());
    }

    public function testGetUrlKey()
    {
        $value = 'general-seller';
        $this->model->setUrlKey($value);
        $this->assertEquals($value, $this->model->getUrlKey());
    }

    public function testGetStatusEnabled()
    {
        $value = SellerInterface::STATUS_ENABLED;
        $this->model->setStatus($value);
        $this->assertEquals($value, $this->model->getStatus());
    }

    public function testGetStatusDisabled()
    {
        $value = SellerInterface::STATUS_DISABLED;
        $this->model->setStatus($value);
        $this->assertEquals($value, $this->model->getStatus());

        $this->assertFalse($this->model->isEnabled());
    }

    public function testIsEnabled()
    {
        $value = SellerInterface::STATUS_ENABLED;
        $this->model->setStatus($value);
        $this->assertEquals($value, $this->model->isEnabled());
        $this->assertTrue($this->model->isEnabled());
    }

    public function testGetDirectName()
    {
        $value = 'General Seller';
        $this->model->setName($value);
        $this->attributeOptionHandlerMock
            ->expects($this->never())
            ->method('getAttributeOptionById');
        $this->assertEquals($value, $this->model->getName());
    }

    public function testGetLabelFromAttributeOption()
    {
        $label = 'General Seller';

        $option = $this->objectManager->getObject(Option::class);
        $option->setLabel($label);
        $option->setValue('10');

        $this->attributeOptionHandlerMock->expects($this->never())->method('getAttributeOptionById');
        $this->model->setAttributeOption($option);

        $this->assertEquals($option, $this->model->getAttributeOption());
        $this->assertTrue($this->model->hasAttributeOption());
        $this->assertEquals($label, $this->model->getName());
    }

    public function testGetCreatedAt()
    {
        $value = '2021-11-29 00:10:30.0';
        $this->model->setCreatedAt($value);
        $this->assertEquals($value, $this->model->getCreatedAt());
    }

    public function testGetUpdatedAt()
    {
        $value = '2021-11-29 00:10:30.0';
        $this->model->setUpdatedAt($value);
        $this->assertEquals($value, $this->model->getUpdatedAt());
    }

    public function testGetSanitizedUrlKey()
    {
        $name = 'General Seller !123';

        $this->assertEquals('general-seller--123', $this->model->getSanitizedUrlKey($name));
    }

    public function testGetSortOrder()
    {
        $value = '5';
        $this->model->setSortOrder($value);
        $this->assertEquals($value, $this->model->getSortOrder());
    }

    public function testGetMetaTitle()
    {
        $value = 'Test Meta Title';
        $this->model->setMetaTitle($value);
        $this->assertEquals($value, $this->model->getMetaTitle());
    }

    public function testGetMetaDescription()
    {
        $value = 'Test Meta Description';
        $this->model->setMetaDescription($value);
        $this->assertEquals($value, $this->model->getMetaDescription());
    }

    public function testGetOptionId()
    {
        $value = '10';
        $this->model->setOptionId($value);
        $this->assertEquals($value, $this->model->getOptionId());
    }

    public function testValidateValidUrlKey()
    {
        $value = 'test-123';
        $this->model->setUrlKey($value);
        $this->assertEquals($value, $this->model->getUrlKey());
    }

    public function testValidateEmptyUrlKey()
    {
        $this->expectException(ValidatorException::class);
        $this->model->setUrlKey(null);
    }

    public function testGetStoreId()
    {
        $value = '2';
        $this->model->setStoreId($value);
        $this->assertEquals($value, $this->model->getStoreId());
    }
}
