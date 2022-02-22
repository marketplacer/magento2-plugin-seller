<?php

namespace Marketplacer\Seller\Model\UrlProcessor;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlFactory as MagentoUrlFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\UrlRewrite\Model\StorageInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Api\SellerRepositoryInterface;
use Marketplacer\Seller\Helper\Config as ConfigHelper;

/**
 * Class SellerProcessor
 * @package Marketplacer\Seller\Model\UrlProcessor
 */
class SellerProcessor
{
    const URL_ENTITY_TYPE = 'marketplacer-seller';
    const SELLER_LIST_ROUTE_PATH = 'marketplacer/seller/index';
    const SELLER_VIEW_ROUTE_PATH = 'marketplacer/seller/view';
    const SELLER_LIST_TARGET_PATH_PATTERN = self::SELLER_LIST_ROUTE_PATH;
    const SELLER_VIEW_TARGET_PATH_PATTERN = self::SELLER_VIEW_ROUTE_PATH . '/seller_id/%s';
    const REQUEST_PATH_PATTERN = '%s/%s';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var SellerRepositoryInterface
     */
    protected $sellerRepository;

    /**
     * ProcessorAbstract constructor.
     * @param StoreManagerInterface $storeManager
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param StorageInterface $storage
     * @param ConfigHelper $configHelper
     * @param SellerRepositoryInterface $sellerRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        UrlRewriteFactory $urlRewriteFactory,
        StorageInterface $storage,
        ConfigHelper $configHelper,
        SellerRepositoryInterface $sellerRepository
    ) {
        $this->storeManager = $storeManager;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->storage = $storage;
        $this->configHelper = $configHelper;
        $this->sellerRepository = $sellerRepository;
    }

    /**
     * Generate url rewrites
     * @param SellerInterface $seller
     * @param array $storeIds
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws UrlAlreadyExistsException
     */
    public function processSellerUrlRewrites(SellerInterface $seller, array $storeIds = [])
    {
        $urls = [];
        $sellerId = $seller->getSellerId();
        if (!$sellerId) {
            return false;
        }

        $stores = $this->getProcessedStores($storeIds);

        $targetPath = sprintf(self::SELLER_VIEW_TARGET_PATH_PATTERN, $sellerId);

        $sellerStoreRecords = $this->sellerRepository->getAllStoreRecordsById($sellerId);

        foreach ($stores as $store) {
            $seller = $sellerStoreRecords[$store->getId()] ?? $sellerStoreRecords[Store::DEFAULT_STORE_ID];
            $sellerUrlKey = $seller->getUrlKey();
            if (!$sellerUrlKey) {
                continue;
            }

            $baseUrlKey = $this->configHelper->getBaseUrlKey($store->getId());
            if (!$baseUrlKey) {
                continue;
            }
            $urlSuffix = $this->configHelper->getUrlSuffix($store->getId()) ?? '';
            $requestPath = sprintf(self::REQUEST_PATH_PATTERN, $baseUrlKey, $sellerUrlKey) . $urlSuffix;

            $urls[] = $this->urlRewriteFactory->create()
                ->setEntityType(self::URL_ENTITY_TYPE)
                ->setEntityId($sellerId)
                ->setRequestPath($requestPath)
                ->setTargetPath($targetPath)
                ->setStoreId($store->getId());
        }

        $this->storage->replace($urls);
        return true;
    }

    /**
     * Delete url rewrites
     * @param SellerInterface $seller
     * @param string | int | null $storeId
     * @return bool
     */
    public function deleteUrlRewrites(SellerInterface $seller, $storeId = null)
    {
        $sellerId = $seller->getSellerId();
        if (!$sellerId) {
            return false;
        }

        $filterData = [
            UrlRewrite::ENTITY_ID   => $sellerId,
            UrlRewrite::ENTITY_TYPE => SellerProcessor::URL_ENTITY_TYPE,
        ];

        if ($storeId) {
            $filterData[UrlRewrite::STORE_ID] = $storeId;
        }

        $this->storage->deleteByData($filterData);

        return true;
    }

    /**
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws UrlAlreadyExistsException
     */
    public function processSellerListingUrlRewrites(array $storeIds = [])
    {
        $urls = [];

        $stores = $this->getProcessedStores($storeIds);

        foreach ($stores as $store) {
            $baseUrlKey = $this->configHelper->getBaseUrlKey($store->getId());
            if (!$baseUrlKey) {
                continue;
            }
            $urlSuffix = $this->configHelper->getUrlSuffix($store->getId()) ?? '';
            $requestPath = $baseUrlKey . $urlSuffix;

            $urls[] = $this->urlRewriteFactory->create()
                ->setEntityType(self::URL_ENTITY_TYPE)
                ->setEntityId(0)
                ->setRequestPath($requestPath)
                ->setTargetPath(self::SELLER_LIST_TARGET_PATH_PATTERN)
                ->setStoreId($store->getId());
        }

        $this->storage->replace($urls);
        return true;
    }

    /**
     * @param array $storeIds
     * @return StoreInterface[]
     */
    protected function getProcessedStores($storeIds = [])
    {
        $stores = $this->storeManager->getStores();

        if ($storeIds) {
            $storeIds = (array)$storeIds;
            $stores = array_filter(
                $stores,
                function ($store) use ($storeIds) {
                    return in_array($store->getId(), $storeIds);
                }
            );
        }

        return $stores;
    }
}
