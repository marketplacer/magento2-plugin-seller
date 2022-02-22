<?php

namespace Marketplacer\Seller\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Marketplacer\Seller\Model\UrlProcessor\SellerProcessor as SellerUrlProcessor;

class CreateMarketplacerSellerListingUrlRewrites implements DataPatchInterface
{
    /**
     * @var SellerUrlProcessor
     */
    protected $sellerUrlProcessor;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        SellerUrlProcessor $sellerUrlProcessor
    ) {
        $this->sellerUrlProcessor = $sellerUrlProcessor;
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    public function apply()
    {
        $this->sellerUrlProcessor->processSellerListingUrlRewrites();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
