<?php

namespace Marketplacer\Seller\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\Store;
use Magento\Ui\Component\Listing\Columns\Column;
use Marketplacer\Seller\Api\Data\SellerInterface;
use Marketplacer\Seller\Helper\Config;

class SellerActions extends Column
{
    const URL_PATH_EDIT = 'marketplacer/seller/edit';
    const URL_PATH_VIEW = 'marketplacer/seller/view';
    const URL_PATH_DELETE = 'marketplacer/seller/delete';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Config $configHelper,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->configHelper = $configHelper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['seller_id'])) {
                    $editAllowed = $this->configHelper->isAdminEditAllowed();

                    $urlParams = $this->_getEntityUrlParams($item);
                    if ($editAllowed) {
                        $item[$this->getData('name')] = [
                            'edit'   => [
                                'href'  => $this->urlBuilder->getUrl(static::URL_PATH_EDIT, $urlParams),
                                'label' => __('Edit')
                            ],
                            'delete' => [
                                'href'    => $this->urlBuilder->getUrl(static::URL_PATH_DELETE, $urlParams),
                                'label'   => __('Delete'),
                                'confirm' => [
                                    'title'   => __('Delete seller "%1"', $item[SellerInterface::NAME] ?? ''),
                                    'message' => __(
                                        'Are you sure you wan\'t to delete seller "%1" with ID "%2"?',
                                        $item[SellerInterface::NAME] ?? '',
                                        $item[SellerInterface::SELLER_ID] ?? ''
                                    )
                                ]
                            ]
                        ];
                    } else {
                        $item[$this->getData('name')] = [
                            'view' => [
                                'href'  => $this->urlBuilder->getUrl(static::URL_PATH_VIEW, $urlParams),
                                'label' => __('View')
                            ]
                        ];
                    }

                }
            }
        }

        return $dataSource;
    }

    /**
     * Get url params
     * @param array $item
     * @return string[]
     */
    protected function _getEntityUrlParams(array $item)
    {
        return [
            'seller_id' => $item[SellerInterface::SELLER_ID],
            'store'     => $item[SellerInterface::STORE_ID] ?? Store::DEFAULT_STORE_ID,
        ];
    }
}
