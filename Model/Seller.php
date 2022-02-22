<?php

namespace Marketplacer\Seller\Model;

use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Marketplacer\Base\Model\Attribute\AttributeOptionHandler;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Model\UrlProcessor\SellerProcessor;
use Marketplacer\Seller\Model\UrlProcessor\SellerProcessorFactory;
use Marketplacer\SellerApi\Api\SellerAttributeRetrieverInterface;
use Marketplacer\SellerApi\Model\MarketplacerSeller;

class Seller extends MarketplacerSeller implements SellerInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplacer_seller';

    /**
     * @var AttributeOptionHandler
     */
    protected $attributeOptionHandler;

    /**
     * @var AttributeOptionInterface
     */
    protected $attributeOption;

    /**
     * @var SellerAttributeRetrieverInterface
     */
    protected $sellerAttributeRetriver;

    /**
     * @var SellerProcessor
     */
    protected $urlProcessor;

    /**
     * @var SellerProcessorFactory
     */
    protected $urlProcessorFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param SellerAttributeRetrieverInterface $sellerAttributeRetriever
     * @param AttributeOptionHandler $attributeOptionHandler
     * @param SellerProcessorFactory $urlProcessorFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        SellerAttributeRetrieverInterface $sellerAttributeRetriever,
        AttributeOptionHandler $attributeOptionHandler,
        SellerProcessorFactory $urlProcessorFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->attributeOptionHandler = $attributeOptionHandler;
        $this->sellerAttributeRetriver = $sellerAttributeRetriever;
        $this->urlProcessorFactory = $urlProcessorFactory;
    }

    /**
     * Initialization
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Seller::class);
    }

    /**
     * @inheritDoc
     */
    public function getRowId()
    {
        return $this->_getData(SellerInterface::ROW_ID);
    }

    /**
     * @inheritDoc
     */
    public function setRowId($rowId)
    {
        $this->setData(SellerInterface::ROW_ID, $rowId);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getStoreId()
    {
        return $this->_getData(SellerInterface::STORE_ID);
    }

    /**
     * @param int $storeId
     * @return SellerInterface
     */
    public function setStoreId($storeId)
    {
        $this->setData(SellerInterface::STORE_ID, $storeId);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getOptionId()
    {
        return $this->_getData(SellerInterface::OPTION_ID);
    }

    /**
     * Separate seller and option columns create for future usage. Now both values are always the same
     *
     * @param int $optionId
     * @return $this|SellerInterface
     */
    public function setOptionId($optionId)
    {
        $this->setData(SellerInterface::OPTION_ID, $optionId);
        $this->setSellerId($optionId);
        return $this;
    }

    /**
     * @return int|mixed|null
     */
    public function getStatus()
    {
        return $this->_getData(SellerInterface::STATUS);
    }

    /**
     * @param int $status
     * @return $this|SellerInterface
     */
    public function setStatus($status)
    {
        $this->setData(SellerInterface::STATUS, $status);
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return self::STATUS_ENABLED == $this->getStatus();
    }

    /**
     * @return int|mixed|null
     */
    public function getSortOrder()
    {
        return $this->_getData(SellerInterface::SORT_ORDER);
    }

    /**
     * @param int $sortOrder
     * @return $this|SellerInterface
     */
    public function setSortOrder($sortOrder)
    {
        $this->setData(SellerInterface::SORT_ORDER, $sortOrder);
        return $this;
    }

    /**
     * Get Seller name (option label)
     */
    public function getName()
    {
        if (!$this->hasData(SellerInterface::NAME)) {
            $this->setData(SellerInterface::NAME, $this->getLabelFromAttributeOption());
        }

        return $this->_getData(SellerInterface::NAME);
    }

    /**
     * @inheritDoc
     */
    public function getUrlKey()
    {
        return $this->_getData(SellerInterface::URL_KEY);
    }

    /**
     * @param string $urlKey
     * @return SellerInterface
     */
    public function setUrlKey($urlKey)
    {
        $this->validateUrlKey($urlKey);

        $this->setData(SellerInterface::URL_KEY, $urlKey);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMetaTitle()
    {
        return $this->_getData(SellerInterface::META_TITLE);
    }

    /**
     * @param string $metaTitle
     * @return SellerInterface
     */
    public function setMetaTitle($metaTitle)
    {
        $this->setData(SellerInterface::META_TITLE, $metaTitle);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMetaDescription()
    {
        return $this->_getData(SellerInterface::META_DESCRIPTION);
    }

    /**
     * @param string $metaDescription
     * @return SellerInterface
     */
    public function setMetaDescription($metaDescription)
    {
        $this->setData(SellerInterface::META_DESCRIPTION, $metaDescription);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->_getData(SellerInterface::CREATED_AT);
    }

    /**
     * @param string $createdAt
     * @return SellerInterface
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(SellerInterface::CREATED_AT, $createdAt);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt()
    {
        return $this->_getData(SellerInterface::UPDATED_AT);
    }

    /**
     * @param string $updatedAt
     * @return SellerInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData(SellerInterface::UPDATED_AT, $updatedAt);
        return $this;
    }

    /**
     * @return AttributeOptionInterface
     * @throws NoSuchEntityException
     */
    public function getAttributeOption()
    {
        if (null === $this->attributeOption) {
            $attribute = $this->sellerAttributeRetriver->getAttribute();
            $this->attributeOption =
                $this->attributeOptionHandler->getAttributeOptionById($attribute, $this->getOptionId());
        }

        return $this->attributeOption;
    }

    /**
     * @return bool
     */
    public function hasAttributeOption()
    {
        return null !== $this->attributeOption;
    }

    /**
     * @param AttributeOptionInterface $attributeOption
     * @return $this
     */
    public function setAttributeOption(AttributeOptionInterface $attributeOption)
    {
        $this->attributeOption = $attributeOption;
        return $this;
    }

    /**
     * Clean url key
     * @param string $urlKey
     * @return string
     * @throws ValidatorException
     */
    public function getSanitizedUrlKey($urlKey)
    {
        return preg_replace('/[^0-9a-z\_]/', '-', strtolower(trim($urlKey)));
    }

    /**
     * Validate url key
     * @param string $urlKey
     * @return true If url key is valid
     * @throws ValidatorException
     */
    public function validateUrlKey($urlKey)
    {
        $isValid = $this->_validateUrlKey($urlKey);

        if (!$isValid) {
            throw new ValidatorException(__('Url key is not valid'));
        }

        return true;
    }

    /**
     * Validate url key
     *
     * @param string $urlKey
     * @return bool
     */
    protected function _validateUrlKey($urlKey)
    {
        if (!$urlKey) {
            return false;
        }

        return true;
    }

    /**
     * Process url rewrites
     *
     * @return $this
     * @throws NoSuchEntityException
     * @throws UrlAlreadyExistsException
     */
    public function processUrlRewrites()
    {
        $urlProcessor = $this->getUrlProcessor();
        $urlProcessor->processSellerUrlRewrites($this);
        return $this;
    }

    /**
     * Delete url rewrites
     *
     * @return $this
     */
    public function deleteUrlRewrites()
    {
        $urlProcessor = $this->getUrlProcessor();
        $urlProcessor->deleteUrlRewrites($this);
        return $this;
    }

    /**
     * @return SellerProcessor
     */
    protected function getUrlProcessor()
    {
        if (null === $this->urlProcessor) {
            $this->urlProcessor = $this->urlProcessorFactory->create();
        }

        return $this->urlProcessor;
    }

    /**
     * @return Seller
     * @throws NoSuchEntityException
     */
    protected function _afterLoad()
    {
        $optionId = $this->getOptionId();
        if ($optionId) {
            $attribute = $this->sellerAttributeRetriver->getAttribute();
            $attributeOption = $this->attributeOptionHandler->getAttributeOptionById($attribute, $optionId);

            $this->setAttributeOption($attributeOption);
        }

        return parent::_afterLoad();
    }

    /**
     * Get Seller name (option label)
     */
    public function getLabelFromAttributeOption()
    {
        $label = null;
        if ($this->getAttributeOption()) {
            if ($this->getStoreId() == Store::DEFAULT_STORE_ID || !$this->getAttributeOption()->getStoreLabels()) {
                $label = $this->getAttributeOption()->getLabel();
            } else {
                foreach ($this->getAttributeOption()->getStoreLabels() as $storeLabel) {
                    if ($this->getStoreId() == $storeLabel->getStoreId()) {
                        $label = $storeLabel->getLabel();
                        break;
                    }
                }
            }
        }

        return $label;
    }

    /**
     * @return Seller
     * @throws ValidatorException
     */
    public function refreshUrlKey()
    {
        $optionLabel = $this->hasAttributeOption() ? $this->getLabelFromAttributeOption() : null;
        $sellerName = $this->getName();

        if (!$optionLabel || strcmp($optionLabel, $sellerName) !== 0) {
            $newUrlKey = $this->getSanitizedUrlKey($this->getName());
            $this->setUrlKey($newUrlKey);

            $this->setData('_regenerate_url', true);
        }

        return $this;
    }
}
