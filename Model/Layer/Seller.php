<?php

namespace Marketplacer\Seller\Model\Layer;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Layer\Category;
use Magento\Catalog\Model\Layer\ContextInterface;
use Magento\Catalog\Model\Layer\StateFactory;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\SellerApi\Api\SellerAttributeRetrieverInterface;

class Seller extends Category
{
    const LAYER_NAME = 'seller_category_layer';

    /**
     * @var SellerAttributeRetrieverInterface
     */
    protected $sellerAttributeRetriever;

    /**
     * @param ContextInterface $context
     * @param StateFactory $layerStateFactory
     * @param CollectionFactory $attributeCollectionFactory
     * @param Product $catalogProduct
     * @param StoreManagerInterface $storeManager
     * @param Registry $registry
     * @param CategoryRepositoryInterface $categoryRepository
     * @param SellerAttributeRetrieverInterface $sellerAttributeRetriever
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        StateFactory $layerStateFactory,
        CollectionFactory $attributeCollectionFactory,
        Product $catalogProduct,
        StoreManagerInterface $storeManager,
        Registry $registry,
        CategoryRepositoryInterface $categoryRepository,
        SellerAttributeRetrieverInterface $sellerAttributeRetriever,
        array $data = []
    ) {
        parent::__construct($context, $layerStateFactory, $attributeCollectionFactory, $catalogProduct, $storeManager,
            $registry, $categoryRepository, $data);

        $this->sellerAttributeRetriever = $sellerAttributeRetriever;
    }

    /**
     * Retrieve current layer product collection
     * @return Collection
     */
    public function getProductCollection()
    {
        $seller = $this->getCurrentSeller();
        if (isset($this->_productCollections[$seller->getOptionId()])) {
            $collection = $this->_productCollections[$seller->getOptionId()];
        } else {
            $collection = $this->collectionProvider
                ->getCollection($this->getCurrentCategory())
                ->addFieldToFilter($this->sellerAttributeRetriever->getAttributeCode(), $seller->getOptionId());

            $this->prepareProductCollection($collection);
            $this->_productCollections[$seller->getOptionId()] = $collection;
        }

        return $collection;
    }

    /**
     * @return SellerInterface
     */
    public function getCurrentSeller()
    {
        $currentSeller = $this->_getData(self::LAYER_NAME);
        if ($currentSeller === null && ($currentSeller = $this->registry->registry('current_seller'))) {
            $this->setData(self::LAYER_NAME, $currentSeller);
        }
        return $currentSeller;
    }
}
