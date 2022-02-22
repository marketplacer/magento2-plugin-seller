<?php

namespace Marketplacer\Seller\Plugin\LayeredNavigation\Block;

use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\LayeredNavigation\Block\Navigation;
use Marketplacer\SellerApi\Api\SellerAttributeRetrieverInterface;
use Throwable;

class HideFilterOnSellerPagePlugin
{
    /**
     * @var SellerAttributeRetrieverInterface
     */
    protected $sellerAttributeRetriever;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param RequestInterface $request
     * @param SellerAttributeRetrieverInterface $sellerAttributeRetriever
     */
    public function __construct(RequestInterface $request, SellerAttributeRetrieverInterface $sellerAttributeRetriever)
    {
        $this->request = $request;
        $this->sellerAttributeRetriever = $sellerAttributeRetriever;
    }

    /**
     * Hide seller attribute filter in layered navigation on seller page only
     *
     * @param Navigation $itemRepository
     * @param array $resultItems
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterGetFilters(
        Navigation $itemRepository,
        array $resultItems
    ) {
        if ('marketplacer_seller_view' !== $this->request->getFullActionName()) {
            return $resultItems;
        }

        try {
            $sellerAttributeCode = $this->sellerAttributeRetriever->getAttributeCode();
            $resultItems = array_filter($resultItems, function ($filter) use ($sellerAttributeCode) {
                if (!$filter instanceof AbstractFilter
                    || !$filter->getData('attribute_model')
                    || $filter->getAttributeModel()->getAttributeCode() !== $sellerAttributeCode) {
                    return $filter;
                }
                return null;
            });
        } catch (Throwable $exception) {
            return $resultItems;
        }

        return $resultItems;
    }
}
