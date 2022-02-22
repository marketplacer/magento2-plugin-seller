<?php

namespace Marketplacer\Seller\Helper;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Api\SellerRepositoryInterface;
use Marketplacer\Seller\Helper\Url as UrlHelper;
use Marketplacer\SellerApi\Api\SellerAttributeRetrieverInterface;

class Data extends AbstractHelper
{
    public const BULK_OPERATIONS_CHUNK_SIZE = 100;

    /**
     * @var SellerAttributeRetrieverInterface
     */
    protected $sellerAttributeRetriever;

    /**
     * @var SellerRepositoryInterface
     */
    protected $sellerRepository;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * Data constructor.
     * @param Context $context
     * @param SellerAttributeRetrieverInterface $sellerAttributeRetriever
     * @param SellerRepositoryInterface $sellerRepository
     * @param UrlHelper $urlHelper
     */
    public function __construct(
        Context $context,
        SellerAttributeRetrieverInterface $sellerAttributeRetriever,
        SellerRepositoryInterface $sellerRepository,
        UrlHelper $urlHelper
    ) {
        parent::__construct($context);
        $this->sellerAttributeRetriever = $sellerAttributeRetriever;
        $this->sellerRepository = $sellerRepository;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param Product $product
     * @return SellerInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getSellerByProduct(Product $product)
    {
        $sellerId = $product->getData($this->sellerAttributeRetriever->getAttributeCode());
        if (!$sellerId) {
            return null;
        }

        try {
            $sellerEntity = $this->sellerRepository->getById($sellerId, $product->getStoreId());
        } catch (NoSuchEntityException $e) {
            return null;
        }
        return $sellerEntity;
    }

    /**
     * @param SellerInterface $seller
     * @return string|null
     */
    public function getSellerUrl(SellerInterface $seller)
    {
        try {
            $url = $this->urlHelper->getSellerUrlById($seller->getSellerId(), $seller->getStoreId());
        } catch (Exception $e) {
            return null;
        }
        return $url;
    }
}
