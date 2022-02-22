<?php

namespace Marketplacer\Seller\Model\ResourceModel\Seller\Grid;

use Marketplacer\Base\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idEntityKey = 'seller_id';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(
            'Magento\Framework\View\Element\UiComponent\DataProvider\Document',
            'Marketplacer\Seller\Model\ResourceModel\Seller'
        );
    }
}
