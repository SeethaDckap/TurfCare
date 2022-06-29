<?php

namespace Turfcare\EconnectSXE\Observer\CustomerPrice;

use LeanSwift\EconnectSXE\Api\PriceInterface;
use LeanSwift\EconnectSXE\Helper\CustomerPriceRegistry;
use LeanSwift\EconnectSXE\Helper\Data;
use LeanSwift\EconnectSXE\Helper\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Registry;
use Magento\Catalog\Model\ProductFactory;
use LeanSwift\EconnectSXE\Observer\CustomerPrice\BindCustomerPriceonListingLoadObserver as CustomerPriceListObserver;

class BindCustomerPriceonListingLoadObserver extends CustomerPriceListObserver
{
    /**
     * Econnect helper
     *
     * @var Data|null
     */
    protected $_helperData = null;

    /**
     * @var Customerprice
     */
    protected $_customerprice;

    protected $_customerpriceEnabled;

    protected $_listingPriceEnabled;

    protected $_registry;

    protected $_layout;

    /**
     * Magento Product Object
     *
     * @var Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * BindCustomerPriceonListingLoadObserver constructor.
     *
     * BindCustomerPriceonListingLoadObserver constructor.
     * @param Data $helperData
     * @param PriceInterface $priceInterface
     * @param Registry $registry
     * @param string $customerPriceEnable
     * @param string $listingPriceEnable
     */
    public function __construct(
        Data $helperData,
        Priceinterface $priceInterface,
        CustomerPriceRegistry $CustomerPriceRegistry,
        Product $productHelper,
        $listingPriceEnable = '',
        ProductFactory $product
    ) {
        $this->_product = $product;
        parent::__construct($helperData,$priceInterface,$CustomerPriceRegistry,$productHelper,$listingPriceEnable);

    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        $productData = [];
        $storeId = $this->_helperData->getStoreId();
        $erpCustomerNr = $this->_priceInterface->getCustomerErpNumber();
        $listingCustomerPriceEnabled = $this->_helperData->getDataValue($this->_listingPriceEnabled, $storeId);
        $this->customerPriceRegistry->enablePricingInListing($listingCustomerPriceEnabled);
        if (!$erpCustomerNr || !$listingCustomerPriceEnabled) {
            return $this;
        }
        if ($listingCustomerPriceEnabled) {
            $this->customerPriceRegistry->enableCustomerSpecificPrice($listingCustomerPriceEnabled);
            $erpCustomerNr = $this->_priceInterface->getCustomerErpNumber();
            if ($erpCustomerNr) {
                $productCollection = $observer->getEvent()->getCollection();
                $productCollection->addFieldToSelect(['entity_id', Product::SXE_PRODUCT_NUMBER]);
                foreach ($productCollection as $product) {
                    $productNumber = $this->productHelper->getSXEProductNumber($product);
                    if (!$productNumber) {
                        continue;
                    }
                    $productData[$productNumber] = [$product->getId(), 1];
                }
                if (empty($productData)) {
                    return $this;
                }
                $this->_priceInterface->updateCustomerPrice($productData, $erpCustomerNr);
            }
        }
    }
}
