<?php

namespace Marketplacer\Seller\Test\Unit\Model\Seller;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\State;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ActionValidator\RemoveAction;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Marketplacer\Base\Model\Attribute\AttributeOptionHandler;
use Marketplacer\Seller\Model\Seller;
use Marketplacer\Seller\Model\Seller\Validator;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Model\UrlProcessor\SellerProcessorFactory;
use Marketplacer\SellerApi\Api\SellerAttributeRetrieverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class ValidatorTest
 * @package Marketplacer\Seller\Test\Unit\Model\Seller
 */
class ValidatorTest extends TestCase
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

    /** @var Validator | MockObject */
    private $validateObject;

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
                'context' => $this->contextMock,
                'registry' => $this->registryMock,
                'sellerAttributeRetriever' => $this->sellerAttributeRetrieverMock,
                'attributeOptionHandler' => $this->attributeOptionHandlerMock,
                'urlProcessorFactory' => $this->urlProcessorFactoryMock,
                'resource' => $this->resourceMock,
                'resourceCollection' => $this->resourceCollectionMock,
            ]
        );
        $this->validateObject = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['validate'])
            ->getMock();
    }

    public function testValidateException()
    {
        $this->validateObject->expects($this->never())->method('validate')->with([
            $this->model, false
        ]);
        $this->expectException(LocalizedException::class);
        $this->objectManager->getObject(Validator::class)->validate($this->model, false);
    }

    public function testValidate()
    {
        $this->validateObject->expects($this->never())->method('validate')->with([
            $this->model, false
        ]);

        $this->model->setData([
            SellerInterface::NAME => 'Name',
            SellerInterface::PHONE => 'Phone',
            SellerInterface::EMAIL_ADDRESS => 'Email Address',
            SellerInterface::ADDRESS => 'Address',
        ]);

        $validator = $this->objectManager->getObject(Validator::class);
        $validator->validate($this->model, false);
        $this->assertEquals('Name', $this->model->getName());
        $this->assertEquals('Phone', $this->model->getPhone());
        $this->assertEquals('Email Address', $this->model->getEmailAddress());
        $this->assertEquals('Address', $this->model->getAddress());
    }

    public function testSkipValidationFlagValidate()
    {
        $this->validateObject->expects($this->never())->method('validate')->with([
            $this->model, false
        ]);

        $this->model->setData([
            '_skip_validation_flag' => true,
            SellerInterface::NAME => 'Name',
            SellerInterface::PHONE => 'Phone',
            SellerInterface::EMAIL_ADDRESS => 'Email Address',
            SellerInterface::ADDRESS => 'Address',
        ]);

        $validator = $this->objectManager->getObject(Validator::class);
        $validator->validate($this->model, false);
        $this->assertEquals('Name', $this->model->getName());
        $this->assertEquals('Phone', $this->model->getPhone());
        $this->assertEquals('Email Address', $this->model->getEmailAddress());
        $this->assertEquals('Address', $this->model->getAddress());
    }
}
