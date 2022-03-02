<?php

namespace Marketplacer\Seller\Setup\Patch\Data;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Ui\DataProvider\Product\ProductCollectionFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\Store;
use Marketplacer\Seller\Helper\Config;
use Marketplacer\SellerApi\Api\SellerAttributeRetrieverInterface;

class AssignGeneralSellerToAllProducts implements DataPatchInterface
{
    /** @var ModuleDataSetupInterface */
    protected $moduleDataSetup;

    /** @var EavSetupFactory */
    protected $eavSetupFactory;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var SellerAttributeRetrieverInterface
     */
    protected $sellerAttributeRetriever;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        ProductCollectionFactory $productCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        SellerAttributeRetrieverInterface $sellerAttributeRetriever
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->sellerAttributeRetriever = $sellerAttributeRetriever;
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();

        $configSelect = $connection
            ->select()
            ->from(['configs' => $connection->getTableName('core_config_data')], 'value')
            ->where('configs.path = ?', Config::XML_PATH_GENERAL_SELLER_ID)
            ->where('configs.scope = ?', ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
            ->where('configs.scope_id = ?', Store::DEFAULT_STORE_ID);

        $generalSellerId = $connection->fetchOne($configSelect);

        if (!$generalSellerId) {
            throw new LocalizedException(
                __('General Seller Id is not initialized at config param ' . Config::XML_PATH_GENERAL_SELLER_ID)
            );
        }

        $sellerAttribute = $this->sellerAttributeRetriever->getAttribute();

        $connection->beginTransaction();
        try {
            //assign general seller to all existing products that has no seller
            $productCollection = $this->productCollectionFactory->create();
            $productResource = $productCollection->getResource();
            $linkField = $productResource->getLinkField();
            $productCollection->addAttributeToFilter($sellerAttribute->getAttributeCode(), ['null' => true], 'left');

            $columns = [
                $linkField     => $linkField,
                'store_id'     => new \Zend_Db_Expr(Store::DEFAULT_STORE_ID),
                'attribute_id' => new \Zend_Db_Expr($sellerAttribute->getId()),
                'value'        => new \Zend_Db_Expr($generalSellerId),
            ];
            $select = $productCollection->getSelect();
            $select->reset(Select::COLUMNS)
                ->columns($columns);

            $insertFromSelectSql = $connection->insertFromSelect(
                $select,
                $sellerAttribute->getBackendTable(),
                array_keys($columns),
                Mysql::INSERT_ON_DUPLICATE
            );

            $connection->query($insertFromSelectSql);

            $eavSetup = $this->eavSetupFactory->create();
            $eavSetup->updateAttribute(Product::ENTITY, $sellerAttribute->getAttributeCode(), 'is_required', 1);

            $connection->commit();
        } catch (\Throwable $e) {
            $connection->rollBack();
            throw new LocalizedException(
                __('An error occurred during general seller create: %1', $e->getMessage()),
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            CreateMarketplacerSellerProductAttribute::class,
            CreateGeneralSellerEntity::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
