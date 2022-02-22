<?php

namespace Marketplacer\Seller\Model\Sitemap\ItemProvider;

use Magento\Sitemap\Model\ItemProvider\CategoryConfigReader;
use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Api\SellerRepositoryInterface;
use Marketplacer\Seller\Helper\Url as UrlHelper;
use Marketplacer\Seller\Helper\Config as ConfigHelper;

class Seller implements ItemProviderInterface
{
    /**
     * @var SellerRepositoryInterface
     */
    protected $sellerRepository;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var SitemapItemInterfaceFactory
     */
    protected $sitemapItemFactory;

    /**
     * Config reader
     *
     * @var CategoryConfigReader
     */
    protected $categoryConfigReader;

    /**
     * @param SellerRepositoryInterface $sellerRepository
     * @param UrlHelper $urlHelper
     * @param ConfigHelper $configHelper
     * @param SitemapItemInterfaceFactory $sitemapItemFactory
     * @param CategoryConfigReader $categoryConfigReader
     */
    public function __construct(
        SellerRepositoryInterface $sellerRepository,
        UrlHelper $urlHelper,
        ConfigHelper $configHelper,
        SitemapItemInterfaceFactory $sitemapItemFactory,
        CategoryConfigReader $categoryConfigReader
    ) {
        $this->sellerRepository = $sellerRepository;
        $this->urlHelper = $urlHelper;
        $this->configHelper = $configHelper;
        $this->sitemapItemFactory = $sitemapItemFactory;
        $this->categoryConfigReader = $categoryConfigReader;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems($storeId)
    {
        if (!$this->configHelper->isEnabledOnStorefront($storeId)) {
            return [];
        }

        $sellerListingItem = $this->sitemapItemFactory->create([
            'url'             => $this->urlHelper->getSellerListingUrl($storeId, ['_request_path_only' => 1]),
            'updatedAt'       => null,
            'images'          => null,
            'priority'        => $this->categoryConfigReader->getPriority($storeId),
            'changeFrequency' => $this->categoryConfigReader->getChangeFrequency($storeId),
        ]);

        $sellers = $this->sellerRepository->getAllDisplayedSellers($storeId);

        $sellerItems = array_map(function (SellerInterface $seller) use ($storeId) {
            return $this->sitemapItemFactory->create([
                'url'             => $this->urlHelper->getSellerUrl($seller, ['_request_path_only' => 1]),
                'updatedAt'       => $seller->getUpdatedAt(),
                'images'          => null,
                'priority'        => $this->categoryConfigReader->getPriority($storeId),
                'changeFrequency' => $this->categoryConfigReader->getChangeFrequency($storeId),
            ]);
        }, $sellers);

        $sitemapItems = array_merge([$sellerListingItem], $sellerItems);

        return $sitemapItems;
    }
}
