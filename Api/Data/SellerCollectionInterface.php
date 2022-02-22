<?php

namespace Marketplacer\Seller\Api\Data;

interface SellerCollectionInterface
{
    /**
     * @param int | string $sellerId
     * @return $this
     */
    public function addSellerIdToFilter($sellerId);

    /**
     * @param string $name
     * @return $this
     */
    public function addSellerNameToFilter($name);

    /**
     * @return $this
     */
    public function addWithNameToFilter();
}
