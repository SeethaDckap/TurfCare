<?php
/**
 * *
 *   LeanSwift eConnect Extension
 *
 *   NOTICE OF LICENSE
 *
 *   This source file is subject to the LeanSwift eConnect Extension License
 *   that is bundled with this package in the file LICENSE.txt located in the Connector Server.
 *
 *   DISCLAIMER
 *
 *  This extension is licensed and distributed by LeanSwift. Do not edit or add to this file
 *   if you wish to upgrade Extension and Connector to newer versions in the future.
 *   If you wish to customize Extension for your needs please contact LeanSwift for more
 *   information. You may not reverse engineer, decompile,
 *   or disassemble LeanSwift Connector Extension (All Versions), except and only to the extent that
 *   such activity is expressly permitted by applicable law not withstanding this limitation.
 *
 * @category  LeanSwift
 * @package   LeanSwift_EconnectSXE
 * @copyright Copyright (c) 2019 LeanSwift Inc. (http://www.leanswift.com)
 * @license   http://www.leanswift.com/license/connector-extension
 */

namespace Turfcare\EconnectSXE\Model\Catalog\Import;


/**
 * Class SaveProduct
 *
 * @package LeanSwift\Econnect\Model\Catalog\Import
 */
class SaveProduct extends \LeanSwift\EconnectSXE\Model\Catalog\Import\SaveProduct
{
    const VALUE = 'value';
    const ROW_ID = 'row_id';
    const ENTITY_ID = 'entity_id';
    const REPLACE = 'replace';
    const WEBSITE = 'website';
    const TYPE_ID = 'type_id';
    const ADDITION = 'addition';
    const POSITION = 'position';
    const STOCK_ID = 'stock_id';
    const UPDATED_AT = 'updated_at';
    const WEBSITE_ID = 'website_id';
    const CATEGORY_ID = 'category_id';
    const IS_IN_STOCK = 'is_in_stock';
    const HAS_OPTIONS = 'has_options';
    const CATEGORY_IDS = 'category_ids';
    const ATTRIBUTE_ID = 'attribute_id';
    const ATTRIBUTE_SET = 'attribute_set';
    const ATTRIBUTE_SET_ID = 'attribute_set_id';

    /**
     * #@+
     * Table name
     */
    const CATALOG_PRODUCT_ENTITY = 'catalog_product_entity';
    const CATALOG_PRODUCT_WEBSITE = 'catalog_product_website';
    const CATALOG_PRODUCT_CATEGORY = 'catalog_product_category';
    const CATALOG_INVENTORY_STOCK_ITEM = 'cataloginventory_stock_item';
    /**
     * #@-
     */

    /**
     * Date Format
     */
    const DATETIME_PHP_FORMAT = 'Y-m-d H:i:s';


    protected $_helperData;

    protected $_productIdsToReindex;

    public function setHelperObject($helper)
    {
        $this->_helperData = $helper;
    }

    public function handleBunchData($bunch) {
        $categoryIds = $replaceCategory = $attributeSetId = $website = $isAddition = $productId = $defaultStore = null;
        $categoriesIn = $attributes = $stockData = $isInCategories = $delProductId = [];
        $resource = $this->_resourceFactory->create();
        $catProdWebTable = $this->_connection->getTableName('catalog_product_website');
        $entityTable = $this->_resourceFactory->create()->getEntityTable();
        foreach ($bunch as $rowNum => $rowData) {
            $rowScope = $this->getRowScope($rowData);
            $rowSku = $rowData[self::COL_SKU];

            if (null === $rowSku) {
                $this->_rowsToSkip[$rowNum] = true;
                // skip rows when SKU is NULL
                continue;
            } elseif (self::SCOPE_STORE == $rowScope) {
                // set necessary data from SCOPE_DEFAULT row
                $rowData[self::COL_TYPE] = $this->skuProcessor->getNewSku($rowSku)['type_id'];
                $rowData['attribute_set_id'] = $this->skuProcessor->getNewSku($rowSku)['attr_set_id'];
                $rowData[self::COL_ATTR_SET] = $this->skuProcessor->getNewSku($rowSku)['attr_set_code'];
            }
            //Check category id and replace value exists in rowdata to map products with categories
            if (isset($rowData['category_ids']) && isset($rowData['replace'])) {
                $categoryIds = $rowData['category_ids'];
                $replaceCategory = $rowData['replace'];
                unset($rowData['category_ids']);
                unset($rowData['replace']);
            }

            //Check attribute set exists in rowdata to map attribute set for products
            if (isset($rowData['attribute_set'])) {
                $attributeSetId = $rowData['attribute_set'];
                unset($rowData['attribute_set']);
            }

            if (isset($rowData['website'])) {
                $website = $rowData['website'];
                unset($rowData['website']);
            }

            if (isset($rowData['addition'])) {
                $isAddition = $rowData['addition'];
                unset($rowData['addition']);
            }


            // entity phase
            $productId = $this->_connection
                ->fetchOne(
                    $this->_connection->select()
                        ->from(
                            $this->getResource()
                                ->getTable('catalog_product_entity')
                        )
                        ->where('sku = ?', (string)$rowSku)
                        ->columns('entity_id')
                );
            if ($productId) {
                $this->productExistAlready($productId, $attributeSetId, $entityTable);
            } else {
                $stockData = $this->updateProductStock($attributeSetId, $rowData, $rowSku, $entityTable);
            }

            // TC-14: Only Allow Backorder based on product status type is 's' or 'o' only.
            if($productId && isset($rowData['statusType']))
            {
                //$isBackorderEnabled = $this->stockConfiguration->getBackorders();
                $stockData[$rowSku] = [
                    'product_id' => $productId,
                    'stock_id' => 1,
                    'is_in_stock' => ($rowData['statusType'] == 's' || $rowData['statusType'] == 'o') ? 1 : 0,
                    'backorders' => ($rowData['statusType'] == 's' || $rowData['statusType'] == 'o') ? 2 : 0,
                    'use_config_backorders' => ($rowData['statusType'] == 's' || $rowData['statusType'] == 'o') ? 0 : 1
                ];
                unset($rowData['statusType']);
            }
            if ($isAddition) {
                $this->addProductToWebsite($website, $productId, $catProdWebTable);
            }
            $storeIds = null;
            $helper = $this->_helperData;
            if (is_array($website)) {
                foreach ($website as $websiteId) {
                    $storeIds = $helper->getAllStores($websiteId);
                }
            } else {
                $storeIds = $helper->getAllStores($website);
            }

            //If categoryIds is not empty, prepare query to map product with category
            if (!empty($categoryIds)) {
                if ($replaceCategory) {
                    //Collect the existing product ids to replace the new one,
                    // only if change category for existing product option is enabled
                    $delProductId[] = $productId;
                }

                //Explode category ids which is separated by commas
                $categoryArrayId = explode(',', $categoryIds);
                foreach ($categoryArrayId as $categoryId) {
                    $isInCategories[] = [
                        Self::PRODUCT_ID => $productId,
                        self::CATEGORY_ID    => $categoryId,
                        self::POSITION       => 1,
                    ];
                }
            }
            //Check if category id exists
            if ($isInCategories) {
                $this->saveCategoriesData($isInCategories, $delProductId);
            }
            $attributes[] = $this->updateAttributesData($rowData, $resource, $rowScope, $rowSku, $storeIds);
            //If categoryIds is not empty, prepare query to map product with category
        }
        return [
            'categories' => $categoriesIn,
            'attributes' => $attributes,
            'stockData' => $stockData
        ];
    }

    public function productExistAlready($productId, $attributeSetId, $entityTable)
    {
        if ($attributeSetId) {//Change attribute set for existing products
            // existing row
            $entityRowsUp[] = [
                'updated_at' => (new DateTime())->format(self::DATETIME_PHP_FORMAT),
                'entity_id' => $productId,
                'attribute_set_id' => $attributeSetId,
            ];

            $this->_connection->insertOnDuplicate($entityTable, $entityRowsUp, ['attribute_set_id', 'updated_at']);
        }
    }

    public function updateProductStock($attributeSetId, $rowData, $rowSku, $entityTable)
    {
        if (!$attributeSetId) { //If atribute set sync is disabled, assign the default attribute set
            $attributeSetId = 4;
        }
        $entityRowsIn = [
            'attribute_set_id' => $attributeSetId,
            'type_id' => 'simple',
            'sku' => $rowSku,
            'has_options' => isset($rowData['has_options']) ? $rowData['has_options'] : 0,
        ];
        $this->_connection->insert($entityTable, $entityRowsIn);
        $productId = $this->_connection->lastInsertId();
        $this->_productIdsToReindex[] = $productId;

        // Checking backorder enabled
        //$isBackorderEnabled = $this->stockConfiguration->getBackorders();
        if(isset($rowData['statusType']) && ($rowData['statusType'] == 's' || $rowData['statusType'] == 'o')) {
            $isBackorderEnabled = 1;
        }
        else {
            $isBackorderEnabled = 0;
        }
        // Initially qty is zero and if backorder enabled stock=>in stock else out of stock
        $stockData[$rowSku] = [
            'product_id' => $productId,
            'qty' => 0,
            'stock_id' => 1,
            'is_in_stock' => ($isBackorderEnabled != 0) ? 1 : 0,
            'backorders' => ($isBackorderEnabled == 0) ? 0 : 2,
            'use_config_backorders' => ($isBackorderEnabled == 0) ? 0 : 1
        ];
        return $stockData;
    }

    public function updateAttributesData($rowData, $resource, $rowScope, $rowSku, $storeIds=[])
    {
        //$storeIds = [];
        //$object_manager = \Magento\Framework\App\ObjectManager::getInstance();
        //$helper = $object_manager->create('LeanSwift\EconnectSXE\Helper\Product');
        $defaultStore = 0;
        $product = $this->_proxyProdFactory->create(['data' => $rowData]);
        foreach ($rowData as $attrCode => $attrValue) {
            $pdtStoreIds = null;
            if (!isset($this->_attributeCache[$attrCode])) {
                $this->_attributeCache[$attrCode] = $resource->getAttribute($attrCode);
            }
            $attribute = $this->_attributeCache[$attrCode];
            if ('multiselect' != $attribute->getFrontendInput() && self::SCOPE_NULL == $rowScope) {
                // skip attribute processing for SCOPE_NULL rows
                continue;
            }
            $attrId = $attribute->getId();
            $backModel = $attribute->getBackendModel();
            $attrTable = $attribute->getBackend()->getTable();

            if ('datetime' == $attribute->getBackendType() && strtotime($attrValue)) {
                $attrValue = (new DateTime())->setTimestamp(strtotime($attrValue));
                $attrValue = $attrValue->format(self::DATETIME_PHP_FORMAT);
            } elseif ($backModel) {
                $attribute->getBackend()->beforeSave($product);
                $attrValue = $product->getData($attribute->getAttributeCode());
            }
            //$storeIds[] = $defaultStore;
            $pdtStoreIds = (self::SCOPE_DEFAULT == $attribute->getIsGlobal()) ? [$defaultStore] : $storeIds;

            foreach ($pdtStoreIds as $storeId) {
                if (!isset($attributes[$attrTable][$rowSku][$attrId][$storeId])) {
                    $attributes[$attrTable][$rowSku][$attrId][$storeId] = $attrValue;
                }
            }
            // restore 'backend_model' to avoid 'default' setting
            $attribute->setBackendModel($backModel);
        }
        return $attributes;
    }

    public function addProductToWebsite($website, $productId, $catProdWebTable) {
        if (is_array($website)) {
            foreach ($website as $websiteId) {
                $webRowsIn = ['product_id' => $productId, 'website_id' => $websiteId];
                $this->_connection->insertOnDuplicate($catProdWebTable, $webRowsIn,
                    ['product_id', 'website_id']);
            }
        }
    }

    protected function saveCategoriesData($productCategories, $deleteIds)
    {
        $categoryTable = $this->_resourceFactory->create()->getProductCategoryTable();
        /**
         * To update existing product categories,check if category id exists,
         * if yes, check product is already mapped to categories, then delete before update
         * */
        if (count($deleteIds)) {
            $categoryData = $this->_connection->fetchOne(
                $this->_connection->select()->from(
                    $categoryTable,
                    ['product_id']
                )->where('product_id IN (?)', $deleteIds)
            );

            if ($categoryData) {
                $this->_connection->delete(
                    $categoryTable,
                    $this->_connection->quoteInto('product_id IN (?)', $deleteIds)
                );
            }
        }
        //Map products to categories
        $this->_connection->insertOnDuplicate($categoryTable, $productCategories, ['product_id', 'category_id']);
    }


}
