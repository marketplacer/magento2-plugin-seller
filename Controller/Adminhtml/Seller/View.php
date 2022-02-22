<?php

namespace Marketplacer\Seller\Controller\Adminhtml\Seller;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class View
 * @package Marketplacer\Seller\Controller\Adminhtml\Seller
 */
class View extends AbstractSellerAction
{
    /**
     * Edit seller
     * @return ResultInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $sellerId = $this->getRequest()->getParam('seller_id');
        try {
            $this->sellerRepository->getById($sellerId);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('The seller with ID "%1" does not exist.', $sellerId ?? ''));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/');
        }
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Sellers'));
        $resultPage->getConfig()->getTitle()->prepend(__('View Seller'));

        return $resultPage;
    }
}
