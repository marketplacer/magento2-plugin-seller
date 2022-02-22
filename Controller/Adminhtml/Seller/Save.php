<?php

namespace Marketplacer\Seller\Controller\Adminhtml\Seller;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Marketplacer\Base\Api\CacheInvalidatorInterface;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Api\Data\SellerInterfaceFactory;
use Marketplacer\Seller\Api\SellerRepositoryInterface;
use Marketplacer\Seller\Helper\Config;
use Marketplacer\Seller\Model\Seller;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Class Save
 * @package Marketplacer\Seller\Controller\Adminhtml\Seller
 */
class Save extends AbstractSellerAction
{
    /**
     * @var SellerInterfaceFactory
     */
    protected $sellerFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Save constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param SellerInterfaceFactory $sellerFactory
     * @param SellerRepositoryInterface $sellerRepository
     * @param StoreManagerInterface $storeManager
     * @param Config $configHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        SellerInterfaceFactory $sellerFactory,
        SellerRepositoryInterface $sellerRepository,
        StoreManagerInterface $storeManager,
        Config $configHelper,
        CacheInvalidatorInterface $cacheInvalidator,
        LoggerInterface $logger
    ) {
        parent::__construct($context, $resultPageFactory, $sellerRepository, $configHelper, $cacheInvalidator);
        $this->storeManager = $storeManager;
        $this->sellerFactory = $sellerFactory;
        $this->logger = $logger;
    }

    /**
     * Save action
     * @return ResultInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        /**
         * @var $seller Seller
         */
        if (!$this->isAdminEditAllowed()) {
            return $this->processEditNotAllowedRedirect();
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data = $this->getRequest()->getPostValue()) {
            $hasError = false;

            $sellerId = $data[SellerInterface::SELLER_ID] ?? null;
            if (empty($data[SellerInterface::STORE_ID])) {
                $data[SellerInterface::STORE_ID] = Store::DEFAULT_STORE_ID;
            }
            $storeId = $data[SellerInterface::STORE_ID];

            unset($data['row_id'], $data['url_key']);

            if ($sellerId) {
                $seller = $this->sellerRepository->getById($sellerId, $storeId);
            } else {
                $seller = $this->sellerFactory->create();
            }
            $seller->addData($data);

            try {
                $this->sellerRepository->save($seller);
                $this->cacheInvalidator->invalidate();
                $this->messageManager->addSuccessMessage(__('You saved Seller'));
            } catch (LocalizedException $e) {
                $hasError = true;
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Throwable $e) {
                $hasError = true;
                $this->logger->critical($e->getMessage());
                $this->messageManager->addErrorMessage('An error error occurred by saving the seller.');
            }

            $sellerId = $seller->getSellerId();
            if (($hasError || $this->getRequest()->getParam('back')) && $sellerId) {
                $params = [
                    'seller_id' => $sellerId,
                    '_current'  => true,
                    'store'     => $seller->getStoreId(),
                ];
                return $this->resultRedirectFactory->create()->setPath('*/*/edit', $params);
            }
        }

        return $resultRedirect->setPath('*/*/index');
    }
}
