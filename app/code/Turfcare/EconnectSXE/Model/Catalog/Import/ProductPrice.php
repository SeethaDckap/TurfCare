<?php
namespace Turfcare\EconnectSXE\Model\Catalog\Import;

use LeanSwift\EconnectSXE\Helper\Constant;
use LeanSwift\EconnectSXE\Helper\Erpapi;
use LeanSwift\EconnectSXE\Helper\Xpath;
use LeanSwift\EconnectSXE\Model\Catalog\Import\ProductPrice as SchedulerProductPrice;

class ProductPrice extends SchedulerProductPrice
{

    /**
     * @param $websiteIds
     * @param $sku
     * @param $erpItemNumber
     */
    public function importPriceForWebsites($websiteIds, $sku, $erpItemNumber)
    {
        foreach ($websiteIds as $websiteId) {
            $scopeData['website'] = $websiteId;
            $this->scope->setRequest($scopeData)->setScopeData(true);
            $mapping = $this->_helperData->getERPMappingByAPI('price', $websiteId, 'sync', Constant::KEY_WEBSITE);
            // TC-19 Allow backorder for items
            $mapping['statusType'] = array('sxe_attribute' => '//ns:statustype','default_attribute_value' =>'', 'use_on' => 'both');
            if (count($mapping) > 0) {
                $this->importPrice($mapping, $sku, $erpItemNumber, $websiteId);
            }
        }
    }
    /**
     * @param $websiteId
     * @param $wareHouse
     * @param $chunkedData
     * @return array
     */
    public function updateProductPriceViaCRON($websiteId, $wareHouse, $chunkedData)
    {
        $finalRequest = [];
        $finalResponse = [];
        $output = [];
        $skuColl = [];
        $scopeData['website'] = $websiteId;
        $this->scope->setRequest($scopeData)->setScopeData(true);
        $mapping = $this->_helperData->getERPMappingByAPI('price', $websiteId, 'sync', Constant::KEY_WEBSITE);
        $mapping['statusType'] = array('sxe_attribute' => '//ns:statustype','default_attribute_value' =>'', 'use_on' => 'both');
        $mergedMapping = ['api' => 'price', 'map' => $mapping];
        if (count($mapping) > 0) {
            $this->isIONenabled = $this->version->isIONEnabled();
            if (!$this->isIONenabled) {
                $this->adapter = $this->_request;
            } else {
                $this->adapter = $this->ionRequest;
            }
            $loggerInfo = $this->customLogger ?? $this->logger->getLogInfo();
            $this->adapter->setAPI(Erpapi::PRODUCT_PRICE_API);
            $this->adapter->setLogInfo($loggerInfo);
            foreach ($chunkedData as $requestProductData) {
                //Price
                $productNumber = $requestProductData['sxe_productno'];
                $skuColl[$productNumber] = $requestProductData['sku'];
                if (!$this->isIONenabled) {
                    $postData = [
                        'ProductCode' => $productNumber,
                        'Warehouse' => $wareHouse,
                        'CrossReferenceFlag' => true,
                        'Ininfieldvalue' => $this->productHelper->getInFieldValueFieldsForSoap(Erpapi::PRODUCT_PRICE_API, $this->getNameSpace())
                    ];
                    $finalRequest[] = $this->adapter->formRequest($postData);
                } else {
                    $postData = [
                        'productCode' => $productNumber,
                        'warehouse' => $wareHouse,
                        'crossReferenceFlag' => true,
                        'tInfieldvalue' => $this->productHelper->getInFieldValueFields(Erpapi::PRODUCT_PRICE_API)
                    ];
                    $finalRequest[] = $postData;
                }
                unset($postData);
            }
            if ($this->isIONenabled) {
                $this->adapter
                    ->setAPI(Erpapi::PRODUCT_PRICE_API)
                    ->setConcurrent(true)
                    ->setMultipleRequest($finalRequest)
                    ->setConcurrencyLevel(50)->send();
                unset($finalRequest);
                $responseList = $this->adapter->getMultiRequestResponse();
                if (!empty($responseList)) {
                    foreach ($responseList as $key => $response) {
                        if (isset($response['error'])) {
                            if ($response['error']) {
                                $output = [];
                            }
                        }
                        if (isset($response['response'])) {
                            $output[$response['response']['crossReferenceProduct']] = $response['response'];
                        }
                        if (isset($response['response']['cErrorMessage'])) {
                            if ($response['response']['cErrorMessage']) {
                                $output[$response['response']['crossReferenceProduct']] = [];
                            }
                        }
                    }
                    $this->timestampProductPrice($output, $skuColl, $websiteId, $mergedMapping);
                }
            } else {
                $rowData = [];
                $this->adapter->setAPI(Erpapi::PRODUCT_PRICE_API);
                $this->adapter->setRequestBody($finalRequest)->send();
                $responseList = $this->adapter->getResponse();
                $nameSpace = $this->getNameSpace();
                foreach ($responseList as $response) {
                    $errorMessage = current($this->xpathHelper->getErrorMessage($response, $nameSpace));
                    if (!$errorMessage) {
                        $productNumber = $this->xpathHelper->parseResponse(
                            $response,
                            $nameSpace,
                            Xpath::PRODUCT_NUMBER_PRODUCT_GENERAL
                        );
                        if (!empty($productNumber)) {
                            $SxeProductNumber = current($productNumber);
                            if ($SxeProductNumber) {
                                $productData = $this->parsingProductData(false, $mapping, $response);
                                if (!empty($productData)) {
                                    $productData[Constant::KEY_SKU] = $skuColl[$SxeProductNumber] ?? '';
                                    if ($websiteId) {
                                        $productData[Constant::KEY_WEBSITE] = $websiteId;
                                    }
                                    if ($productData[Constant::KEY_SKU]) {
                                        $rowData[] = $productData;
                                    }
                                }
                            }
                        }
                        unset($productNumber);
                        unset($productData);
                    }
                }
                if (!empty($rowData)) {
                    $this->_massProductSave->_saveCustomProducts($rowData);
                }
                unset($rowData);
            }
            unset($responseList);
            unset($skuColl);
        } else {
            $this->writeLog(
                __(
                    'Price configuration cannot be found.
                Contact administrator to setup price synchronization for Website - ' . $websiteId
                )
            );
        }
        return $finalResponse;
    }
    /**
     * @param $response
     * @param $skuColl
     * @param $websiteId
     */
    public function timestampProductPrice($response, $skuColl, $websiteId, $mergedMapping)
    {
        $rowData = $productData = $bulkSyncMapping = $syncMapping = [];
        $oldItemCount = $newItemCount = 0;
        if (!empty($response)) {
            foreach ($response as $erpItemNumber => $record) {
                if ($this->isIONenabled) {
                    if (!isset($record['tIcprodwhsedata']['t-icprodwhsedata'])) {
                        $recordData['tIcprodwhsedata']['t-icprodwhsedata'][] = $record;
                    } else {
                        $recordData = $record;
                    }
                } else {
                    $recordData = $record;
                }
                if (array_key_exists($erpItemNumber, $skuColl)) {
                    if ($this->isIONenabled) {
                        $productData = $this->parsingProductData(false,
                            $mergedMapping['map'],
                            $recordData['tIcprodwhsedata']['t-icprodwhsedata']
                        );
                    } else {
                        $productData = $this->parsingProductData(false,
                            $mergedMapping['map'],
                            $recordData
                        );
                    }

                    $productData[Constant::KEY_SKU] = $skuColl[$erpItemNumber];
                    $oldItemCount++;
                    $this->writeLog('Item[] for sync: ' . $productData[Constant::KEY_SKU], false, null, 'catalog');
                    if (isset($productData[Constant::KEY_SKU])) {
                        $productData[Constant::KEY_WEBSITE] = $websiteId;
                        $rowData[] = $productData;
                        unset($productData);
                    }
                }
            }
            $this->writeLog(
                'Product Sync info : New Items ' . $newItemCount . ' Existing Items: ' . $oldItemCount,
                false,
                null,
                'catalog'
            );
            $this->_massUpdateProduct($rowData);
        }
    }
}