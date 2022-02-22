<?php

namespace Marketplacer\Seller\Setup\Patch\Data;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\Store;
use Marketplacer\Base\Model\Attribute\AttributeOptionHandler;
use Marketplacer\Seller\Api\Data\ProductAttributeInterface;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Api\Data\SellerInterfaceFactory;
use Marketplacer\Seller\Api\SellerRepositoryInterface;
use Marketplacer\Seller\Helper\Config;
use Marketplacer\Seller\Model\ResourceModel\Seller as SellerResource;
use Marketplacer\Seller\Model\ResourceModel\Seller\CollectionFactory;
use Marketplacer\SellerApi\Api\SellerAttributeRetrieverInterface;

class CreateGeneralSellerEntity implements DataPatchInterface
{
    /** @var
     * SellerResource
     */
    protected $sellerResource;

    /** @var SellerRepositoryInterface
     */
    protected $sellerRepository;

    /** @var CollectionFactory
     */
    protected $sellerCollectionFactory;

    /** @var SellerInterfaceFactory
     */
    protected $sellerInterfaceFactory;

    /**
     * @var SellerAttributeRetrieverInterface
     */
    protected $sellerAttributeRetriever;

    /**
     * @var AttributeOptionHandler
     */
    protected $attributeOptionHandler;

    /**
     * @param SellerResource $sellerResource
     * @param SellerRepositoryInterface $sellerRepository
     * @param CollectionFactory $sellerCollectionFactory
     * @param SellerInterfaceFactory $sellerInterfaceFactory
     * @param AttributeOptionHandler $attributeOptionHandler
     */
    public function __construct(
        SellerResource $sellerResource,
        SellerRepositoryInterface $sellerRepository,
        CollectionFactory $sellerCollectionFactory,
        SellerInterfaceFactory $sellerInterfaceFactory,
        SellerAttributeRetrieverInterface $sellerAttributeRetriever,
        AttributeOptionHandler $attributeOptionHandler
    ) {
        $this->sellerResource = $sellerResource;
        $this->sellerRepository = $sellerRepository;
        $this->sellerCollectionFactory = $sellerCollectionFactory;
        $this->sellerInterfaceFactory = $sellerInterfaceFactory;
        $this->sellerAttributeRetriever = $sellerAttributeRetriever;
        $this->attributeOptionHandler = $attributeOptionHandler;
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    public function apply()
    {
        $generalSellerId = $this->getOrCreateGeneralSeller();

        $this->initGeneralSellerIdInConfig($generalSellerId);
    }

    /**
     * @return int|mixed|null
     * @throws LocalizedException
     */
    protected function getOrCreateGeneralSeller()
    {
        $connection = $this->sellerResource->getConnection();

        $select = $connection
            ->select()
            ->from(['sellers' => $this->sellerResource->getMainTable()])
            ->joinLeft(
                ['eav_aov' => $this->sellerResource->getTable('eav_attribute_option_value')],
                'sellers.option_id = eav_aov.option_id AND sellers.store_id = eav_aov.store_id',
                ['name' => 'eav_aov.value']
            )
            ->where('sellers.store_id = ?', Store::DEFAULT_STORE_ID)
            ->where('eav_aov.value = ?', SellerInterface::SELLER_NAME_GENERAL);

        $sellerRow = $connection->fetchRow($select);

        if ($sellerRow && isset($sellerRow[SellerInterface::SELLER_ID])) {
            $generalSellerId = $sellerRow[SellerInterface::SELLER_ID];
        } else {
            $generalSeller = $this->sellerInterfaceFactory->create();
            $generalSeller
                ->setName(SellerInterface::SELLER_NAME_GENERAL)
                ->setStoreId(Store::DEFAULT_STORE_ID)
                ->setStatus(SellerInterface::STATUS_ENABLED);
            $generalSeller->setData('_skip_validation_flag', true);

            $generalSeller = $this->sellerRepository->save($generalSeller);

            $option = $generalSeller->getAttributeOption();

            //save general seller as default attribute option
            $defaultOption = $option->setIsDefault(true);
            $sellerAttribute = $this->sellerAttributeRetriever->getAttribute();
            $this->attributeOptionHandler->saveAttributeOption($sellerAttribute, $defaultOption);

            $generalSellerId = $generalSeller->getSellerId();
        }

        return $generalSellerId;
    }

    /**
     * @param string | int $generalSellerId
     */
    protected function initGeneralSellerIdInConfig($generalSellerId)
    {
        $connection = $this->sellerResource->getConnection();

        $configSelect = $connection
            ->select()
            ->from(['configs' => $connection->getTableName('core_config_data')], 'value')
            ->where('configs.path = ?', Config::XML_PATH_GENERAL_SELLER_ID)
            ->where('configs.scope = ?', ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
            ->where('configs.scope_id = ?', Store::DEFAULT_STORE_ID);

        $existingConfigId = $connection->fetchOne($configSelect);

        if (!$existingConfigId) {
            $connection->insertOnDuplicate(
                $connection->getTableName('core_config_data'),
                [
                    'path'     => Config::XML_PATH_GENERAL_SELLER_ID,
                    'scope'    => ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    'scope_id' => Store::DEFAULT_STORE_ID,
                    'value'    => $generalSellerId
                ],
                ['value']
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            CreateMarketplacerSellerProductAttribute::class
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
