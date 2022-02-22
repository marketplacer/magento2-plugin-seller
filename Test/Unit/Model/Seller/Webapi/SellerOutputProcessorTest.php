<?php

namespace Marketplacer\Seller\Test\Unit\Model\Seller\Webapi;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Model\Seller;
use Marketplacer\Seller\Model\Seller\Webapi\SellerOutputProcessor;
use Marketplacer\SellerApi\Api\Data\MarketplacerSellerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class SellerOutputProcessorTest
 * @package Marketplacer\Seller\Test\Unit\Model\Seller\Webapi
 */
class SellerOutputProcessorTest extends TestCase
{
    /**
     * @var Seller
     */
    private $sellerObject;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var SellerOutputProcessor
     */
    private $sellerOutputProcessor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->sellerOutputProcessor = $this->objectManager->getObject(SellerOutputProcessor::class);
    }

    public function testExecute() {
        $result = [
            'id'=> 10,
            'store_image' => 'image',
            'phone' => 'Phone',
            'logo' => 'logo',
            'name' => 'Name',
            'address' => 'Address',
            'opening_hours' => '',
            'business_number' => '',
            'policies' => '',
            'description' => '',
            'email_address' => 'Email Address',
            'shipping_policy' => '',
        ];
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
        ]);
        $resultfromfun =  $this->sellerOutputProcessor->execute($this->sellerObject, $result);
        $this->assertEquals($result, $resultfromfun);
    }

    public function testExecuteWithExtraSellerData() {
        $result = [
            'id'=> 10,
            'store_image' => 'image',
            'phone' => 'Phone',
            'logo' => 'logo',
            'name' => 'Name',
            'address' => 'Address',
            'opening_hours' => '',
            'business_number' => '',
            'policies' => '',
            'description' => '',
            'email_address' => 'Email Address',
            'shipping_policy' => '',
        ];
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
            'test1' => '',
            'test2' => ''
        ]);
        $resultfromfun =  $this->sellerOutputProcessor->execute($this->sellerObject, $result);
        $this->assertEquals($result, $resultfromfun);
    }
}
