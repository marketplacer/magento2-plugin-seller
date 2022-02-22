<?php

namespace Marketplacer\Seller\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Marketplacer\SellerApi\Api\Data\ProductAttributeInterface;

class CreateMarketplacerSellerProductAttribute implements DataPatchInterface
{
    /** @var ModuleDataSetupInterface */
    protected $moduleDataSetup;

    /** @var EavSetupFactory */
    protected $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);

        $sellerAttributeId = $eavSetup->getAttribute(
            Product::ENTITY,
            ProductAttributeInterface::SELLER_ATTRIBUTE_CODE,
            'attribute_id'
        );

        if (!empty($sellerAttributeId)) {
            return null;
        }

        $attrData = [
            'type'                    => 'int',
            'label'                   => 'Seller',
            'input'                   => 'select',
            'required'                => false,
            'source'                  => Table::class,
            'user_defined'            => true,
            'global'                  => ScopedAttributeInterface::SCOPE_GLOBAL,
            'sort_order'              => 100,
            'visible'                 => true,
            'searchable'              => false,
            'comparable'              => false,
            'filterable'              => true,
            'filterable_in_search'    => true,
            'visible_on_front'        => true,
            'used_in_product_listing' => true,
            'is_used_in_grid'         => true,
            'is_filterable_in_grid'   => true,
            'used_for_promo_rules'    => true,
            'group'                   => 'General'
        ];

        $eavSetup->addAttribute($entityTypeId, ProductAttributeInterface::SELLER_ATTRIBUTE_CODE, $attrData);
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
