<?php

namespace Marketplacer\Seller\Observer\Seller;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Helper\Config as ConfigHelper;

class SellerDeleteBefore implements ObserverInterface
{
    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @param ConfigHelper $configHelper
     */
    public function __construct(ConfigHelper $configHelper)
    {
        $this->configHelper = $configHelper;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /**
         * @var $seller SellerInterface
         */
        $seller = $observer->getEvent()->getData('data_object');
        if (!$seller instanceof SellerInterface || !$seller->getSellerId()) {
            return;
        }

        if ($this->configHelper->getGeneralSellerId() == $seller->getSellerId()) {
            throw new LocalizedException(__('You cannot delete Marketplacer General Seller record'));
        }
    }
}
