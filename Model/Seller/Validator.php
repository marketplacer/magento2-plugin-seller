<?php

namespace Marketplacer\Seller\Model\Seller;

use Magento\Framework\Exception\LocalizedException;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Model\Seller;

class Validator
{
    /**
     * @var array
     */
    protected $requiredFields = [
        SellerInterface::NAME          => 'Name',
        SellerInterface::PHONE         => 'Phone',
        SellerInterface::EMAIL_ADDRESS => 'Email Address',
        SellerInterface::ADDRESS       => 'Address',
    ];

    /**
     * @param SellerInterface $seller
     * @param bool $onlyExistingData
     * @return void
     * @throws LocalizedException
     */
    public function validate(SellerInterface $seller, $onlyExistingData = false)
    {
        if ($seller->getData('_skip_validation_flag')) {
            return;
        }

        /**
         * @var $seller Seller
         */
        if (!$onlyExistingData) {
            foreach ($this->requiredFields as $dataKey => $fieldName) {
                if (!$seller->hasData($dataKey)) {
                    throw new LocalizedException(__('%1 is required', $fieldName));
                }
            }
        }

        foreach ($this->requiredFields as $dataKey => $fieldName) {
            if ($seller->hasData($dataKey) && empty($seller->getData($dataKey))) {
                throw new LocalizedException(__('%1 is required and can\'t be empty', $fieldName));
            }
        }
    }
}
