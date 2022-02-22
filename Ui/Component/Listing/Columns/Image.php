<?php

namespace Marketplacer\Seller\Ui\Component\Listing\Columns;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Marketplacer\Base\Helper\Image as ImageHelper;

/**
 * Class Image
 */
class Image extends Column
{
    const NAME = 'thumbnail';
    const ALT_FIELD = 'name';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;
    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * Image constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ImageHelper $imageHelper
     * @param DataObjectFactory $dataObjectFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ImageHelper $imageHelper,
        DataObjectFactory $dataObjectFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->imageHelper = $imageHelper;
        $this->urlBuilder = $urlBuilder;

        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Prepare Data Source
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $dataObject = $this->dataObjectFactory->create();
                $dataObject->setData($item);
                $imageSrc = $this->getImageSrc($dataObject, $fieldName);
                $item[$fieldName . '_src'] = $imageSrc;
                $item[$fieldName . '_alt'] = $this->getAlt($item) ?: '';
                $item[$fieldName . '_orig_src'] = $imageSrc;
            }
        }
        return $dataSource;
    }

    /**
     * Get image alt parameter
     * @param array $row
     * @return null|string
     */
    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return isset($row[$altField]) ? $row[$altField] : null;
    }

    /**
     * @param DataObject $row
     * @param string|null $fieldName
     * @return string
     */
    public function getImageSrc(DataObject $row, $fieldName)
    {
        $imageHelper = $this->imageHelper->init($row, $fieldName);
        return !empty($row->getData($fieldName)) ? $row->getData($fieldName) : $imageHelper->resize(50, 50)->getUrl();
    }
}
