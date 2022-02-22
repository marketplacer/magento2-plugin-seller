<?php

namespace Marketplacer\Seller\Model\Seller\Webapi;

use Marketplacer\Seller\Api\Data\SellerInterface;

class SellerOutputProcessor
{
    protected const ALLOWED_API_FIELDS = [
        'id',
        'store_image',
        'phone',
        'logo',
        'name',
        'address',
        'opening_hours',
        'business_number',
        'policies',
        'description',
        'email_address',
        'shipping_policy',
    ];

    /**
     * Filter result output array
     *
     * @param SellerInterface $seller
     * @param array $result
     * @return array
     */
    public function execute(
        SellerInterface $seller,
        array $result
    ): array {
        $filteredResult = [];
        foreach (self::ALLOWED_API_FIELDS as $allowedFieldName) {
            if (array_key_exists($allowedFieldName, $result)) {
                $filteredResult[$allowedFieldName] = $result[$allowedFieldName];
            } else {
                $filteredResult[$allowedFieldName] = '';
            }
        }

        $filteredResult['id'] = $seller->getSellerId();

        return $filteredResult;
    }
}
