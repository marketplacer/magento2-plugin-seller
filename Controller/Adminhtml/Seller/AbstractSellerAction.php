<?php

namespace Marketplacer\Seller\Controller\Adminhtml\Seller;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\View\Result\PageFactory;
use Marketplacer\Base\Api\CacheInvalidatorInterface;
use Marketplacer\Seller\Api\SellerRepositoryInterface;
use Marketplacer\Seller\Helper\Config;

/**
 * Class AbstractSellerEditAction
 * @package Marketplacer\Seller\Controller\Adminhtml\Seller
 */
abstract class AbstractSellerAction extends Action
{
    const ADMIN_RESOURCE = 'Marketplacer_Seller::seller';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var SellerRepositoryInterface
     */
    protected $sellerRepository;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var CacheInvalidator
     */
    protected $cacheInvalidator;

    /**
     * Edit constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param SellerRepositoryInterface $sellerRepository
     * @param Config $configHelper
     * @param CacheInvalidatorInterface $cacheInvalidator
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        SellerRepositoryInterface $sellerRepository,
        Config $configHelper,
        CacheInvalidatorInterface $cacheInvalidator
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->sellerRepository = $sellerRepository;
        $this->configHelper = $configHelper;
        $this->cacheInvalidator = $cacheInvalidator;
    }

    /**
     * @return bool
     */
    protected function isAdminEditAllowed()
    {
        if (!$this->configHelper->isAdminEditAllowed()) {
            return false;
        }

        return true;
    }

    /**
     * @return Redirect
     */
    protected function processEditNotAllowedRedirect()
    {
        $this->messageManager->addErrorMessage(__('Seller editing by admin is not allowed in configuration'));
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/index');
    }
}
