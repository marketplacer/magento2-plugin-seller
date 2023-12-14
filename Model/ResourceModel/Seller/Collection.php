<?php

namespace Marketplacer\Seller\Model\ResourceModel\Seller;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Api\StoreRepositoryInterface;
use Marketplacer\Base\Model\Attribute\AttributeOptionHandler;
use Marketplacer\Base\Model\ResourceModel\AbstractCollection;
use Marketplacer\Seller\Api\Data\SellerCollectionInterface;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\SellerApi\Api\Data\MarketplacerSellerInterface;
use Marketplacer\SellerApi\Api\SellerAttributeRetrieverInterface;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection implements SellerCollectionInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplacer_seller_collection';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'collection';

    /**
     * @var string
     */
    protected $_idEntityKey = 'seller_id';

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var AttributeOptionHandler
     */
    protected $attributeOptionHandler;

    /**
     * @var SellerAttributeRetrieverInterface
     */
    protected $sellerAttributeRetriever;

    /**
     * Collection constructor.
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param StoreRepositoryInterface $store
     * @param string $eventPrefix
     * @param string $eventObject
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreRepositoryInterface $storeRepository,
        AttributeOptionHandler $attributeOptionHandler,
        SellerAttributeRetrieverInterface $sellerAttributeRetriever,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->storeRepository = $storeRepository;
        $this->attributeOptionHandler = $attributeOptionHandler;
        $this->sellerAttributeRetriever = $sellerAttributeRetriever;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );

        $this->addFilterToMap('id', 'main_table.seller_id');
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Marketplacer\Seller\Model\Seller', 'Marketplacer\Seller\Model\ResourceModel\Seller');
    }

    /**
     * @param mixed $sellerId
     * @return $this
     */
    public function addSellerIdToFilter($sellerId)
    {
        $this->addFieldToFilter(SellerInterface::SELLER_ID, $sellerId);
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function addSellerNameToFilter($name)
    {
        $this->addFieldToFilter(SellerInterface::NAME, $name);
        return $this;
    }

    /**
     * @return $this
     */
    public function addWithNameToFilter()
    {
        $this->addFieldToFilter(SellerInterface::NAME, 'notnull');
        return $this;
    }

    /**
     * @return $this
     */
    public function addStatusToFilter($status)
    {
        $this->addFieldToFilter(SellerInterface::STATUS, $status);
        return $this;
    }

    /**
     * @return $this
     */
    public function addStatusActiveToFilter()
    {
        $this->addStatusToFilter(SellerInterface::STATUS_ENABLED);
        return $this;
    }

    /**
     * @return $this
     */
    public function addSourceIdsToFilter($ids = []): self
    {
        $this->addFieldToFilter(MarketplacerSellerInterface::SOURCE_CODE, ['in' => $ids]);
        return $this;
    }

    /**
     * @return Collection
     * @throws NoSuchEntityException
     */
    protected function _afterLoad()
    {
        $this->initSellerAttributeOptions();

        return parent::_afterLoad();
    }

    /**
     * @return $this
     * @throws NoSuchEntityException
     */
    protected function initSellerAttributeOptions()
    {
        $sellers = $this->getItems();
        $optionIds = array_map(
            function (SellerInterface $seller) {
                return $seller->getOptionId();
            },
            $sellers
        );

        $optionIds = array_filter(array_unique($optionIds));
        $sellerAttribute = $this->sellerAttributeRetriever->getAttribute();
        $indexedOptions = $this->attributeOptionHandler->getAttributeOptionsByIds($sellerAttribute, $optionIds);

        foreach ($sellers as $seller) {
            $option = $indexedOptions[$seller->getOptionId()] ?? null;
            if ($option) {
                $seller->setAttributeOption($option);
            }
        }

        return $this;
    }
}
