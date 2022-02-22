<?php

namespace Marketplacer\Seller\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlFactory as MagentoUrlFactory;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Api\SellerRepositoryInterface;
use Marketplacer\Seller\Model\UrlProcessor\SellerProcessor;

/**
 * Class Url
 * @package Marketplacer\Seller\Helper
 */
class Url extends AbstractHelper
{
    /**
     * @var SellerRepositoryInterface
     */
    protected $sellerRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @var MagentoUrlFactory
     */
    protected $urlFactory;

    /**
     * Url constructor.
     * @param Context $context
     * @param SellerRepositoryInterface $sellerRepositoryInterface
     * @param StoreManagerInterface $storeManager
     * @param UrlFinderInterface $urlFinder
     * @param MagentoUrlFactory $urlFactory
     */
    public function __construct(
        Context $context,
        SellerRepositoryInterface $sellerRepositoryInterface,
        StoreManagerInterface $storeManager,
        UrlFinderInterface $urlFinder,
        MagentoUrlFactory $urlFactory
    ) {
        $this->sellerRepository = $sellerRepositoryInterface;
        $this->storeManager = $storeManager;
        $this->urlFinder = $urlFinder;
        $this->urlFactory = $urlFactory;
        parent::__construct($context);
    }

    /**
     * Get seller url
     * @param SellerInterface $seller
     * @param array $params
     * @return string
     * @throws NoSuchEntityException
     */
    public function getSellerUrl(SellerInterface $seller, $params = [])
    {
        $routePath = '';
        $requestPath = '';
        $routeParams = $params;

        $sellerId = $seller->getSellerId();
        $storeId = $seller->getStoreId();
        if (!$storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $filterData = [
            UrlRewrite::ENTITY_ID   => $sellerId,
            UrlRewrite::ENTITY_TYPE => SellerProcessor::URL_ENTITY_TYPE,
            UrlRewrite::STORE_ID    => $storeId,
        ];

        $rewrite = $this->urlFinder->findOneByData($filterData);
        if ($rewrite) {
            $requestPath = $rewrite->getRequestPath();
        }

        if (isset($routeParams['_scope'])) {
            $storeId = $this->storeManager->getStore($routeParams['_scope'])->getId();
        }

        if ($storeId != $this->storeManager->getStore()->getId()) {
            $routeParams['_scope_to_url'] = true;
        }

        if (empty($requestPath)) {
            $requestPath = sprintf(SellerProcessor::SELLER_VIEW_TARGET_PATH_PATTERN, $sellerId);
        }

        if (isset($routeParams['_request_path_only'])) {
            return $requestPath;
        }

        $routeParams['_direct'] = $requestPath;

        // Reset cached URL instance GET query params
        if (!isset($routeParams['_query'])) {
            $routeParams['_query'] = [];
        }

        return $this->getUrlInstance()->setScope($storeId)->getUrl($routePath, $routeParams);
    }

    /**
     * @param int $sellerId
     * @param array $params
     * @return bool|string
     * @throws NoSuchEntityException
     */
    public function getSellerUrlById($sellerId, $storeId = null, $params = [])
    {
        $seller = $this->sellerRepository->getById($sellerId, $storeId);
        if ($seller->getSellerId()) {
            return $this->getSellerUrl($seller, $params);
        }

        return null;
    }

    /**
     * Get seller listing url
     * @param array $params
     * @return string
     * @throws NoSuchEntityException
     */
    public function getSellerListingUrl($storeId = null, $params = [])
    {
        $routePath = '';
        $requestPath = '';
        $routeParams = $params;

        if (!$storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $filterData = [
            UrlRewrite::ENTITY_ID   => 0,
            UrlRewrite::ENTITY_TYPE => SellerProcessor::URL_ENTITY_TYPE,
            UrlRewrite::STORE_ID    => $storeId,
        ];

        $rewrite = $this->urlFinder->findOneByData($filterData);
        if ($rewrite) {
            $requestPath = $rewrite->getRequestPath();
        }

        if (isset($routeParams['_scope'])) {
            $storeId = $this->storeManager->getStore($routeParams['_scope'])->getId();
        }

        if ($storeId != $this->storeManager->getStore()->getId()) {
            $routeParams['_scope_to_url'] = true;
        }

        if (empty($requestPath)) {
            $requestPath = SellerProcessor::SELLER_LIST_ROUTE_PATH;
        }

        if (isset($params['_request_path_only'])) {
            return $requestPath;
        }

        $routeParams['_direct'] = $requestPath;

        // Reset cached URL instance GET query params
        if (!isset($routeParams['_query'])) {
            $routeParams['_query'] = [];
        }

        return $this->getUrlInstance()->setScope($storeId)->getUrl($routePath, $routeParams);
    }

    /**
     * Retrieve URL Instance
     * @return UrlInterface
     */
    protected function getUrlInstance()
    {
        return $this->urlFactory->create();
    }
}
