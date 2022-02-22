<?php

namespace Marketplacer\Seller\Api\Data;

use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;
use Marketplacer\SellerApi\Api\Data\MarketplacerSellerInterface;

/**
 * @method hasStoreId() bool
 */
interface SellerInterface extends MarketplacerSellerInterface
{
    public const ROW_ID = 'row_id';
//    public const SELLER_ID = 'seller_id';                   //part of parent MarketplacerSellerInterface
    public const STORE_ID = 'store_id';
    public const OPTION_ID = 'option_id';
    public const STATUS = 'status';
    public const SORT_ORDER = 'sort_order';
//    public const NAME = 'name';                               //part of parent MarketplacerSellerInterface
//    public const LOGO = 'logo';                               //part of parent MarketplacerSellerInterface
//    public const STORE_IMAGE = 'store_image';                 //part of parent MarketplacerSellerInterface
//    public const PHONE = 'phone';                             //part of parent MarketplacerSellerInterface
//    public const ADDRESS = 'address';                         //part of parent MarketplacerSellerInterface
//    public const DESCRIPTION = 'description';                 //part of parent MarketplacerSellerInterface
//    public const OPENING_HOURS = 'opening_hours';             //part of parent MarketplacerSellerInterface
//    public const BUSINESS_NUMBER = 'business_number';         //part of parent MarketplacerSellerInterface
//    public const POLICIES = 'policies';                       //part of parent MarketplacerSellerInterface
//    public const EMAIL_ADDRESS = 'email_address';             //part of parent MarketplacerSellerInterface
    public const URL_KEY = 'url_key';
    public const META_TITLE = 'meta_title';
    public const META_DESCRIPTION = 'meta_description';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    public const STATUS_ENABLED = '1';
    public const STATUS_DISABLED = '0';

    public const SELLER_NAME_GENERAL = 'General Seller';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getRowId();

    /**
     * @param int $rowId
     * @return SellerInterface
     */
    public function setRowId($rowId);

    /**
     * Get Seller Store ID
     *
     * @return int|null
     */
    public function getStoreId();

    /**
     * @param int $storeId
     * @return SellerInterface
     */
    public function setStoreId($storeId);

    /**
     * Get Attribute option ID
     *
     * @return int|null
     */
    public function getOptionId();

    /**
     * @param int $optionId
     * @return SellerInterface
     */
    public function setOptionId($optionId);

    /**
     * Get Status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * @param int $status
     * @return SellerInterface
     */
    public function setStatus($status);

    /**
     * @return bool
     */
    public function isEnabled();

    /**
     * Get Seller Sort order
     *
     * @return int|null
     */
    public function getSortOrder();

    /**
     * @param int $sortOrder
     * @return SellerInterface
     */
    public function setSortOrder($sortOrder);

    /**
     * @return string
     */
    public function getUrlKey();

    /**
     * @param string $urlKey
     * @return SellerInterface
     */
    public function setUrlKey($urlKey);

    /**
     * @return string
     */
    public function getMetaTitle();

    /**
     * @param string $metaTitle
     * @return SellerInterface
     */
    public function setMetaTitle($metaTitle);

    /**
     * @return string
     */
    public function getMetaDescription();

    /**
     * @param string $metaDescription
     * @return SellerInterface
     */
    public function setMetaDescription($metaDescription);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     * @return SellerInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     * @return SellerInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return AttributeOptionInterface
     * @throws NoSuchEntityException
     */
    public function getAttributeOption();

    /**
     * @return bool
     */
    public function hasAttributeOption();

    /**
     * @param AttributeOptionInterface $attributeOption
     * @return $this
     */
    public function setAttributeOption(AttributeOptionInterface $attributeOption);

    /**
     * Clean url key
     * @param string $urlKey
     * @return string
     * @throws ValidatorException
     */
    public function getSanitizedUrlKey($urlKey);

    /**
     * Validate url key
     * @param string $urlKey
     * @return true If url key is valid
     * @throws ValidatorException
     */
    public function validateUrlKey($urlKey);

    /**
     * Process url rewrites
     *
     * @return $this
     */
    public function processUrlRewrites();

    /**
     * Delete url rewrites
     *
     * @return $this
     */
    public function deleteUrlRewrites();
}
