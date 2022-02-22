<?php

namespace Marketplacer\Seller\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\SellerApi\Api\Data\MarketplacerSellerSearchResultsInterface;
use Marketplacer\SellerApi\Api\Data\MarketplacerSellerInterface;

interface SellerRepositoryInterface extends \Marketplacer\SellerApi\Api\SellerRepositoryInterface
{
    /**
     * @param int | string $sellerId
     * @param int | string | null $storeId
     * @return SellerInterface | MarketplacerSellerInterface
     * @throws NoSuchEntityException
     */
    public function getById($sellerId, $storeId = null);

    /**
     * @param array $sellerIds
     * @param int | string | null $storeId
     * @return SellerInterface[] | MarketplacerSellerInterface[]
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getByIds(array $sellerIds = [], $storeId = null);

    /**
     * @return SellerInterface[] | MarketplacerSellerInterface[]
     * @throws LocalizedException
     */
    public function getAllSellerIds();

    /**
     * @param int|string|null $storeId
     * @return SellerInterface[] | MarketplacerSellerInterface[]
     * @throws LocalizedException
     */
    public function getAllDisplayedSellers($storeId = null);

    /**
     * Get list of sellers
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return MarketplacerSellerSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param SellerInterface | MarketplacerSellerInterface $seller
     * @return SellerInterface | MarketplacerSellerInterface
     * @throws LocalizedException
     */
    public function save($seller);

    /**
     * @param int | string $sellerId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($sellerId);
}
