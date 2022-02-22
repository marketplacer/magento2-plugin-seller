<?php

namespace Marketplacer\Seller\Controller\Adminhtml\Seller;

use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Throwable;

/**
 * Class Save
 * @package Marketplacer\Seller\Controller\Adminhtml\Seller
 */
class Delete extends AbstractSellerAction
{
    /**
     * Save action
     * @return ResultInterface
     */
    public function execute()
    {
        if (!$this->isAdminEditAllowed()) {
            return $this->processEditNotAllowedRedirect();
        }

        $sellerId = $this->getRequest()->getParam('seller_id');

        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $this->sellerRepository->deleteById($sellerId);
            $this->cacheInvalidator->invalidate();
            $this->messageManager->addSuccessMessage(__('Seller successfully deleted [ID: "%1"]', $sellerId));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                __('Unable to delete Seller ID "%1". Error: %2', $sellerId ?? '', $e->getMessage()),
            );
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index');
        } catch (Throwable $e) {
            $this->messageManager->addErrorMessage(__('An error occurred by deleting of seller with ID "%1"',
                $sellerId ?? ''));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index');
        }

        return $resultRedirect->setPath('*/*/index');
    }
}
