<?php

namespace LeanSwift\Turfcare\Helper;

use Magento\Catalog\Model\ResourceModel\Category\StateDependentCollectionFactory;
use Magento\Framework\Data\Collection;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use LeanSwift\EconnectSXE\Helper\Product;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Checkout\Helper\Cart;
use LeanSwift\Turfcare\Block\Product\SupersededInformation;
use Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku;
use LeanSwift\EconnectSXE\Helper\Product as ProductHelper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Erp product category code
     */
    const ERP_PRODUCT_CATEGORY_CODE = "erp_product_category_code";

    /**
     * @var \Magento\Cms\Helper\Page
     */
    private $pageHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\StateDependentCollectionFactory
     */
    public $collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $sessionFactory;

    /**
     * productCollectionFactory;
     *
     * @var
     */
    protected $_productCollectionFactory;

    /**
     * @var  $productstatus
     */
    protected $productStatus;

    /**
     * @var $productVisibility
     */
    protected $productVisibility;

    protected $_stockStateInterface;

    protected $_cart;

    /**
     * @var \LeanSwift\Ctos\Block\SupersededInformation
     */
    protected $supersededInformation;

    protected $_getSourceItemsDataBySku;

    protected $productHelper;


    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Cms\Helper\Page $pageHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\SessionFactory $sessionFactory
     * @param StateDependentCollectionFactory $collectionFactory
     * @param CollectionFactory $productCollectionFactory
     * @param Status $productStatus
     * @param Visibility $productVisibility
     * @param StockStateInterface $stockStateInterface
     * @param Cart $cart
     * @param SupersededInformation $supersededInformation
     * @param GetSourceItemsDataBySku $getSourceItemsDataBySku
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Cms\Helper\Page $pageHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        StateDependentCollectionFactory $collectionFactory,
        CollectionFactory $productCollectionFactory,
        Status $productStatus,
        Visibility $productVisibility,
        StockStateInterface $stockStateInterface,
        Cart $cart,
        SupersededInformation $supersededInformation,
        GetSourceItemsDataBySku $getSourceItemsDataBySku,
        ProductHelper $productHelper
    )
    {
        parent::__construct($context);
        $this->pageHelper = $pageHelper;
        $this->storeManager = $storeManager;
        $this->sessionFactory = $sessionFactory;
        $this->collectionFactory = $collectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->_stockStateInterface = $stockStateInterface;
        $this->_cart = $cart;
        $this->supersededInformation = $supersededInformation;
        $this->_getSourceItemsDataBySku = $getSourceItemsDataBySku;
        $this->productHelper = $productHelper;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->sessionFactory->create();
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Get hidden categories from guest users
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function hideCategories()
    {
        $hideCategoryArray = [];
        $storeId = $this->getStoreId();
        $collection = $this->collectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addAttributeToSelect(array('is_active_for_logged_in_users'));
        $collection->addAttributeToFilter('include_in_menu', 1);
        $collection->addIsActiveFilter();
        $collection->addNavigationMaxDepthFilter();
        $collection->addUrlRewriteToResult();
        $collection->addOrder('level', Collection::SORT_ORDER_ASC);
        $collection->addOrder('position', Collection::SORT_ORDER_ASC);
        $collection->addOrder('parent_id', Collection::SORT_ORDER_ASC);
        $collection->addOrder('entity_id', Collection::SORT_ORDER_ASC);
        $collection->addAttributeToFilter('is_active_for_logged_in_users', array('neq' => 1));
        if (($collection->getSize()) > 0) {
            $hideCategoryArray = $collection->getColumnValues('entity_id');
        }

        return $hideCategoryArray;
    }

    /**
     * Is add stock filter
     *
     * @return bool
     */
    public function isAddStockFilter()
    {
        $isShowOutOfStock = $this->scopeConfig->isSetFlag(
            'cataloginventory/options/show_out_of_stock',
            ScopeInterface::SCOPE_STORE
        );

        return false === $isShowOutOfStock;
    }

    /**
     * Get customer codes for product collection filter
     *
     * @return |null
     */
    public function getErpCustomerCodes()
    {
        $erpCustomerCodes = '';
        $customer = $this->getCustomerSession()->getCustomer();
        if ($customer->getId()) {
            $erpCustomerCodes = explode(",", $customer->getErpCustomerCode());
        }
        return $erpCustomerCodes;
    }

    /**
     * Return allowed dealer product ids based on the customer types
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDealerItems()
    {
        $allowedProductIds = [];
        $collection = $this->getProductCollection();
        if (($collection->getSize()) > 0) {
            $erpCustomerCodes = $this->getErpCustomerCodes();
            if (!empty($erpCustomerCodes) && count($erpCustomerCodes) > 0) {
                $collection->addAttributeToFilter(self::ERP_PRODUCT_CATEGORY_CODE, ["in", $erpCustomerCodes]);
            } else {
                $collection->addAttributeToFilter(self::ERP_PRODUCT_CATEGORY_CODE, ["in", array('')]);
            }
            $allowedProductIds = $collection->getColumnValues('entity_id');
        }

        return $allowedProductIds;
    }

    /**
     * Product Collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductCollection()
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addStoreFilter($this->storeManager->getStore());
        $collection->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()]);
        $collection->setVisibility($this->productVisibility->getVisibleInSiteIds());

        return $collection;
    }

    /**
     * @return mixed
     */
    public function getSenderEmail()
    {
        $smtpEmail = $this->scopeConfig->getValue('trans_email/ident_support/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $smtpEmail;
    }

    /**
     * Get cart item additional data
     *
     * @param $product
     * @return mixed
     */
    public function getCartItemData($product){

        $resultQty = 0;
        $sxeProductNumber = $this->productHelper->getSXEProductNumber($product);
        $productId = $product->getId();

        $stockInfoModel = $this->_getSourceItemsDataBySku->execute($product->getSku());
        $stockInfo = isset($stockInfoModel[0]['quantity'])?$stockInfoModel[0]['quantity']:0;

        //$stockInfo = $this->_stockStateInterface->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
        $resultArray['sxe_product_number'] = ($sxeProductNumber)?$sxeProductNumber:$product->getSku();
        $resultArray['qty'] = ($stockInfo)?$stockInfo:0;
        if($productId) {
            $resultQty = $this->getItemBackOrderQty($productId,$resultArray['qty']);
        }
        $resultArray['backorderedqty'] =$resultQty;

        return $resultArray;
    }


    /**
     * Get ItemBackOrderQty
     *
     * @param $productId
     * @return float|int
     */
    public function getItemBackOrderQty($productId,$availableqty)
    {
        $resultQty = 0;
        $quoteItems = $this->_cart->getQuote()->getItems();
        if($quoteItems) {
            foreach ($quoteItems as $item) {
                if ($productId == $item->getProduct_id()) {
                    $quoteQty = $item->getQty();
                }
            }
        }
        $quoteQty = 0;
        if($quoteQty > $availableqty){
            $resultQty = $quoteQty - $availableqty;
        }
        return $resultQty;
    }

    /**
     * Superseded Item Exist Check
     *
     * @param $sku
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isSupersededItemExist($sku){
        $result = false;
        $name = $this->supersededInformation->getSupersededItemName($sku);
        if($name){
            $result = true;
        }

        return $result;
    }

    /**
     * Superseded Item Exist Check
     *
     * @param $sku
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSupersededUrl($sku){
        $result = "#";
        $url = $this->supersededInformation->getSupersededProductURL($sku);
        if($url){
            $result = $url;
        }

        return $result;
    }

    /**
     * Superseded Item Exist Check
     *
     * @param $sku
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSupersededSku($sku){
        $finalSku = "";
        $sku = $this->supersededInformation->getSupersededItemSku($sku);
        if($sku){
            $finalSku = $sku;
        }

        return $finalSku;
    }

    /**
     * Get current store code
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreViewCode(){
        $languageArray = array('en'=>"English","fr"=>"French");
        $storeCode = $this->storeManager->getStore()->getCode();

        return $languageArray[$storeCode];
    }

    /**
     * TUR-18 Get customer division data
     */
    public function getErpDivision()
    {
        $erpDivision = '';
        $customer = $this->getCustomerSession()->getCustomer();
        if($customer->getErpDivision()) {
            $erpDivision=$customer->getErpDivision();
        }
        return $erpDivision;
    }
}