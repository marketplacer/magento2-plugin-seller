<?php

namespace Marketplacer\Seller\Model;

use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Marketplacer\Base\Model\Attribute\AttributeOptionHandler;
use Marketplacer\Seller\Api\Data\SellerCollectionInterface;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Api\Data\SellerInterfaceFactory;
use Marketplacer\SellerApi\Api\Data\MarketplacerSellerSearchResultsInterfaceFactory;
use Marketplacer\Seller\Api\SellerRepositoryInterface;
use Marketplacer\Seller\Model\ResourceModel\Seller\CollectionFactory as SellerCollectionFactory;
use Marketplacer\Seller\Model\Seller\SellerDataToOptionSetter;
use Marketplacer\Seller\Model\Seller\Validator as SellerValidator;
use Marketplacer\SellerApi\Api\SellerAttributeRetrieverInterface;
use Psr\Log\LoggerInterface;

class SellerRepository implements SellerRepositoryInterface
{
    /**
     * @var \Marketplacer\Seller\Model\ResourceModel\Seller
     */
    protected $sellerResource;

    /**
     * @var SellerAttributeRetrieverInterface
     */
    protected $sellerAttributeRetriever;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var SellerInterfaceFactory
     */
    protected $sellerFactory;

    /**
     * @var SellerValidator
     */
    protected $sellerValidator;

    /**
     * @var SellerDataToOptionSetter
     */
    protected $sellerDataToOptionSetter;

    /**
     * @var array
     */
    protected $imageInfoByUrl = [];

    /**
     * @var AttributeOptionInterfaceFactory
     */
    protected $attributeOptionFactory;

    /**
     * @var AttributeOptionHandler
     */
    protected $attributeOptionHandler;

    /**
     * @var SellerCollectionFactory
     */
    protected $sellerCollectionFactory;

    /**
     * @var MarketplacerSellerSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * SellerRepository constructor.
     * @param ResourceModel\Seller $sellerResource
     * @param SellerInterfaceFactory $sellerFactory
     * @param AttributeOptionHandler $attributeOptionHandler
     * @param SellerAttributeRetrieverInterface $sellerAttributeRetriever
     * @param StoreManagerInterface $storeManager
     * @param AttributeOptionInterfaceFactory $attributeOptionFactory
     * @param SellerCollectionFactory $sellerCollectionFactory
     * @param SellerValidator $sellerValidator
     * @param SellerDataToOptionSetter $sellerDataToOptionSetter
     */
    public function __construct(
        \Marketplacer\Seller\Model\ResourceModel\Seller $sellerResource,
        SellerInterfaceFactory $sellerFactory,
        AttributeOptionHandler $attributeOptionHandler,
        SellerAttributeRetrieverInterface $sellerAttributeRetriever,
        StoreManagerInterface $storeManager,
        AttributeOptionInterfaceFactory $attributeOptionFactory,
        SellerCollectionFactory $sellerCollectionFactory,
        SellerValidator $sellerValidator,
        SellerDataToOptionSetter $sellerDataToOptionSetter,
        MarketplacerSellerSearchResultsInterfaceFactory $sellerSearchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        LoggerInterface $logger
    ) {
        $this->sellerResource = $sellerResource;
        $this->sellerFactory = $sellerFactory;
        $this->attributeOptionHandler = $attributeOptionHandler;
        $this->sellerAttributeRetriever = $sellerAttributeRetriever;
        $this->storeManager = $storeManager;
        $this->attributeOptionFactory = $attributeOptionFactory;
        $this->sellerCollectionFactory = $sellerCollectionFactory;
        $this->sellerValidator = $sellerValidator;
        $this->sellerDataToOptionSetter = $sellerDataToOptionSetter;
        $this->searchResultsFactory = $sellerSearchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->logger = $logger;
    }

    /**
     * @param int $sellerId
     * @param int | string | null $storeId
     * @return SellerInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getById($sellerId, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        /**
         * @var $seller SellerInterface
         */
        $sellerCollection = $this->sellerCollectionFactory->create();
        $sellerCollection->addSellerIdToFilter($sellerId);
        $sellerCollection->addStoreIdToFilter($storeId, true);
        $sellerCollection->setOrder(SellerInterface::STORE_ID, 'DESC');
        //seller might have several records on storeview or on global level
        $sellerCollection->setCurPage(1)->setPageSize(1);
        $seller = $sellerCollection->getFirstItem();

        if (!$seller || !$seller->getRowId()) {
            throw new NoSuchEntityException(__('The seller with ID "%1" does not exist.', $sellerId ?? ''));
        }

        // seller exist, but not for requested store (for default) so create virtual copy
        if ($seller->getStoreId() != $storeId) {
            $seller = clone $seller;
            $seller->unsRowId();
            $seller->setStoreId($storeId);
        }

        return $seller;
    }

    /**
     * @param array $sellerIds
     * @param int | string | null $storeId
     * @return SellerInterface[]
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getByIds(array $sellerIds = [], $storeId = null)
    {
        if (!$sellerIds) {
            return [];
        }

        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        /**
         * @var $seller SellerInterface
         */
        $sellerCollection = $this->sellerCollectionFactory->create();
        $sellerCollection->addSellerIdToFilter(['in' => $sellerIds]);
        $sellerCollection->addStoreIdToFilter($storeId, true);

        $sellerCollection->setOrder(SellerInterface::SORT_ORDER, 'ASC');
        $sellerCollection->setOrder(SellerInterface::STORE_ID, 'DESC');

        return $this->getStoreItemsFromCollection($sellerCollection, $storeId);
    }

    /**
     * @param int | string | null $storeId
     * @return SellerInterface[]
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getAllDisplayedSellers($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        /**
         * @var $seller SellerInterface
         */
        $sellerCollection = $this->sellerCollectionFactory->create();
        $sellerCollection->addStoreIdToFilter($storeId, true);
        $sellerCollection->addStatusActiveToFilter();

        $sellerCollection->setOrder(SellerInterface::SORT_ORDER, 'ASC');
        $sellerCollection->setOrder(SellerInterface::STORE_ID, 'DESC');

        return $this->getStoreItemsFromCollection($sellerCollection, $storeId);
    }

    /**
     * @param SellerCollectionInterface $sellerCollection
     * @param $storeId
     * @return array
     */
    protected function getStoreItemsFromCollection(SellerCollectionInterface $sellerCollection, $storeId)
    {
        //seller might have several records on storeview or on global level
        $resultSellers = [];
        foreach ($sellerCollection->getItems() as $seller) {
            if ($seller->getStoreId() == $storeId) {
                $resultSellers[$seller->getSellerId()] = $seller;
            } elseif (!isset($resultSellers[$seller->getSellerId()])) {
                // seller exist, but not for requested store (for default) so create virtual copy
                $seller = clone $seller;
                $seller->unsRowId();
                $seller->setStoreId($storeId);
                $resultSellers[$seller->getSellerId()] = $seller;
            }
        }

        return $resultSellers;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getAllSellerIds()
    {
        return $this->sellerResource->getAllSellerIds();
    }

    /**
     * @param int $sellerId
     * @param int | null $storeId
     * @return SellerInterface[]
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getAllStoreRecordsById($sellerId)
    {
        /**
         * @var $seller SellerInterface
         */
        $storeIds = array_keys($this->storeManager->getStores(true, false));
        $sellerCollection = $this->sellerCollectionFactory->create();
        $sellerCollection->addSellerIdToFilter($sellerId);
        $sellerCollection->addStoreIdToFilter($storeIds, true);
        //seller might have several records on storeview or on global level

        $sellersByStoreId = [];
        foreach ($sellerCollection as $seller) {
            $sellersByStoreId[$seller->getStoreId()] = $seller;
        }

        if (!$sellersByStoreId) {
            throw new NoSuchEntityException(__('The seller with ID "%1" does not exist.', $sellerId ?? ''));
        }

        return $sellersByStoreId;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->sellerCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        if (!$collection->hasFlag('_store_filter_applied')) {
            $collection->addStoreIdToFilter($this->storeManager->getStore()->getId());
        }
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSellers($collection->getItems());
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function save($seller)
    {
        if (!$seller->hasStoreId()) {
            $seller->setStoreId($this->storeManager->getStore()->getId());
        }

        $sellerAttribute = $this->sellerAttributeRetriever->getAttribute();

        if (!$seller->getOptionId()) { //new seller
            $this->sellerValidator->validate($seller);

            $attributeOption = $this->attributeOptionHandler->createAttributeOption();
            $attributeOption->setLabel($seller->getName());

            $this->sellerDataToOptionSetter->setFromSeller($seller, $attributeOption);

            if (!$this->attributeOptionHandler->isAdminLabelUnique($sellerAttribute, $attributeOption)) {
                throw new CouldNotSaveException(__('Seller with this name already exists.'));
            }

            $sellerStoreId = $seller->getStoreId();
            $seller->setStoreId(Store::DEFAULT_STORE_ID);

            $this->sellerResource->beginTransaction();
            try {
                $this->attributeOptionHandler->saveAttributeOption($sellerAttribute, $attributeOption);
                if (!$attributeOption->getValue()) {
                    throw new LocalizedException(__('Unable to save Seller attribute option'));
                }
                $seller->setOptionId($attributeOption->getValue());

                $seller->refreshUrlKey();
                $this->sellerResource->save($seller);

                //if first creation was on store level, create base record and copy on store level to match options
                if ($sellerStoreId != Store::DEFAULT_STORE_ID) {
                    $seller->setStoreId($sellerStoreId);
                    $seller->unsRowId();
                    $seller->refreshUrlKey();

                    $this->sellerResource->save($seller);
                }

                $this->sellerResource->commit();
            } catch (LocalizedException $exception) {
                $this->sellerResource->rollBack();
                throw $exception;
            } catch (\Throwable $exception) {
                $this->sellerResource->rollBack();
                $this->logger->critical($exception);
                throw new LocalizedException(__('Unable to create seller'));
            }
        } else {
            //existing seller
            $optionId = $seller->getOptionId();
            if (!$optionId || !$this->isSellerOptionExist($optionId)) {
                throw new NoSuchEntityException(__('Seller attribute option with id = %1 not found.', $optionId));
            }

            $this->sellerValidator->validate($seller, true);

            $seller->refreshUrlKey();

            $attributeOption = $seller->getAttributeOption();
            $this->sellerDataToOptionSetter->setFromSeller($seller, $attributeOption);

            if (!$this->attributeOptionHandler->isAdminLabelUnique($sellerAttribute, $attributeOption)) {
                throw new CouldNotSaveException(__('Seller with this name already exists.'));
            }

            $this->sellerResource->beginTransaction();
            try {
                $this->attributeOptionHandler->saveAttributeOption($sellerAttribute, $attributeOption);

                $this->sellerResource->save($seller);

                $this->sellerResource->commit();
            } catch (LocalizedException $exception) {
                $this->sellerResource->rollBack();
                throw $exception;
            } catch (\Throwable $exception) {
                $this->sellerResource->rollBack();
                $this->logger->critical($exception);
                throw new LocalizedException(__('Unable to update the seller with ID "%1"', $seller->getSellerId()));
            }
        }

        return $seller;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($sellerId)
    {
        $seller = $this->getById($sellerId, Store::DEFAULT_STORE_ID);

        $sellerAttribute = $this->sellerAttributeRetriever->getAttribute();

        $this->sellerResource->beginTransaction();
        try {
            if ($seller->getOptionId()
                && $this->attributeOptionHandler->getAttributeOptionById($sellerAttribute, $seller->getOptionId())
            ) {
                $this->attributeOptionHandler->deleteOptionById($sellerAttribute, $seller->getOptionId());
            }

            $this->sellerResource->delete($seller);

            $this->sellerResource->commit();
        } catch (LocalizedException $exception) {
            $this->sellerResource->rollBack();
            throw $exception;
        } catch (\Throwable $exception) {
            $this->sellerResource->rollBack();
            $this->logger->critical($exception);
            throw new LocalizedException(__('Unable to delete the seller with ID "%1"', $sellerId));
        }

        return true;
    }

    /**
     * @param int $optionId
     * @return bool
     * @throws LocalizedException
     */
    public function isSellerOptionExist($optionId)
    {
        $sellerAttribute = $this->sellerAttributeRetriever->getAttribute();
        return $this->attributeOptionHandler->isAttributeOptionIdExist($sellerAttribute, $optionId);
    }
}
