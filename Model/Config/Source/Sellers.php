<?php

namespace Marketplacer\Seller\Model\Config\Source;

use Magento\Framework\Locale\ListsInterface;
use Magento\Framework\Option\ArrayInterface;
use Magento\Store\Model\Store;
use Marketplacer\Seller\Model\ResourceModel\Seller\CollectionFactory;

class Sellers implements ArrayInterface
{
    /**
     * @var ListsInterface
     */
    protected $_localeLists;

    /**
     * @var CollectionFactory
     */
    protected $sellerCollectionFactory;

    /**
     * @var array | null
     */
    protected $sellerOptions = null;

    /**
     * @param CollectionFactory $sellerCollectionFactory
     */
    public function __construct(CollectionFactory $sellerCollectionFactory)
    {
        $this->sellerCollectionFactory = $sellerCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (null === $this->sellerOptions) {
            $collection = $this->sellerCollectionFactory->create();
            $collection->addStoreIdToFilter(Store::DEFAULT_STORE_ID);

            $options = [];
            foreach ($collection as $seller) {
                $options[] = [
                    'label' => $seller->getName(),
                    'value' => $seller->getSellerId()
                ];
            }

            $this->sellerOptions = $options;
        }

        return $this->sellerOptions;
    }
}
