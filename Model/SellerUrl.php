<?php

namespace Marketplacer\Seller\Model;

use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Helper\Url as UrlHelper;
use Marketplacer\SellerApi\Api\Data\MarketplacerSellerInterface;

class SellerUrl implements \Marketplacer\SellerApi\Api\MarketplacerSellerUrlInterface
{
    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * @param UrlHelper $urlHelper
     */
    public function __construct(UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param SellerInterface | MarketplacerSellerInterface $seller
     * @return string|null
     */
    public function getSellerUrl(MarketplacerSellerInterface $seller)
    {
        try {
            $url = $this->urlHelper->getSellerUrlById($seller->getSellerId(), $seller->getStoreId());
        } catch (\Throwable $e) {
            return null;
        }

        return $url;
    }
}
