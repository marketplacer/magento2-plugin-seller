<?php

namespace Marketplacer\Seller\Test\Unit\Model\Seller;

use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Api\Data\AttributeOptionLabelInterface;
use Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory;
use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Model\Seller;
use Marketplacer\Seller\Model\Seller\SellerDataToOptionSetter;
use Marketplacer\SellerApi\Api\Data\MarketplacerSellerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class SellerDataToOptionSetterTest
 * @package Marketplacer\Seller\Test\Unit\Model\Seller
 */
class SellerDataToOptionSetterTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;
    /**
     * @var \Magento\Eav\Api\Data\AttributeOptionLabelInterface
     */
    private $storeLabel;

    /**
     * @var AttributeOptionInterface
     */
    private $attributeOptionObject;

    /**
     * @var object
     */
    private $sellerOptionSetter;

    /**
     * @var Option
     */
    private $attriOptionWithoutStoreLabel;

    /**
     * @var AttributeOptionLabelInterfaceFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $attributeOptionLabelFactoryMock;

    /**
     * @var Seller
     */
    private $sellerObject;

    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->storeLabel = $this->objectManager->getObject(Option::class);
        $this->storeLabel->setLabel('test');
        $this->storeLabel->setStoreId(1);

        $this->attributeOptionObject = $this->objectManager->getObject(Option::class);
        $this->attributeOptionObject->setData([
            AttributeOptionInterface::LABEL => 'test',
            AttributeOptionInterface::VALUE => 'value',
            AttributeOptionInterface::IS_DEFAULT => 'defualt',
            AttributeOptionInterface::SORT_ORDER => '1',
            AttributeOptionLabelInterface::STORE_ID => 1,
            AttributeOptionInterface::STORE_LABELS =>  [
                $this->storeLabel,
            ],
        ]);

        $this->attributeOptionLabelFactoryMock = $this->createMock(AttributeOptionLabelInterfaceFactory::class);
        $this->attributeOptionLabelFactoryMock->method('create')->willReturn($this->objectManager->getObject(\Magento\Eav\Model\Entity\Attribute\OptionLabel::class));
        $this->sellerOptionSetter = $this->objectManager->getObject(SellerDataToOptionSetter::class,
            [
                'attributeOptionLabelFactory'  => $this->attributeOptionLabelFactoryMock,
            ]
        );
    }

    public function testUpdateStoreLabels ()
    {
        $this->sellerObject = $this->objectManager->getObject(Seller::class);
        $this->sellerObject->setData([
            SellerInterface::NAME => 'Name',
            MarketplacerSellerInterface::SELLER_ID => 10,
            SellerInterface::STORE_ID => 1,
        ]);
        $this->sellerOptionSetter->setFromSeller($this->sellerObject, $this->attributeOptionObject);
        $storeLabels = $this->attributeOptionObject->getStoreLabels();
        foreach ($storeLabels as $storeLabel) {
            $this->assertEquals($this->sellerObject->getName(), $storeLabel->getLabel());
        }
    }

    public function testSellerDefaultStoreId()
    {
        $this->sellerObject = $this->objectManager->getObject(Seller::class);
        $this->sellerObject->setData([
            SellerInterface::NAME => 'Name',
            MarketplacerSellerInterface::SELLER_ID => 10,
            SellerInterface::STORE_ID => Store::DEFAULT_STORE_ID,
        ]);
        $this->sellerOptionSetter->setFromSeller($this->sellerObject, $this->attributeOptionObject);
        $this->assertEquals($this->sellerObject->getName(), $this->attributeOptionObject->getLabel());
    }

    public function testAttrOptionsWithoutStoreLabels()
    {
        $this->sellerObject = $this->objectManager->getObject(Seller::class);
        $this->sellerObject->setData([
            SellerInterface::NAME => 'Name',
            SellerInterface::PHONE => 'Phone',
            SellerInterface::EMAIL_ADDRESS => 'Email Address',
            SellerInterface::ADDRESS => 'Address',
            MarketplacerSellerInterface::SELLER_ID => 10,
            MarketplacerSellerInterface::STORE_IMAGE => 'image',
            MarketplacerSellerInterface::LOGO => 'logo',
            MarketplacerSellerInterface::OPENING_HOURS => '',
            MarketplacerSellerInterface::BUSINESS_NUMBER => '',
            MarketplacerSellerInterface::POLICIES => '',
            MarketplacerSellerInterface::DESCRIPTION => '',
            MarketplacerSellerInterface::SHIPPING_POLICY => '',
            SellerInterface::STORE_ID => 1,
        ]);

        $this->attriOptionWithoutStoreLabel = $this->objectManager->getObject(Option::class);
        $this->attriOptionWithoutStoreLabel->setData([
            AttributeOptionInterface::LABEL => 'test',
            AttributeOptionInterface::VALUE => 'value',
            AttributeOptionInterface::IS_DEFAULT => 'defualt',
            AttributeOptionInterface::SORT_ORDER => '1',
            AttributeOptionLabelInterface::STORE_ID => 1,
        ]);
        $this->sellerOptionSetter->setFromSeller($this->sellerObject, $this->attriOptionWithoutStoreLabel);
        $storeLabels = $this->attriOptionWithoutStoreLabel->getStoreLabels();
        foreach ($storeLabels as $storeLabel) {
            $this->assertEquals($this->sellerObject->getName(), $storeLabel->getLabel());
            $this->assertEquals($this->sellerObject->getStoreId(), $storeLabel->getStoreId());
        }
    }

    public function testValidateException()
    {
        $this->sellerObject = $this->objectManager->getObject(Seller::class);
        $this->sellerObject->setData([
            SellerInterface::NAME => '',
            MarketplacerSellerInterface::SELLER_ID => 10,
            SellerInterface::STORE_ID => Store::DEFAULT_STORE_ID,
        ]);
        $this->expectException(LocalizedException::class);
        $this->sellerOptionSetter->setFromSeller($this->sellerObject, $this->attributeOptionObject);
    }
}
