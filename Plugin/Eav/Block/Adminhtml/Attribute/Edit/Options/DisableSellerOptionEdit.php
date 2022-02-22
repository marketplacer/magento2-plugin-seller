<?php

namespace Marketplacer\Seller\Plugin\Eav\Block\Adminhtml\Attribute\Edit\Options;

use Magento\Eav\Block\Adminhtml\Attribute\Edit\Options\Options as OptionsBlock;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Registry;
use Marketplacer\SellerApi\Api\SellerAttributeRetrieverInterface;

class DisableSellerOptionEdit
{
    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var SellerAttributeRetrieverInterface
     */
    protected $sellerAttributeRetriever;

    /**
     * @param Registry $registry
     * @param SellerAttributeRetrieverInterface $sellerAttributeRetriever
     */
    public function __construct(
        Registry $registry,
        SellerAttributeRetrieverInterface $sellerAttributeRetriever
    ) {
        $this->_registry = $registry;
        $this->sellerAttributeRetriever = $sellerAttributeRetriever;
    }

    /**
     * @param OptionsBlock $subject
     * @param string $key
     * @param null $index
     * @param mixed $result
     * @return bool | mixed
     */
    public function afterGetData(OptionsBlock $subject, $result = null, $key = '', $index = null)
    {
        if ('read_only' !== $key) {
            return $result;
        }

        $attribute = $this->getAttribute();

        if ($attribute && $this->sellerAttributeRetriever->getAttributeCode() === $attribute->getAttributeCode()) {
            $result = true;
            return $result;
        }

        return $result;
    }

    /**
     * Retrieve attribute object from registry
     *
     * @return AbstractAttribute
     * @codeCoverageIgnore
     * @see \Magento\Eav\Block\Adminhtml\Attribute\Edit\Options\Options::getAttributeObject
     *
     */
    protected function getAttribute()
    {
        return $this->_registry->registry('entity_attribute');
    }
}
