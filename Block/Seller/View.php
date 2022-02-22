<?php

namespace Marketplacer\Seller\Block\Seller;

use Magento\Catalog\Helper\Category as CategoryHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Asset\GroupedCollection as PageAsset;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Helper\Config as ConfigHelper;
use Marketplacer\Seller\Helper\Url as UrlHelper;
use Marketplacer\Seller\Model\ResourceModel\Seller\CollectionFactory as SellerCollectionFactory;

/**
 * Class View
 * @package Marketplacer\Seller\Block\Seller
 */
class View extends Template implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'marketplacer_seller_view';

    const ASSET_CANONICAL = 'canonical';

    /**
     * @var SellerCollectionFactory
     */
    protected $sellerCollectionFactory;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * @var PageAsset
     */
    protected $pageAsset;

    /**
     * @var CategoryHelper
     */
    protected $categoryHelper;

    /**
     * View constructor.
     * @param Template\Context $context
     * @param Registry $coreRegistry
     * @param StoreManagerInterface $storeManager
     * @param SellerCollectionFactory $sellerCollectionFactory
     * @param ConfigHelper $configHelper
     * @param UrlHelper $urlHelper
     * @param PageAsset|null $pageAsset
     * @param CategoryHelper|null $categoryHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $coreRegistry,
        StoreManagerInterface $storeManager,
        SellerCollectionFactory $sellerCollectionFactory,
        ConfigHelper $configHelper,
        UrlHelper $urlHelper,
        PageAsset $pageAsset,
        CategoryHelper $categoryHelper,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->storeManager = $storeManager;
        $this->sellerCollectionFactory = $sellerCollectionFactory;
        $this->configHelper = $configHelper;
        $this->urlHelper = $urlHelper;
        $this->pageAsset = $pageAsset;
        $this->categoryHelper = $categoryHelper;

        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * @return SellerInterface | null
     */
    public function getCurrentSeller()
    {
        if (!$this->hasData('current_seller')) {
            $this->setData('current_seller', $this->coreRegistry->registry('current_seller'));
        }
        return $this->getData('current_seller');
    }

    /**
     * Prepare global layout
     *
     * @return $this
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function _prepareLayout()
    {
        $seller = $this->getCurrentSeller();

        if ($seller) {
            $this->addBreadcrumbs($seller);

            $this->addPageMetaInfo($seller);

            if ($this->categoryHelper->canUseCanonicalTag()) {
                $this->addCanonicalUrl($seller);
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function addBreadcrumbs(SellerInterface $seller)
    {
        if (!$breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            return;
        }
        $storeId = $this->_storeManager->getStore()->getId();

        //home page crumb
        $breadcrumbsBlock->addCrumb('home', [
            'label' => __('Home'),
            'title' => __('Go to Home Page'),
            'link'  => $this->_storeManager->getStore()->getBaseUrl()
        ]);

        //listing crumb
        $listingTitle = $this->configHelper->getListingPageTitle($storeId) ?? __('Sellers');

        $breadcrumbsBlock->addCrumb('sellers', [
            'label' => $listingTitle,
            'title' => $listingTitle,
            'link'  => $this->urlHelper->getSellerListingUrl($storeId),
        ]);

        //seller crumb
        $breadcrumbsBlock->addCrumb('seller', [
            'label' => $seller->getName(),
            'title' => $seller->getName()
        ]);
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function addPageMetaInfo(SellerInterface $seller)
    {
        $storeId = $this->_storeManager->getStore()->getId();

        $listingTitle = $this->configHelper->getListingPageTitle($storeId);
        $sellerTitle = $seller->getName();

        $this->pageConfig->getTitle()->prepend($listingTitle);
        $this->pageConfig->getTitle()->prepend($sellerTitle);

        $sellerMetaTitle = $seller->getMetaTitle() ?? $sellerTitle;
        $sellerMetaDescription = $seller->getMetaDescription() ?? $seller->getDescription();

        $this->pageConfig->setMetaTitle($sellerMetaTitle);
        $this->pageConfig->setDescription($sellerMetaDescription);

        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($this->_escaper->escapeHtml($sellerTitle));
        }
    }

    /**
     * @return void
     * @throws NoSuchEntityException
     */
    protected function addCanonicalUrl(SellerInterface $seller)
    {
        foreach ($this->pageAsset->getAll() as $url => $asset) {
            if ($asset->getContentType() == self::ASSET_CANONICAL) {
                $this->pageAsset->remove($url);
            }
        }

        $this->pageConfig->addRemotePageAsset(
            $this->urlHelper->getSellerUrl($seller),
            self::ASSET_CANONICAL,
            ['attributes' => ['rel' => self::ASSET_CANONICAL]]
        );
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getCurrentSeller()->getSellerId()];
    }
}
