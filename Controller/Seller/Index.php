<?php

namespace Marketplacer\Seller\Controller\Seller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Marketplacer\Seller\Helper\Config as ConfigHelper;

/**
 * Class Index
 * @package Marketplacer\Seller\Controller\Seller
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ConfigHelper $configHelper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->configHelper = $configHelper;
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        if (!$this->configHelper->isEnabledOnStorefront()) {
            $this->_redirect('noroute');
        }

        return $this->resultPageFactory->create();
    }
}
