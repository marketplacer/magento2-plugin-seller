<?php

namespace Marketplacer\Seller\Observer\Adminhtml\Config;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Marketplacer\Seller\Api\SellerRepositoryInterface;
use Marketplacer\Seller\Helper\Config as ConfigHelper;
use Marketplacer\Seller\Helper\Data;
use Marketplacer\Seller\Model\ResourceModel\Seller as SellerResource;
use Marketplacer\Seller\Model\UrlProcessor\SellerProcessor as SellerUrlProcessor;

/**
 * Class SystemConfigUpdateAfter
 * @package Marketplacer\Seller\Observer\Adminhtml\Config
 */
class SystemConfigUpdateAfterRefreshUrlRewrites implements ObserverInterface
{
    protected const TRIGGERING_XML_PATHS = [
        ConfigHelper::XML_PATH_SEO_BASE_URL_KEY,
        ConfigHelper::XML_PATH_SEO_URL_SUFFIX
    ];

    /**
     * @var SellerRepositoryInterface
     */
    protected $sellerRepository;

    /**
     * @var SellerUrlProcessor
     */
    protected $sellerUrlProcessor;

    /**
     * @var SellerResource
     */
    protected $sellerResource;

    /**
     * @param SellerRepositoryInterface $sellerRepository
     * @param SellerResource $sellerResource
     * @param SellerUrlProcessor $sellerUrlProcessor
     */
    public function __construct(
        SellerRepositoryInterface $sellerRepository,
        SellerResource $sellerResource,
        SellerUrlProcessor $sellerUrlProcessor
    ) {
        $this->sellerRepository = $sellerRepository;
        $this->sellerResource = $sellerResource;
        $this->sellerUrlProcessor = $sellerUrlProcessor;
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
        $this->sellerUrlProcessor->processSellerListingUrlRewrites();

        $allIds = $this->sellerResource->getAllSellerIds();

        $idsChunks = array_chunk($allIds, Data::BULK_OPERATIONS_CHUNK_SIZE);

        foreach ($idsChunks as $ids) {
            $sellers = $this->sellerRepository->getByIds($ids, Store::DEFAULT_STORE_ID);
            foreach ($sellers as $seller) {
                $seller->processUrlRewrites();
            }
        }
        return $this;
    }
}
