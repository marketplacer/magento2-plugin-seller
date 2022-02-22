<?php

namespace Marketplacer\Seller\Block\Seller;

use Magento\Catalog\Helper\Category as CategoryHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Asset\GroupedCollection as PageAsset;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Api\SellerRepositoryInterface;
use Marketplacer\Seller\Helper\Config as ConfigHelper;
use Marketplacer\Seller\Helper\Url as UrlHelper;
use Marketplacer\SellerApi\Api\SellerAttributeRetrieverInterface;

class Index extends Template
{
    const ASSET_CANONICAL = 'canonical';

    /**
     * @var SellerRepositoryInterface
     */
    protected $sellerRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var SellerAttributeRetrieverInterface
     */
    protected $attributeRetriever;

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
     * Seller constructor.
     * @param Template\Context $context
     * @param SellerRepositoryInterface $sellerRepository
     * @param StoreManagerInterface $storeManager
     * @param SellerAttributeRetrieverInterface $attributeRetriever
     * @param ConfigHelper $configHelper
     * @param UrlHelper $urlHelper
     * @param PageAsset|null $pageAsset
     * @param CategoryHelper|null $categoryHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        SellerRepositoryInterface $sellerRepository,
        StoreManagerInterface $storeManager,
        SellerAttributeRetrieverInterface $attributeRetriever,
        ConfigHelper $configHelper,
        UrlHelper $urlHelper,
        PageAsset $pageAsset,
        CategoryHelper $categoryHelper,
        array $data = []
    ) {
        $this->sellerRepository = $sellerRepository;
        $this->storeManager = $storeManager;
        $this->attributeRetriever = $attributeRetriever;
        $this->configHelper = $configHelper;
        $this->urlHelper = $urlHelper;
        $this->pageAsset = $pageAsset;
        $this->categoryHelper = $categoryHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return SellerInterface[]
     * @throws NoSuchEntityException
     */
    public function getAllDisplayedSellers()
    {
        if (!$this->hasData('all_sellers')) {
            $currentStoreId = $this->storeManager->getStore()->getId();

            $this->setData('all_sellers', $this->sellerRepository->getAllDisplayedSellers($currentStoreId));
        }

        return $this->getData('all_sellers');
    }

    /**
     * Prepare global layout
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        $this->addBreadcrumbs();

        $this->addPageMetaInfo();

        if ($this->categoryHelper->canUseCanonicalTag()) {
            $this->addCanonicalUrl();
        }

        return parent::_prepareLayout();
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function addBreadcrumbs()
    {
        if (!$breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            return;
        }
        $storeId = $this->_storeManager->getStore()->getId();

        $breadcrumbsBlock->addCrumb('home', [
            'label' => __('Home'),
            'title' => __('Go to Home Page'),
            'link'  => $this->_storeManager->getStore()->getBaseUrl()
        ]);
        $listingTitle = $this->configHelper->getListingPageTitle($storeId) ?? __('Sellers');

        $breadcrumbsBlock->addCrumb('sellers', [
            'label' => $listingTitle,
            'title' => $listingTitle
        ]);
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function addPageMetaInfo()
    {
        $storeId = $this->_storeManager->getStore()->getId();

        $listingTitle = $this->configHelper->getListingPageTitle($storeId);

        $this->pageConfig->getTitle()->set($listingTitle);

        $listingMetaTitle = $this->configHelper->getListingMetaTitle() ?? $listingTitle;
        $listingMetaDescription = $this->configHelper->getListingMetaDescription() ?? $listingTitle;

        $this->pageConfig->setMetaTitle($listingMetaTitle);
        $this->pageConfig->setDescription($listingMetaDescription);

        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($this->_escaper->escapeHtml($listingTitle));
        }
    }

    /**
     * @return void
     * @throws NoSuchEntityException
     */
    protected function addCanonicalUrl()
    {
        foreach ($this->pageAsset->getAll() as $url => $asset) {
            if ($asset->getContentType() == self::ASSET_CANONICAL) {
                $this->pageAsset->remove($url);
            }
        }

        $this->pageConfig->addRemotePageAsset(
            $this->urlHelper->getSellerListingUrl(),
            self::ASSET_CANONICAL,
            ['attributes' => ['rel' => self::ASSET_CANONICAL]]
        );
    }
}
