<?php

namespace Marketplacer\Seller\Block\Adminhtml\Order;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;

/**
 * Class View
 * @package Marketplacer\Seller\Block\Adminhtml\Order
 */
class View extends Widget
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * View constructor.
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(Context $context, Registry $registry, array $data = [])
    {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed|null
     * @throws LocalizedException
     */
    public function getOrder()
    {
        if ($this->registry->registry('current_order')) {
            return $this->registry->registry('current_order');
        }
        if ($this->registry->registry('order')) {
            return $this->registry->registry('order');
        }
        throw new LocalizedException(__('We can\'t get the order instance right now.'));
    }
}
