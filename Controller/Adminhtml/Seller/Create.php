<?php

namespace Marketplacer\Seller\Controller\Adminhtml\Seller;

use Magento\Framework\Controller\ResultInterface;

/**
 * Class Create
 * @package Marketplacer\Seller\Controller\Adminhtml\Seller
 */
class Create extends AbstractSellerAction
{
    /**
     * Create seller form
     *
     * @return ResultInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        if (!$this->isAdminEditAllowed()) {
            return $this->processEditNotAllowedRedirect();
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Sellers'));
        $resultPage->getConfig()->getTitle()->prepend(__('New Seller'));

        return $resultPage;
    }
}
