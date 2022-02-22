<?php

namespace Marketplacer\Seller\Block\Adminhtml\Seller\Index;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Marketplacer\Base\Block\Adminhtml\Entity\Edit\GenericButton;
use Marketplacer\Seller\Helper\Config;

/**
 * Class CreateButton
 * @package Marketplacer\Seller\Block\Adminhtml\Seller\Index
 */
class CreateButton extends GenericButton implements ButtonProviderInterface
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

        return [
            'label'      => __('Add New Seller'),
            'class'      => 'primary',
            'url'        => '*/*/create',
            'sort_order' => 90,
        ];
    }
}
