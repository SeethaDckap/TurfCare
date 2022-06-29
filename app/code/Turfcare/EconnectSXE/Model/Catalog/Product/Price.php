<?php

namespace Turfcare\EconnectSXE\Model\Catalog\Product;

use LeanSwift\EconnectSXE\Model\Catalog\Product\Price as PriceModel;

/**
 * Class Price
 *
 * @package LeanSwift\Econnect\Model\Catalog\Product
 */
class Price extends PriceModel
{

    /**
     * @param $product
     * @param $customer
     * @param null $storeId
     * @return mixed|null
     */
    public function getCustomerPrice($product, $customer, $storeId = null)
    {
        $cacheHrs = $this->getCacheHours($storeId);
        if ($cacheHrs == '' && !is_int($cacheHrs)) {
            $erpPrice = null;
        } else {
            $customerId = $customer->getId();
            $productId = $product->getId();
            $productErpNumber = $product->getSxeProductno();
            $customerErpNumber = $customer->getSxeCustomerNr();
            if (!$customerErpNumber) {
                return null;
            }
            $websiteId = $customer->getWebsiteId();
            $storeId = ($storeId == 0) ? $storeId : $this->_helper->getStoreIdByWebsiteId($websiteId);
            $erpPrice = null;
            $result = current($this->_priceModel->loadByCustomerItem($customerErpNumber, [$productErpNumber]));
            $savedCustomerPrice = $result['price'];
            $getLastUpdated = $result['last_updated'];
            $now = microtime(true);
            if ($getLastUpdated) {
                // check if not updated in last n hours
                if ($now < (60 * 60 * $cacheHrs) + strtotime($getLastUpdated)) {
                    if ($savedCustomerPrice != null && $savedCustomerPrice >= 0) {
                        $erpPrice = $savedCustomerPrice;
                    }
                }
            }
        }
        if ($erpPrice == null) {
            $responses = $this->sendPriceRequest($customerErpNumber, [$productErpNumber], $qty = 1, $storeId);
            if ($responses) {
                $facility = $this->getHelper()->getDataValue($this->_warehousePath, $this->getStoreId());
                if ($responses) {
                    foreach ($responses as $erpItem => $price) {
                        $result['itemNo'] = $erpItem;
                        $result['salesPrice'] = $price;
                        $erpPrice = $price;
                        $this->_priceModel->updateinTable(
                            $result,
                            $customerId,
                            $customerErpNumber,
                            $productId,
                            $facility
                        );
                    }
                }
            }
        }
        return $erpPrice;
    }
}
