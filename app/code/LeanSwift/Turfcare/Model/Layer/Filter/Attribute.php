<?php

namespace LeanSwift\Turfcare\Model\Layer\Filter;

use LeanSwift\Turfcare\Helper\Data;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use WeltPixel\LayeredNavigation\Helper\Data as LayerHelper;
use WeltPixel\LayeredNavigation\Model\Layer\Filter as LayerFilter;
use WeltPixel\LayeredNavigation\Model\AttributeOptions;
use WeltPixel\LayeredNavigation\Model\Layer\Filter\Attribute as WeltPixelAttributeLayer;
use Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider\SelectBuilderForAttribute\ApplyStockConditionToSelect;

/**
 * Class Attribute
 * @package LeanSwift\Turfcare\Model\Layer\Filter
 */
class Attribute extends WeltPixelAttributeLayer
{
    /**
     * @var \Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider\SelectBuilderForAttribute\ApplyStockConditionToSelect
     */
    private $applyStockConditionToSelect;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var Data
     */
    protected $_helper;

    /**
     * Attribute constructor.
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Magento\Framework\Filter\StripTags $tagFilter
     * @param \Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory $filterAttributeFactory
     * @param LayerHelper $moduleHelper
     * @param LayerFilter $layerFilter
     * @param AttributeOptions $attributeOptions
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        \Magento\Framework\Filter\StripTags $tagFilter,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory $filterAttributeFactory,
        LayerHelper $moduleHelper,
        LayerFilter $layerFilter,
        AttributeOptions $attributeOptions,
        ResourceConnection $resource,
        ScopeResolverInterface $scopeResolver,
        ApplyStockConditionToSelect $applyStockConditionToSelect,
        Data $helper,
        array $data = []
    )
    {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $tagFilter,
            $filterAttributeFactory,
            $moduleHelper,
            $layerFilter,
            $attributeOptions,
            $data
        );

        $this->_helper = $helper;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->scopeResolver = $scopeResolver;
        $this->applyStockConditionToSelect = $applyStockConditionToSelect;
    }

    /**
     * @inheritdoc
     */
    protected function _getItemsData()
    {
        if (!$this->_moduleHelper->isEnabled()) {
            return parent::_getItemsData();
        }

        $attribute = $this->getAttributeModel();
        $wpLnAttributeOptions = ($attribute->getId()) ? $this->_wpAttributeOptions->getDisplayOptionsByAttribute($attribute->getId()) : false;

        if (!$wpLnAttributeOptions->getIsMultiselect() && $this->_isFilter) {
            return [];
        }

        /** @var \WeltPixel\LayeredNavigation\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();

        if ($this->_isFilter && $this->_layerFilter->isMainFilter($this)) {
            $productCollection = $productCollection->getCollectionClone()
                ->removeAttributeSearch($attribute->getAttributeCode());
        }

        $optionsFacetedData = $productCollection->getFacetedData($attribute->getAttributeCode());

        $customer = $this->_helper->getCustomerSession();
        //$allowedProductIds = $this->_helper->getDealerItems();
        $attributeValues = [];

        // Common product filter preparation start
        $currentScopeId = $this->scopeResolver->getScope()->getId();
        $table = $this->resource->getTableName(
            'catalog_product_index_eav' . ($attribute->getBackendType() === 'decimal' ? '_decimal' : '')
        );

        $select = $this->connection->select();
        $select->from(['main_table' => $table])
            ->distinct()
            ->where('main_table.attribute_id = ?', (int)$attribute->getAttributeId())
            ->where('main_table.store_id = ? ', $currentScopeId);

        //Condition to restrict the product for dealers
//        if ($customer->isLoggedIn() && count($allowedProductIds) > 0) {
//            $select->where('main_table.entity_id IN (?)', $allowedProductIds);
//        }


        if ($this->_helper->isAddStockFilter()) {
            $select = $this->applyStockConditionToSelect->execute($select);
        }

        // Common product filter preparation end
//        if (!$customer->isLoggedIn()) {
//            $categoryIds = $this->_helper->hideCategories();
//            if (count($categoryIds)) {
//                $select->joinInner(
//                    ['product_category' => $this->resource->getTableName('catalog_category_product_index_store' . $currentScopeId)],
//                    'main_table.entity_id = product_category.product_id',
//                    ['category_id']
//                );
//                $select->joinInner(
//                    ['catalog_category' => $this->resource->getTableName('catalog_category_entity')],
//                    'product_category.category_id = catalog_category.entity_id',
//                    ['level']
//                );
//                $select->where('catalog_category.level > 2');
//                $select->where('product_category.category_id IN (?)', $categoryIds);
//            }
//        }
        $result = $this->connection->fetchAll($select);
        if (count($result)) {
            $attributeValues = array_column($result, 'value');
            $attributeValues = array_unique($attributeValues);
        }

        $productSize = $productCollection->getSize();

        if ((count($optionsFacetedData) === 0
                && $this->getAttributeIsFilterable($attribute) !== static::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS)
            || !$productSize
        ) {
            return $this->itemDataBuilder->build();
        }

        $itemData = [];
        $checkCount = false;
        $options = $attribute->getFrontend()->getSelectOptions();
        $counter = false;

        foreach ($options as $option) {
            $sorted = false;
            if (empty($option['value'])) {
                continue;
            }

            $value = $option['value'];

            if ($counter) {
                $count = isset($counter[$value]) ? (int)$counter[$value] : 0;
            } else {
                $count = isset($optionsFacetedData[$value]['count']) ? (int)$optionsFacetedData[$value]['count'] : 0;
            }

            // Check filter type
            if (($this->getAttributeIsFilterable($attribute) == static::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS && (!$this->_layerFilter->isOptionReducesResults($this, $count, $productSize)) && $count == 0)
                || (count($attributeValues) && (!in_array($option['value'], $attributeValues)))
            ) {
                continue;
            }

            if ($count > 0) {
                $checkCount = true;
            }

            $itemData[] = [
                'label' => $this->tagFilter->filter($option['label']),
                'value' => $value,
                'count' => $count
            ];
        }


        if ($checkCount) {
            if ($wpLnAttributeOptions->getSortBy() == 2) {
                usort($itemData, [$this, '_compareAz']);
                $sorted = true;
            }
            foreach ($itemData as $item) {
                $this->itemDataBuilder->addItemData($item['label'], $item['value'], $item['count']);
            }
        }

        if ($wpLnAttributeOptions->getSortBy() == 2 && !$sorted) {
            usort($options, [$this, '_compareAz']);
        }

        return $this->itemDataBuilder->build();
    }
}
