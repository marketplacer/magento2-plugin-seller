<?php

namespace Marketplacer\Seller\Model\Seller;

use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Api\Data\AttributeOptionLabelInterface;
use Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Model\Seller;

class SellerDataToOptionSetter
{
    /**
     * @var AttributeOptionLabelInterfaceFactory
     */
    protected $attributeOptionLabelFactory;

    /**
     * @param AttributeOptionLabelInterfaceFactory $attributeOptionLabelFactory
     */
    public function __construct(AttributeOptionLabelInterfaceFactory $attributeOptionLabelFactory)
    {
        $this->attributeOptionLabelFactory = $attributeOptionLabelFactory;
    }

    /**
     * @param SellerInterface $seller
     * @param AttributeOptionInterface $option
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function setFromSeller(SellerInterface $seller, AttributeOptionInterface $option)
    {
        /**
         * @var $seller Seller
         */
        if ($seller->hasData(SellerInterface::NAME)) {
            $sellerName = $seller->getName();
            if (empty($sellerName)) {
                throw new LocalizedException(__('Seller Name is required'));
            }
            $this->setOptionLabelsFromSeller($option, $seller);
        }

        if (!$option->hasData(AttributeOptionInterface::IS_DEFAULT)) {
            $option->setIsDefault(0);
        }

        if (!$option->hasData(AttributeOptionInterface::SORT_ORDER)) {
            $option->setSortOrder(0);
        }
    }

    /**
     * @param AttributeOptionInterface $option
     * @param SellerInterface $seller
     * @return AttributeOptionInterface
     */
    protected function setOptionLabelsFromSeller(AttributeOptionInterface $option, SellerInterface $seller)
    {
        $sellerName = $seller->getName();
        $storeId = $seller->getStoreId();
        if ($storeId == Store::DEFAULT_STORE_ID) {
            $option->setLabel($sellerName);
        } else {
            $storeLabels = $option->getStoreLabels();

            $storeLabelExist = false;
            if ($storeLabels) {
                foreach ($storeLabels as $storeLabel) {
                    if ($storeLabel->getStoreId() === $storeId) {
                        $storeLabel->setLabel($sellerName);
                        $storeLabelExist = true;
                        break;
                    }
                }
            }
            if (!$storeLabelExist) {
                $storeLabel = $this->attributeOptionLabelFactory->create();
                $storeLabel->setData([
                    AttributeOptionLabelInterface::LABEL    => $sellerName,
                    AttributeOptionLabelInterface::STORE_ID => $storeId,
                ]);
                $storeLabels[] = $storeLabel;
            }

            $option->setStoreLabels($storeLabels);
        }

        return $option;
    }
}
