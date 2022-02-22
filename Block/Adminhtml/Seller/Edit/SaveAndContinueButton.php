<?php

namespace Marketplacer\Seller\Block\Adminhtml\Seller\Edit;

use Magento\Backend\Block\Widget\Context;
use Marketplacer\Seller\Helper\Config;

/**
 * Class SaveButton
 * @package Marketplacer\Seller\Controller\Adminhtml\Seller\Edit
 */
class SaveAndContinueButton extends \Marketplacer\Base\Block\Adminhtml\Entity\Edit\SaveAndContinueButton
{
    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @param Context $context
     * @param Config $configHelper
     */
    public function __construct(Context $context, Config $configHelper)
    {
        parent::__construct($context);
        $this->configHelper = $configHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        if (!$this->configHelper->isAdminEditAllowed()) {
            return [];
        }

        return parent::getButtonData();
    }
}
