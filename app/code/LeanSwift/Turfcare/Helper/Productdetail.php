<?php

namespace LeanSwift\Turfcare\Helper;
use LeanSwift\EconnectSXE\Model\Catalog\Product\Price;
use Magedelight\Orderbysku\Helper\Productdetail as CoreProductHelper;
use LeanSwift\Turfcare\Helper\Data as DataHelper;
use \Magento\CatalogInventory\Api\StockRegistryInterface;

class Productdetail extends CoreProductHelper
{
    /**
     * @var Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    protected $_stockItemRepository;

    /**
     * @var Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var Magento\Catalog\Helper\Output
     */
    protected $_productHelper;

    protected $moduleManager;

    /**
     * Customer Price Object Interface
     *
     * @var PriceInterface
     */
    protected $_customerPriceModel;

    /**
     * Data Helper
     *
     * @var PriceInterface
     */
    protected $_dataHelper;

    protected $stockRegistry;

    /**
     * Productdetail constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Pricing\Render\Layout $layout
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param \Magento\Store\Model\StoreManager $storeManager
     * @param \Magento\Catalog\Helper\Output $producthelper
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magedelight\Orderbysku\Block\Product $productBlock
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Pricing\Render\Layout $layout,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Catalog\Helper\Output $producthelper,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magedelight\Orderbysku\Block\Product $productBlock,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Swatches\Helper\Data $swatchesHelper,
        Price $_customerPriceModel,
        DataHelper $_dataHelper,
        StockRegistryInterface $stockRegistry,
        array $data = []
    ) {
        $this->_customerPriceModel = $_customerPriceModel;
        $this->_dataHelper = $_dataHelper;
        $this->stockRegistry = $stockRegistry;
        parent::__construct($context,$layout,$productCollectionFactory,$objectManager,$stockItemRepository,$productFactory,$filesystem,$imageFactory,$storeManager,$producthelper,$layoutFactory,$productBlock,$registry,$moduleManager,$swatchesHelper,$data);
    }

    /**
     * TC-252 Rewrite core function for customer price logic
     *
     * @param $sku
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductBySku($sku)
    {
        $productdetails = [];
        $store = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
        $collection = $this->_productCollectionFactory->create()
            ->addWebsiteFilter($store->getWebsiteId())
            ->addStoreFilter($store->getId())
            ->addAttributeToSelect('*')
            ->addAttributeToSelect('image')
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('erp_product_category_code')
            ->addAttributeToFilter('status', 1)
            ->addAttributeToFilter('visibility', ['in' => [2, 3, 4]])
            ->addAttributeToFilter('sku', $sku);

        $priceHelper = $this->_objectManager->create('Magento\Framework\Pricing\Helper\Data');

        foreach ($collection as $product) {
            //if configurable product
            $productdetails['configure'] = false;
            if ($product->getTypeId() == 'configurable' || $product->getTypeId() == 'grouped' || $product->getTypeId() == 'virtual' || $product->getTypeId() == 'bundle' || $product->getTypeId() == 'downloadable') {
                $productdetails['messege'] = __('You need to choose options for your item.');
                $productdetails['configure'] = true;
            }

            //TC-252 Set 1 since backorder enabled
            //$productStock = $this->_stockItemRepository->get($product->getId());
            //$minSaleQty = $productStock->getMinSaleQty();
            //$maxSaleQty = $productStock->getMaxSaleQty();



            $productIsInStock = $minSaleQty = 1;


            if ($product->getTypeId() == 'configurable') {
                if ($product->isAvailable()) {
                    $productdetails['is_in_stock'] = __("In stock");
                } else {
                    $productdetails['is_in_stock'] = __("Out of stock");
                    return [];
                }
            } elseif ($product->getTypeId() === 'bundle') {

                $selectionCollection = $product->getTypeInstance(true)
                    ->getSelectionsCollection($product->getTypeInstance(true)->getOptionsIds($product), $product);
                if (empty($selectionCollection->getData())) {
                    $productdetails['is_in_stock'] = __("Out of stock");
                    return [];
                } else {
                    $productdetails['is_in_stock'] = __("In stock");
                }
            } else {
                if ($productIsInStock == true) {
                    $productdetails['is_in_stock'] = __("In stock");
                } else {
                    $productdetails['is_in_stock'] = __("Out of stock");
                }
            }
            $productObject = $this->getLoadedProduct($product);
            //TC-252 Customer price call related changes */
            $customerObject = $this->_dataHelper->getCustomerSession()->getCustomer();
            $customerPrice = $this->_customerPriceModel->getCustomerPrice($productObject,$customerObject,$store->getId());
            $customerPriceHtml ='';
            if($customerPrice){
                $customerPriceWithCurrency = $priceHelper->currency($customerPrice, true, false);
                $customerPriceHtml = $this->getStartingFromPriceHtml($customerPriceWithCurrency);
                //$customerPriceHtml = $this->getPriceHtml($product);
            }
            $productdetails['options'] = $this->getProductOptions($product);

            if ($customOptions = $this->getCustomOptionsHtml($product)) {
                $productdetails['options']['custom_options'] = $customOptions;
            }

            if ($product->getTypeId() == 'grouped') {
                $finalPrice = '0';
                $usedProds = $product->getTypeInstance(true)->getAssociatedProducts($product);
                foreach ($usedProds as $child) {
                    if ($child->getId() != $product->getId()) {
                        $finalPrice += $child->getFinalPrice();
                    }
                }
                $priceWithCurrency = $priceHelper->currency($finalPrice, true, false);
                $priceWithoutCurrency = $finalPrice;
                $priceHtml = $this->getStartingFromPriceHtml($priceWithCurrency);
            } elseif ($product->getTypeId() == 'bundle') {
                $finalPrice  = $product->getPriceInfo()->getPrice('final_price')->getValue();
                $priceWithoutCurrency = $finalPrice;
                $priceWithCurrency = $priceHelper->currency($finalPrice, true, false);
                $priceHtml = $this->getStartingFromPriceHtml($priceWithCurrency);
            } else {
                $priceWithCurrency = $priceHelper->currency($product->getFinalPrice(), true, false);
                $priceWithoutCurrency = $product->getFinalPrice();
                $priceHtml = $this->getStartingFromPriceHtml($priceWithCurrency);

            }

            $imageHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Catalog\Helper\Image::class);
            $attributes = $this->getAdditionalData($product);
            $productdetails['type'] = $product->getTypeId();
            $productdetails['product_id'] = $product->getId();
            $productdetails['attributes'] = $attributes;
            $productdetails['name'] = $product->getName();
            $productdetails['sku'] = $product->getSku();
            $productdetails['description'] = $product->getDescription();
            $productdetails['shortdescription'] = $product->getShortDescription();
            $productdetails['price'] = $priceWithCurrency;
            $productdetails['price_without_cur'] = $priceWithoutCurrency;
            $productdetails['price_html'] = $priceHtml;
            $productdetails['product_url'] = $store->getBaseUrl() . $product->getUrlKey();
            $productdetails['productimage'] = (!($product->getImage() == "no_selection")) ? $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage() : $imageHelper->getDefaultPlaceholderUrl('image');
            $productdetails['thumbnail'] = $this->resize($product->getSmallImage(), 100, 100);
            $productdetails['productMinQty'] = $minSaleQty;
            $productdetails['productMaxQty'] = 10000;
            $qtyHtml = "<span>". __('Min: ').$productdetails['productMinQty']."</span>\n
                        <span style='white-space:nowrap'>". __('Max: ').$productdetails['productMaxQty']."</span>";
            $productdetails['productQtyHtml'] = $qtyHtml;
            if($customerPriceHtml) {
                $productdetails['customer_price_html'] = $customerPriceHtml;
                $productdetails['customer_price'] = (float)$customerPrice;
            }
        }
        $erpProductCategoryCode=explode(",", $product->getErpProductCategoryCode());
        $customerData = $this->_dataHelper->getCustomerSession()->getCustomer();
        $erpCustomerCodes = explode(",", $customerData->getErpCustomerCode());
        $categoryCodeFlag = $this->checkCategoryCodes($erpProductCategoryCode,$erpCustomerCodes);
        if(!$categoryCodeFlag) {
            $customMessage = 'Please contact customer service for details';
            $productdetails['price_html'] = $customMessage;
            $productdetails['customer_price_html'] = $customMessage;
        }
        return $productdetails;
    }

    /**
     * @param $sku
     * @param $qty
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function productIsAvailable($sku, $qty)
    {
        $store = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
        $product = $this->_productCollectionFactory->create()
            ->addWebsiteFilter($store->getWebsiteId())
            ->addStoreFilter($store->getId())
            ->addAttributeToSelect('*')
            ->addAttributeToSelect('image')
            ->addAttributeToSelect('url_key')
            ->addAttributeToFilter('status', 1)
            ->addAttributeToFilter('visibility', ['in' => [2, 3, 4]])
            ->addAttributeToFilter('sku', trim($sku))
            ->getFirstItem();

        $_product = $this->_productFactory->create();
        $_product->load($product->getIdBySku($sku));

        $outstocklist = [];
        $qtylist = [];
        $nonsimplelist = [];
        $invalidlist = [];
        $productwithOptionslist = [];
        $productskudetails = [];
        $superSededList = [];

        if ($_product->getSku() && in_array($_product->getVisibility(), [2, 3, 4])) {
            if ($product->getSku()) {
                $imageHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Catalog\Helper\Image::class);

                $productskudetails['name'] = $product->getName();
                $productskudetails['sku'] = $product->getSku();
                $productskudetails['typeid'] = $product->getTypeId();
                $productskudetails['qty'] = $qty;
                $productskudetails['productUrl'] = $_product->getProductUrl();
                $productskudetails['itemimage'] = (!($product->getImage() == "no_selection")) ? $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage() : $imageHelper->getDefaultPlaceholderUrl('image');

                if ($product->getTypeId() == "simple") {
                    $productStock = $this->stockRegistry->getStockItem($product->getId());
                    $productAvailableQty = $productStock->getQty();
                    $productIsInStock = $productStock->getIsInStock();
                    $erpProductCategoryCode=explode(",", $product->getErpProductCategoryCode());
                    $customerData = $this->_dataHelper->getCustomerSession()->getCustomer();
                    $erpCustomerCodes = explode(",", $customerData->getErpCustomerCode());
                    $categoryCodeFlag = $this ->checkCategoryCodes($erpProductCategoryCode,$erpCustomerCodes);

                } else {
                    $productStock = $this->stockRegistry->getStockItem($product->getId());
                    $productAvailableQty = $productStock->getMaxSaleQty();
                    $productskudetails['availability'] = 1;
                    $productIsInStock = $productStock->getIsInStock();
                }

                if ($product->getHasOptions()) {
                    $productskudetails['options'] = 1;
                    array_push($productwithOptionslist, $sku);
                }

                if ($categoryCodeFlag) {
                    if ($product->getStatus() == 1 && $productIsInStock == true) {
                        $productskudetails['massege'] = "<span style='color: green;'>" . __('Item Available.') . "</span>";
                        $productskudetails['availability'] = 1;
                    } else {
                        if ($product->getStatus() == 1) {
                            $productskudetails['massege'] = "<span style='color: red;'>" . __('Product is out of stock.') . "</span>";
                            $productskudetails['availability'] = 0;
                            array_push($outstocklist, $sku);
                        } else {
                            $productskudetails['massege'] = "<span style='color: red;'>" . __('Product is disabled.') . "</span>";
                            $productskudetails['availability'] = 0;
                            array_push($invalidlist, $sku);
                        }
                    }
                } else {
                    $productskudetails['massege'] = "<span style='color: red;'>" . __('Please contact customer service for details') . "</span>";
                    $productskudetails['availability'] = 0;
                    array_push($qtylist, $sku);
                }
            } else {
                $productskudetails['name'] = $_product->getName();
                $productskudetails['sku'] = $_product->getSku();
                $productskudetails['typeid'] = $_product->getTypeId();
                $productskudetails['qty'] = $qty;
                $productskudetails['itemimage'] = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $_product->getImage();
                $productskudetails['massege'] = "<span style='color: color: #c62421 !important; font-size: 14px !important;'>" . __('Item quantity is not available') . "</span>";
                $productskudetails['availability'] = 0;

                array_push($qtylist, $sku);
            }

        } else {
            $supersededSkuArray = $this->isProductSuperseded(trim($sku));
            if(count($supersededSkuArray) > 0 ) {
                $superSededList = $supersededSkuArray;
                $productskudetails['massege'] = "<span style='color: color: #c62421 !important; font-size: 14px !important;'>" . __('Superseded sku') . "</span>";
                $productskudetails['availability'] = 0;
            }
            array_push($invalidlist, $sku);
            $productskudetails['massege'] = "<span style='color: #c62421 !important; font-size: 14px !important;'>" . __('Invalid sku') . "</span>";
            $productskudetails['availability'] = 0;

        }
        $productskudetails['outstocklist'] = $outstocklist;
        $productskudetails['qtylist'] = $qtylist;
        $productskudetails['nonsimplelist'] = $nonsimplelist;
        $productskudetails['invalidlist'] = $invalidlist;
        $productskudetails['productwithOptionslist'] = $productwithOptionslist;
        $productskudetails['typeid'] = $product->getTypeId();
        $productskudetails['superSededList'] = $superSededList;
        return $productskudetails;
    }

    public function isProductSuperseded($sku){
        $supersededSku = array();
        $store = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
        $productCollection = $this->_productCollectionFactory->create()
            ->addAttributeToSelect(['sku','status','Visibility','supersedes'])
            ->addAttributeToSelect('supersedes')
            ->addAttributeToFilter('status', 1)
            ->addAttributeToFilter('visibility', ['in' => [2, 3, 4]])
            ->addAttributeToFilter('supersedes', ['like' => '%'.$sku.'%'])
            ->getFirstItem();

        if($productCollection){
            $supersededSku[$sku] = $productCollection->getSku();
        }
        return $supersededSku;
    }

    /**
     *
     * @param $filename
     * @param $delimiter
     * @return array
     */
    public function csvHeaderCheck($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }

        $header = null;
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if ($header === null) {
                    $header = $row;
                    break;
                }
            }
            fclose($handle);
        }

        $headercsvData = $header;

        $csvFormat = [];
        $columns = null;
        $headerData = ['sku', 'qty','product_comment'];
        foreach ($headercsvData as $column) {
            if (!in_array($column, $headerData)) {
                if ($columns == null) {
                    $columns.= $column;
                } else {
                    $columns.= $delimiter . ' ' . $column;
                }
                $csvFormat['success'] = false;

                if ($columns == "") {
                    $csvFormat['message'] = __("Please upload proper csv file.");
                } else {
                    $csvFormat['message'] = __('Csv file is Wrong columns name with ' . $columns);
                }
            }
        }
        return $csvFormat;
    }

    public function checkCategoryCodes($erpProductCategoryCode, $erpCustomerCodes){
        $categoryCodeFlag = false;
        if(count($erpProductCategoryCode) > 0) {
            foreach($erpProductCategoryCode as $ProductCategoryCode) {
                if(in_array($ProductCategoryCode,$erpCustomerCodes)) {
                    $categoryCodeFlag=true;
                    break;
                }
            }
        }
        return $categoryCodeFlag;
    }

}
