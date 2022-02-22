<?php

namespace Marketplacer\Seller\Model\ResourceModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\Store;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Helper\Config;

/**
 * Class Seller
 * @package Marketplacer\Seller\Model\ResourceModel
 */
class Seller extends AbstractDb
{
    public const SELLER_TABLE_NAME = 'marketplacer_seller';

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getAllSellerIds()
    {
        $connection = $this->getConnection();

        $idsSelect = $connection
            ->select()
            ->from($this->getMainTable())
            ->reset(Select::COLUMNS)
            ->columns(SellerInterface::SELLER_ID)
            ->distinct(true);

        return $connection->fetchCol($idsSelect);
    }

    /**
     * Initialization
     * @return void
     */
    protected function _construct()
    {
        $this->_init(static::SELLER_TABLE_NAME, SellerInterface::ROW_ID);
    }

    /**
     * @param SellerInterface | AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if (null === $object->getStatus()) {
            $object->setStatus(SellerInterface::STATUS_ENABLED);
        }

        return parent::_beforeSave($object);
    }

    /**
     * @param SellerInterface | AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        if ($object->getData('_regenerate_url')
            || ($object->getOrigData() && ($object->dataHasChangedFor('name') || $object->dataHasChangedFor('url_key')))
        ) {
            $object->processUrlRewrites();
        }

        return parent::_afterSave($object);
    }

    /**
     * @param SellerInterface | AbstractModel $object
     * @return Seller
     * @throws LocalizedException
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        $connection = $this->getConnection();

        $configSelect = $connection
            ->select()
            ->from(['configs' => $connection->getTableName('core_config_data')], 'value')
            ->where('configs.path = ?', Config::XML_PATH_GENERAL_SELLER_ID)
            ->where('configs.scope = ?', ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
            ->where('configs.scope_id = ?', Store::DEFAULT_STORE_ID);

        $existingConfigId = $connection->fetchOne($configSelect);

        //remove records related to other stores if exist
        $connection->delete(
            $this->getMainTable(),
            $connection->quoteInto('seller_id = ?', $object->getSellerId())
            . ' AND '
            . $connection->quoteInto('store_id != ?', $object->getStoreId())
        );

        $object->deleteUrlRewrites();

        return parent::_afterDelete($object);
    }

    /**
     * @param SellerInterface | AbstractModel $object
     * @return Seller
     * @throws LocalizedException
     */
    protected function _afterDelete(AbstractModel $object)
    {
        $connection = $this->getConnection();

        //remove records related to other stores if exist
        $connection->delete(
            $this->getMainTable(),
            $connection->quoteInto('seller_id = ?', $object->getSellerId())
            . ' AND '
            . $connection->quoteInto('store_id != ?', $object->getStoreId())
        );

        $object->deleteUrlRewrites();

        return parent::_afterDelete($object);
    }
}
