<?php

namespace Marketplacer\Seller\Observer\Adminhtml\Config;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Marketplacer\Base\Model\Attribute\AttributeOptionHandler;
use Marketplacer\Seller\Api\SellerRepositoryInterface;
use Marketplacer\Seller\Helper\Config as ConfigHelper;
use Marketplacer\SellerApi\Api\SellerAttributeRetrieverInterface;

/**
 * Class SystemConfigUpdateAfterUpdateGeneralSeller
 * @package Marketplacer\Seller\Observer\Adminhtml\Config
 */
class SystemConfigUpdateAfterUpdateGeneralSeller implements ObserverInterface
{
    protected const TRIGGERING_XML_PATHS = [
        ConfigHelper::XML_PATH_GENERAL_SELLER_ID,
    ];

    /**
     * @var SellerRepositoryInterface
     */
    protected $sellerRepository;

    /**
     * @var SellerAttributeRetrieverInterface
     */
    protected $sellerAttributeRetriever;

    /**
     * @var AttributeOptionHandler
     */
    protected $attributeOptionHandler;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @param SellerRepositoryInterface $sellerRepository
     * @param SellerAttributeRetrieverInterface $sellerAttributeRetriever
     * @param AttributeOptionHandler $attributeOptionHandler
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        SellerRepositoryInterface $sellerRepository,
        SellerAttributeRetrieverInterface $sellerAttributeRetriever,
        AttributeOptionHandler $attributeOptionHandler,
        ConfigHelper $configHelper
    ) {
        $this->sellerRepository = $sellerRepository;
        $this->sellerAttributeRetriever = $sellerAttributeRetriever;
        $this->attributeOptionHandler = $attributeOptionHandler;
        $this->configHelper = $configHelper;
    }

    /**
     * Execute
     * @param EventObserver $observer
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws UrlAlreadyExistsException
     */
    public function execute(EventObserver $observer)
    {
        $triggers = array_intersect($observer->getChangedPaths(), static::TRIGGERING_XML_PATHS);

        if (!$triggers) {
            return $this;
        }

        $sellerAttribute = $this->sellerAttributeRetriever->getAttribute();

        $generalSellerId = $this->configHelper->getGeneralSellerId();

        $generalSeller = $this->sellerRepository->getById($generalSellerId);

        $generalOption = $generalSeller->getAttributeOption();
        $generalOption->setIsDefault(true);

        $this->attributeOptionHandler->saveAttributeOption($sellerAttribute, $generalOption);

        return $this;
    }
}
