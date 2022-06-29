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

namespace Turfcare\EconnectSXE\Model\Catalog\Product;

use LeanSwift\EconnectSXE\Api\ION\RequestInterface as IonRequest;
use LeanSwift\EconnectSXE\Api\LoggerInterface;
use LeanSwift\EconnectSXE\Api\ScopeInterface as SxeScope;
use LeanSwift\EconnectSXE\Api\Soap\RequestInterface;
use LeanSwift\EconnectSXE\Api\VersionInterface;
use LeanSwift\EconnectSXE\Helper\Erpapi;
use LeanSwift\EconnectSXE\Helper\Product;
use LeanSwift\EconnectSXE\Helper\Xpath;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Helper\Stock as StockHelper;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\Checkout\Model\Cart as CartItem;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection as DbConnection;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Module\Manager;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Indexer\Model\Indexer;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku;
use Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySkuFactory;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterfaceFactory;
use Magento\InventorySalesApi\Model\GetAssignedStockIdForWebsiteInterfaceFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Stock extends \LeanSwift\EconnectSXE\Model\Catalog\Product\Stock
{
    /**
     * BATCH SIZE
     */
    const BATCH_SIZE = 50;

    /**
     * @var StockRegistryInterface
     */
    protected $_stockRegistryInterface;

    /**
     * @var $_productRepository
     */
    protected $_productRepository;

    /**
     * @var $_updateStock
     */
    protected $_updateStock;

    /**
     * @var $_scopeConfig
     */
    protected $_scopeConfig;

    /**
     * @var $_path
     */
    protected $_path;

    /**
     * @var $_productStock
     */
    protected $_productStock;

    /**
     * @var $_warehousepath
     */
    protected $_warehousepath;

    /**
     * @var $_productHelper
     */
    protected $_productHelper;

    /**
     * @var $_stockIndexerProcessor
     */
    protected $_stockIndexerProcessor;

    /**
     * @var null $_connection
     */
    protected $_connection = null;

    /**
     * @var bool $_reindexFlag
     */
    protected $_reindexFlag = false;

    /**
     * @var $_indexerRegistry
     */
    protected $_indexerRegistry;

    /**
     * @var $_getAssignedStockIdForWebsiteInterface
     */
    protected $_getAssignedStockIdForWebsiteInterface;

    /**
     * @var $_wareHouse
     */
    protected $_wareHouse;

    /**
     * @var $getSourceItemsDataBySku
     */
    protected $getSourceItemsDataBySku;

    /**
     * @var
     */
    protected $_indexer;

    /**
     * @var
     */
    protected $sourceItem;

    /**
     * Stock constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param StockRegistryInterface $StockRegistryInterface
     * @param string $warehousePath
     * @param RequestInterface $ProductStock
     * @param StockHelper $stock
     * @param ProductFactory $productFactory
     * @param Processor $stockIndexerProcessor
     * @param DbConnection $dbConnection
     * @param IndexerRegistry $indexerRegistry
     * @param CartItem $cart
     * @param GetAssignedStockIdForWebsiteInterface $GetAssignedStockIdForWebsiteInterface
     * @param GetSourceItemsDataBySku $GetSourceItemsDataBySku
     */
    public function __construct(ProductRepositoryInterface $productRepository, StoreManagerInterface $storeManager, ScopeConfigInterface $scopeConfig, StockRegistryInterface $StockRegistryInterface, RequestInterface $ProductStock, LoggerInterface $logger, StockHelper $stock, ProductFactory $productFactory, Processor $stockIndexerProcessor, DbConnection $dbConnection, IndexerRegistry $indexerRegistry, CartItem $cart, GetAssignedStockIdForWebsiteInterfaceFactory $GetAssignedStockIdForWebsiteInterface, Manager $manager, GetSourceItemsDataBySkuFactory $GetSourceItemsDataBySkuFactory, SourceItemInterfaceFactory $SourceItemInterfaceFactory, VersionInterface $version, IonRequest $ionRequest, Xpath $xpath, Product $productHelper, GetStockItemConfigurationInterfaceFactory $GetStockItemConfigurationInterfaceFactory, ManagerInterface $managerInterface, SxeScope $sxeScope, SourceItem $RsourceItem, $warehousePath = '', Indexer $indexer, SourceItem $sourceItem)
    {
        parent::__construct($productRepository, $storeManager, $scopeConfig, $StockRegistryInterface, $ProductStock, $logger, $stock, $productFactory, $stockIndexerProcessor, $dbConnection, $indexerRegistry, $cart, $GetAssignedStockIdForWebsiteInterface, $manager, $GetSourceItemsDataBySkuFactory, $SourceItemInterfaceFactory, $version, $ionRequest, $xpath, $productHelper, $GetStockItemConfigurationInterfaceFactory, $managerInterface, $sxeScope, $RsourceItem, $warehousePath);
        $this->_indexer = $indexer;
        $this->sourceItem = $sourceItem;
    }

    /**
     * @param $productId
     * @return bool|\Magento\Catalog\Api\Data\ProductInterface
     */
    public function getProduct($productId)
    {
        try {
            return $this->_productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    public function bulkStockUpdate($erpItemResponseList, $skuColl, $productIdCol, $noparse = false)
    {
        $updateFlag = false;
        $stockInfo = [];
        $stockStatusInfo = [];
        foreach ($erpItemResponseList as $s_erp_item => $response) {
            $stock_qty = '';
            $entityId = $productIdCol[$s_erp_item];
            $sku = $skuColl[$s_erp_item];
            if (!empty($response) || $response instanceof \SimpleXMLElement) {
                if ($noparse) {
                    $stock_qty = $response;
                } else {
                    $stock_qty = $this->getParsedQty($response);
                }
                $stockInfo[$sku] = [
                    'product_id' => $entityId,
                    'qty' => $stock_qty,
                    'stock_id' => 3,
                    'is_in_stock' => 1
                ];
                $stockStatusInfo[$sku] = [
                    'product_id' => $entityId,
                    'qty' => $stock_qty,
                    'stock_id' => 3,
                    'stock_status' => 1,
                ];
                if ($stock_qty > 0) {
                    /**
                     * Check if stockQty value is greater than zero, M3 response may return negative values,
                     * for that cases product should be out of stock
                     **/
                    $stockInfo[$sku]['is_in_stock'] = 1;
                    $stockStatusInfo[$sku]['stock_status'] = 1;
                }
                if ($stock_qty != null || $stock_qty == 0) {
                    $updateFlag = true;
                }
            }
        }
        if ($updateFlag) {
            $this->_directUpdate($stockInfo, $stockStatusInfo);
            unset($stockInfo);
            unset($stockStatusInfo);
        }
    }

    /**
     * @param $associatedProducts
     * @return int|void
     * @throws NoSuchEntityException
     */
    protected function _saveStock($associatedProducts)
    {
        $updateCounter = 0;
        $flag = false;
        $productNumberArray = $associatedProducts->getColumnValues(Product::SXE_PRODUCT_NUMBER);
        $trimmedArray = array_map('trim', $productNumberArray);
        if (count($trimmedArray)) {
            $stockData = $this->_createRequest($trimmedArray);
            if ($stockData) {
                $updateCounter = $this->updateStockInformation($associatedProducts, $stockData);
            }
        }
        return $updateCounter;
    }

    /**
     * @param $associatedProducts
     * @param $stockData
     * @return int|void
     * @throws NoSuchEntityException
     */
    public function updateStockInformation($associatedProducts, $stockData)
    {
        $stockInfo = [];
        $stockStatusInfo = [];
        $productIdList = [];
        foreach ($associatedProducts as $product) {
            $productNumber = trim($product->getData(Product::SXE_PRODUCT_NUMBER));
            if ($productNumber) {
                if (array_key_exists($productNumber, $stockData)) {
                    $flag = true;
                    $stockQty = $stockData[$productNumber];
                    $sku = $product->getSku();
                    $entityId = $product->getId();
                    $stockInfo[$sku] = [
                        'product_id' => $entityId,
                        'qty' => $stockQty,
                        'stock_id' => 3,
                        'is_in_stock' => 1
                    ];
                    $stockStatusInfo[$sku] = [
                        'product_id' => $entityId,
                        'qty' => $stockQty,
                        'stock_id' => 3,
                        'stock_status' => 1,
                    ];
                    $productIdList[] = $entityId;
                    /**
                     * Check if stockQty value is greater than zero,
                     * somecases M3 response may return negative values,
                     * for that cases product should be out of stock
                     * */
                    if ($stockQty > 0) {
                        $stockInfo[$sku]['is_in_stock'] = 1;
                        $stockStatusInfo[$sku]['stock_status'] = 1;
                    }
                }
            }
        }
        if ($flag) {
            $status = $this->setProductIds($productIdList)->_directUpdate($stockInfo, $stockStatusInfo);
            unset($productIdList);
            return $status;
        }
    }

    /**
     * @param $productNumberList
     * @return array
     */
    public function createRequestViaSOAP($productNumberList)
    {
        $output = [];
        $postData = [];
        $keys = [];
        foreach ($productNumberList as $productNumber) {
            $postValues['ProductCode'] = $productNumber;
            $postValues['CrossReferenceFlag'] = 0;
            $postValues['Warehouse'] = $this->getCurrentWarehouse();
            $postValues['Ininfieldvalue'] = $this->productHelper->getInFieldValueFieldsForSoap(Erpapi::SOAP_PRODUCT_QTY_API, $this->_productStock->getNameSpace());
            $keys[] = $productNumber;
            $postData[] = $this->adapter->formRequest($postValues);
        }
        /**
         * Setting keys where the response is binded to the key
         */
        $logger = $this->customLogger ?? $this->logger->getLogInfo();
        $this->adapter->setKeys($keys);
        $this->adapter->setRequestBody($postData)->setLogInfo($logger)->send();
        $outputData = $this->adapter->getResponse();
        unset($postData);
        unset($postValues);
        unset($keys);
        //$errorExists = current($this->xpathHelper->getErrorMessage($outputData, $this->adapter->getNameSpace()));
        //if(!$errorExists)
        //{
        if (!empty($outputData)) {
            foreach ($outputData as $productNumber => $response) {
                $output[$productNumber] = $this->parseResponse($response);
            }
        }
        //}
        return $output;
    }

    /**
     * @param $data
     */
    public function updateSourceItem($data)
    {
        $sourceItem = $this->_connection->getTableName('inventory_source_item');
        $this->_connection->insertOnDuplicate($sourceItem, $data);

        /*TC-213 Deleted default source entries from inventory_source_item table to make use turfcare inventory source for products, since default source is deleted due to MSI modules disabled recently and this is assigned by default during the import process*/
        $where = ['sku = ?' => $data['sku'], 'source_code = ?' => "default"];
        $this->_connection->delete($sourceItem, $where);
    }

    /**
     * @param $sxeItemArray
     * @param $skuArray
     * @param $entityIdArray
     * @param $originalQtyArray
     * @param $type
     */
    public function updateCartItems($sxeItemArray, $skuArray, $entityIdArray, $originalQtyArray, $type)
    {
        $productIdList = [];
        $this->errorFlag = false;
        $stockInfo = [];
        $stockStatusInfo = [];
        $stockData = $this->_createRequest($sxeItemArray);
        if ($stockData) {
            foreach ($stockData as $itemNo => $itemQty) {
                $sku = $skuArray[$itemNo];
                $productId = $entityIdArray[$itemNo];
                $productIdList[$sku] = $productId;
                //$existingQty = $originalQtyArray[$itemNo];
                $newQty = $stockData[$itemNo];
                //if ($newQty != $existingQty || $this->isCheckout($type)) {
                $stockInfo[$sku] = ['product_id' => $productId, 'qty' => $newQty, 'stock_id' => 3];
                $stockStatusInfo[$sku] = ['product_id' => $productId, 'qty' => $itemQty, 'stock_id' => 3];
                if ($newQty > 0) {
                    /**
                     * Check if stockQty value is greater than zero, M3 response may return negative values,
                     * Check if stockQty value is greater than zero,
                     * M3 response may return negative values,
                     * for that cases product should be out of stock
                     **/
                    $stockInfo[$sku]['is_in_stock'] = 1;
                    $stockStatusInfo[$sku]['stock_status'] = 1;
                } else {
                    $stockInfo[$sku]['is_in_stock'] = 1;
                    $stockStatusInfo[$sku]['stock_status'] = 1;
                    //set error flag, redirect to shopping cart on checkout
                    $this->errorFlag = true;
                }
            }
            if (count($stockInfo)) {
                $this->setProductIds($productIdList)->_directUpdate($stockInfo, $stockStatusInfo);
                unset($productIdList);
            }
            if ($this->errorFlag) {
                $this->_cartItem->getQuote()->addErrorInfo(
                    'stock',
                    'cataloginventory',
                    null,
                    __('Some of the products are out of stock.')
                );
            }
        }
    }

    public function getBackOrderStatusForProducts($productIds=[])
    {
        $backOrderStatus = [];
        if (empty($productIds)) {
            $productIds = array_values($this->productIds);
        }
        if (!empty($productIds)) {
            $connection = $this->_connection;
            $select = $connection
                ->select()
                ->from(
                    [$this->_connection->getTableName('cataloginventory_stock_item')],
                    ['product_id', 'backorders']
                )
                ->where(
                    'product_id IN (?)',
                    $productIds
                );
            $backOrderStatus = $connection->fetchAll($select, [], \PDO::FETCH_GROUP);
        }
        return $backOrderStatus;
    }

    /**
     * @param $stockData
     * @param $stockStatusInfo
     * @return int|void
     * @throws NoSuchEntityException
     */
    protected function _directUpdate($stockData, $stockStatusInfo)
    {
        $batchStockData = [];
        //if the MSI module is enabled
        if ($this->isMSIEnabled()) {
            $whereEntiryId = null;
            $message = '';
            $beforeTime = microtime(true);
            $adapter = $this->_connection;
            // get stock table to update product qty
            $stockstatusTable = $this->_connection->getTableName('cataloginventory_stock_status_replica');
            // Insert stock status rows
            if (!empty($stockStatusInfo)) {
                $this->_connection->insertOnDuplicate($stockstatusTable, $stockStatusInfo, ['qty', 'stock_status']);
            }
            if (!empty($stockData)) {
                //get Back order status for the product list
                $backOrderData = $this->getBackOrderStatusForProducts();
                foreach ($stockData as $sku => $inventoryData) {
                    //this happens stock id for the current website
                    if ($this->getStockId()) {
                        $stockRow = [];
                        $stockRow = $this->getInventoryStock($this->getStockId());
                        if (!empty($stockRow)) {
                            foreach ($stockRow as $stock) {
                                $batchStockData = $this->formBulkStockData(
                                    $sku,
                                    $inventoryData,
                                    $stock['source_code'],
                                    $backOrderData
                                );
                            }
                        } else {
                            $batchStockData = $this->formBulkStockData(
                                $sku,
                                $inventoryData,
                                'default',
                                $backOrderData
                            );
                        }
                    }
                    if (!empty($batchStockData)) {
                        $this->updateSourceItem($batchStockData);
                    }
                }
            }
            if (!empty($batchStockData)) {
                $afterTime = microtime(true);
                $time = $afterTime - $beforeTime;
                $totalCount = count($stockData);
                $this->writeStockInfo("Updated records for stock - $totalCount Time taken: $time");
                unset($batchStockData);
                unset($stockData);
                unset($backOrderData);
                return $totalCount;
            }
        } else {
            $this->directUpdateWithoutMSI($stockData);
        }
    }

    public function updateStockNewProduct($sku, $productId, $newQty)
    {
        $data['qty'] = $newQty;
        $stockRow = $this->getInventoryStock($this->getStockId());
        if (!empty($stockRow)) {
            foreach ($stockRow as $stock) {
                $this->updateStockForStockItem(
                    $sku,
                    $data,
                    $stock['stock_id'],
                    $stock['source_code']
                );
            }
        } else {
            $this->updateStockForStockItem($sku, $data, 1, 'default');
        }
        $sourceItemIds = $this->getSourceItemIds([$sku]);
        $this->reindexInventory($sourceItemIds);
        //Reindex catalog inventory stock
        $this->reindexStockInfo($productId);
        unset($sourceItemIds);
    }


}
