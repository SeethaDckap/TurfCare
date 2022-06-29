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

namespace Turfcare\EconnectSXE\Model\Sales\ION;

use LeanSwift\EconnectSXE\Helper\Configurations;
use LeanSwift\EconnectSXE\Helper\Data;
use LeanSwift\EconnectSXE\Helper\Order as OrderHelper;
use LeanSwift\EconnectSXE\Helper\Product;
use LeanSwift\EconnectSXE\Model\Config\Reader;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Directory\Model\RegionFactory;

/**
 * Class Order
 * @package LeanSwift\EconnectSXE\Model\Sales\ION
 */
class Order extends \LeanSwift\EconnectSXE\Model\Sales\ION\Order
{

    /**
     * const Shipping Method Mapping
     */
    const XML_PATH_SHIPPING_METHOD_MAPPING = 'econnectSXE/shipping_method/m3_delivery_method';

    /**
     * Shipping Method Mapping Attribute
     */
    const M3_SHIPPING_METHOD_ATTRIBUTE = "shipping_method";
    /**
     * Shipping Method Mapping Attribute Value
     */
    const M3_SHIPPING_METHOD_CODE = "m3_delivery_method";

    /**
     * @var Magento\Directory\Model\RegionFactory
     */
    protected $_regionFactory;


    public function __construct(Reader $reader, Data $dataHelper, Product $productHelper, OrderHelper $orderHelper, RegionFactory $regionFactory)
    {
        parent::__construct($reader,$dataHelper,$productHelper,$orderHelper);
        $this->_regionFactory = $regionFactory;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getOrderHeadDataForION()
    {
        $referenceNumber = '';
        $storeId = $this->getOrderStoreId();
        $canOrderReferenceSent = $this->dataHelper->getDataValue(Configurations::XML_PATH_ORDER_REFERENCE, $storeId);
        if ($canOrderReferenceSent) {
            $referenceNumber = 'Web Order#' . $this->order->getIncrementId();
        }
        $customer = $this->getCustomerRepository()->getById($this->order->getCustomerId());
        $shipVia =  $this->getDeliveryMethod();
        $purchaseOrderNumber = trim($this->getOrder()->getPayment()->getPoNumber());
        //add other data from the XML
        $defaultData = [
            'actionType' => $this->orderHelper->getActionType(),
            //'coNo'=>$this->dataHelper->getDataValue(Configurations::XML_PATH_COMPANY_NUMBER, $storeId),
            'poNo' => $purchaseOrderNumber,
            'refer' => $referenceNumber,
            'shipVia' => $shipVia,
            'takenby' => $this->orderHelper->getTakenBy(true),
            'transType' => $this->orderHelper->getTransactionType($storeId),
            'whse' => $this->dataHelper->getDataValue(Configurations::XML_DEFAULT_WAREHOUSE, $storeId)
        ];
        $finalData = $defaultData;
        $extraData = $this->formatFinalData('sxt_orderV4', $defaultData);
        if ($extraData) {
            $finalData = $extraData;
        }
        return ['sxt_orderV4' => [$defaultData]];
    }

    /**
     * @param $customerObject
     * @return array
     */
    public function getCustomerInfoForION($customerObject)
    {
        $defaultData = ['custNo' => $customerObject->getValue()];
        $finalData = $defaultData;
        $extraData = $this->formatFinalData('sxt_customer', $defaultData);
        if ($extraData) {
            $finalData = $extraData;
        }
        return ['sxt_customer' => [$defaultData]];
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getOrderItemDetailsForION()
    {
        $counter = 1;
        $itemListItems = [];
        foreach ($this->order->getAllItems() as $item) {
            $lineComment = '';
            $additionalOptions = $item->getProductOptionByCode('additional_options');
            if(!empty($additionalOptions)){ /* Get the line text for each item */
                $options = reset($additionalOptions);
                $lineComment = $options['value'];
            }
            $product = $this->getProductRepository()->getById($item->getProductId());
            if (($product->getTypeId() != 'giftcard') && ($product->getTypeId() != 'configurable')) {
                $itemDataFromOrder = [];
                //add other data from the XML
                $this->totalLines = $counter;
                $itemDataFromOrder =
                    [
                        'lineComments' => $lineComment,
                        'lineIden' => +$counter,
                        'qtyOrd' => (string)(int)$item->getQtyOrdered(),
                        'sellerProd' => $this->productHelper->getSXEProductNumber($product)
                    ];
                $finalData = $itemDataFromOrder;
                $extraLineData = $this->orderHelper->prepareExtraProductData($this->order->getStoreId(), 'sxt_itemV4', $product);
                $combinedData = array_merge($itemDataFromOrder, $extraLineData);
                $formattedExtraData = $this->formatFinalData('sxt_itemV4', $combinedData);
                $extraData = array_merge($formattedExtraData, $combinedData);
                if ($extraData) {
                    ksort($extraData);
                    $finalData = $extraData;
                }
                $itemListItems[] =  $finalData;
                $counter = $counter + 1;
            }
        }
        return ['sxt_itemV4' => $itemListItems];
    }

    /**
     * @return array[]
     */
    public function getScheduleData()
    {
        $finalData = [];
        $deliveryMethod=$this->getDeliveryMethod();
        $defaultData = ['shipVia' => $deliveryMethod];
        $extraData = $this->formatFinalData('sxt_schedule', $defaultData);
        if ($extraData) {
            $finalData = $extraData;
        }

        return ['sxt_schedule' => [$finalData]];
    }

    /**
     * @return mixed|string
     * @throws LocalizedException
     */
    public function getDeliveryMethod()
    {
        $order=$this->getOrder();
        $storeId = $this->getOrderStoreId();
        $shippingMethodCode = $order->getShippingMethod();
        $shippingMethodData = $this->dataHelper->getDataValue(self::XML_PATH_SHIPPING_METHOD_MAPPING,$storeId);
        $deliveryMethodValue = '';
        if($shippingMethodCode == "flatrate_flatrate") {
            $shippingAddressObject = $order->getShippingAddress();
            if ($shippingAddressObject) {
                $shippingAddress = $shippingAddressObject->getData();
                if (!empty($shippingAddress)) {
                    try{
                        $addressVal = $this->getAddressRepository()->getById($shippingAddress['customer_address_id']);
                        $deliveryMethodData = $addressVal->getCustomAttribute('delivery_method');
                        if ($deliveryMethodData) {
                            $deliveryMethodValue = $deliveryMethodData->getValue();
                        }
                    } catch (NoSuchEntityException $e) {

                        $this->dataHelper->writeLog('Order Sync Error:  <br>' . $e->getMessage() . '<br>', false);
                    }
                }
            }
        }else{
            $shippingMethodData;
            $mapping = '';
            if ($shippingMethodData) {
                $mapping = $this->dataHelper->getSerializeObject()->unserialize($shippingMethodData);
            }
            if (count($mapping) > 0) {
                foreach ($mapping as $key) {
                    if ($key[self::M3_SHIPPING_METHOD_ATTRIBUTE] == $shippingMethodCode) {
                        $deliveryMethodValue = $key[self::M3_SHIPPING_METHOD_CODE];
                    }
                }
            }
        }

        return $deliveryMethodValue;
    }

    /**
     * @param $data
     * @param $addressType
     * @return array
     */
    public function getAddressDataForIon($data, $addressType)
    {
        $section = ($addressType == "billing") ? 'sxt_billto' : 'sxt_shipto';
        $addressData = $this->orderHelper->splitAddress($data['street'], 'address');
        if ($addressType == "shipping" && isset($addressData['address3'])) {
            $this->setAddressLine3($addressData['address3']);
            unset($addressData['address3']);
        }
        /* TC-246 Send region code instead of region name in order create request */
        if(isset($data['region_id'])){
            $regionId = $data['region_id'];
            $region = $this->_regionFactory->create()->load($regionId);
            $regionCode = $region->getCode();
            if($regionCode) {
                $data['region'] = $regionCode;
            }
        }

        $defaultData = [
            'city' => $data['city'],
            'countryCd' => $data['country_id'],
            'name' => $data['firstname'] .' '. $data['lastname'],
            'phone' => $data['telephone'],
            'postalCd' => $data['postcode'],
            'state' => $data['region']
        ];
        $defaultData = array_merge($addressData, $defaultData);
        //this is needed for the ship to for the shipping address
        if ($addressType == "shipping" && isset($data['sxe_ship_to'])) {
            $shipToData = ['shipToNo' => $data['sxe_ship_to']];
            $defaultData = array_merge($defaultData, $shipToData);
        }
        
        $finalData = $defaultData;
        $extraData = $this->formatFinalData($section, $defaultData);
        if ($extraData) {
            $finalData = $extraData;
        }

        return $finalData;
    }
}