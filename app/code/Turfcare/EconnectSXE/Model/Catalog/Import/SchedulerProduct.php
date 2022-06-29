<?php
/**
 * LeanSwift eConnectSXE Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the LeanSwift eConnectSXE Extension License
 * that is bundled with this package in the file LICENSE.txt located in the Connector Server.
 *
 * DISCLAIMER
 *
 * This extension is licensed and distributed by LeanSwift. Do not edit or add to this file
 * if you wish to upgrade Extension and Connector to newer versions in the future.
 * If you wish to customize Extension for your needs please contact LeanSwift for more
 * information. You may not reverse engineer, decompile,
 * or disassemble LeanSwift eConnectSXE Extension (All Versions), except and only to the extent that
 * such activity is expressly permitted by applicable law not withstanding this limitation.
 *
 * @category  LeanSwift
 * @package   LeanSwift_EconnectSXE
 * @copyright Copyright (c) 2020 LeanSwift Inc. (http://www.leanswift.com)
 * @license   https://www.leanswift.com/end-user-licensing-agreement/
 */

namespace Turfcare\EconnectSXE\Model\Catalog\Import;

use LeanSwift\EconnectSXE\Api\LoggerInterface;
use LeanSwift\EconnectSXE\Api\ScopeInterface;
use LeanSwift\EconnectSXE\Helper\Configurations;
use LeanSwift\EconnectSXE\Helper\Constant;
use LeanSwift\EconnectSXE\Helper\Data;
use LeanSwift\EconnectSXE\Helper\Product;
use LeanSwift\EconnectSXE\Helper\Xpath;
use LeanSwift\EconnectSXE\Model\Catalog\Product\Stock;
use LeanSwift\EconnectSXE\Model\Sync\ProductSycBulkFactory;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Exception\LocalizedException;
use SimpleXMLElement;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;



/**
 * Class SchedulerProduct
 * @package LeanSwift\EconnectSXE\Model\Catalog\Import
 */
class SchedulerProduct extends \LeanSwift\EconnectSXE\Model\Catalog\Import\SchedulerProduct
{
    protected $scope;
    protected $productHelperData;
    protected $helperData;
    protected $logger;
    protected $productPrice;
    protected $isIONenabled;
    protected $ProductSycBulkFactoryFactory;
    protected $stockModel;
    protected $canSyncProduct;
    protected $canSyncStock;
    /**
     * @var array
     */
    protected $batchProducts;
    protected $reindexList = [];
    protected $canSyncCategory;
    protected $_resoureConnetion;
    protected $_collectionFactory;

    /**
     * SchedulerProduct constructor.
     * @param Product $productHelperData
     * @param ScopeInterface $scope
     * @param Data $data
     * @param LoggerInterface $logger
     * @param ProductPrice $productPrice
     * @param ProductSycBulkFactory $ProductSycBulkFactory
     * @param Stock $stockModel
     */
    public function __construct(
        Product $productHelperData,
        ScopeInterface $scope,
        Data $data,
        LoggerInterface $logger,
        ProductPrice $productPrice,
        ProductSycBulkFactory $ProductSycBulkFactory,
        Stock $stockModel,
        ResourceConnection $resourceConnection,
        CollectionFactory $collectionFactory

    )
    {
        parent::__construct($productHelperData, $scope, $data, $logger, $productPrice, $ProductSycBulkFactory, $stockModel);
        $this->_resourceConnection = $resourceConnection;
        $this->_collectionFactory = $collectionFactory;
    }

    public function productInventoryWithSqlQuery()
    {
        $connection = $this->_resourceConnection->getConnection();
        $productTable = $connection->getTableName('catalog_product_entity');
        $inventoryTable= $connection->getTableName('inventory_source_item');
        $where = "(b.sku is null or (b.sku =a.sku and source_code='default'))";
        $sql = "SELECT a.sku FROM $productTable a left outer join $inventoryTable b on a.sku=b.sku where $where";
        $sqlResult = $connection->fetchAll($sql);

        $resultCount = count($sqlResult);
        if($resultCount > 0) {
            $output = 1;
            $skuArray = array_column($sqlResult, 'sku');
            foreach ($skuArray as $sku) {
                $products = $this->_collectionFactory->create()
                    ->addAttributeToSelect(Product::SXE_PRODUCT_NUMBER);
                $collection = $products->addFieldToFilter(Product::SXE_PRODUCT_NUMBER, ['in' => $sku]);
                foreach($collection->getData() as $product) {
                    $this->stockModel->updateStockNewProduct($product['sku'], $product['entity_id'], $output);
                }
            }
        }

        return true;
    }
}
