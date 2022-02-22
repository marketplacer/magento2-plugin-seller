<?php

namespace Marketplacer\Seller\Block\Adminhtml\Seller\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Marketplacer\Base\Block\Adminhtml\Entity\Edit\GenericButton;
use Marketplacer\Seller\Helper\Config;

/**
 * Class SaveButton
 * @package Marketplacer\Seller\Controller\Adminhtml\Seller\Edit
 */
class SaveButton extends GenericButton implements ButtonProviderInterface
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
            'label'          => __('Save Seller'),
            'class'          => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order'     => 90,
        ];
    }
}
