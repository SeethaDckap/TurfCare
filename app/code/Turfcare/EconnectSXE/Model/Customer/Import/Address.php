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

namespace Turfcare\EconnectSXE\Model\Customer\Import;

use Exception;
use LeanSwift\EconnectSXE\Api\ScopeInterface;
use LeanSwift\EconnectSXE\Helper\Customer as CustomerHelper;
use LeanSwift\EconnectSXE\Helper\Data;
use LeanSwift\EconnectSXE\Helper\Erpapi;
use LeanSwift\EconnectSXE\Helper\ION as IonHelper;
use LeanSwift\EconnectSXE\Helper\Xpath;
use LeanSwift\EconnectSXE\Model\CustomerFactory as LsCustomer;
use LeanSwift\EconnectSXE\Model\CustomerMaster;
use LeanSwift\EconnectSXE\Model\Sync\CustomerAddressSync;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\AddressRepository;
use Magento\Customer\Model\ResourceModel\Customer\Collection as Customer;
use Magento\Directory\Model\RegionFactory;
use Magento\Eav\Model\Entity\Context;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Model\AbstractModel;
use SimpleXMLElement;

class Address extends \LeanSwift\EconnectSXE\Model\Customer\Import\Address
{

    /**
     * Address constructor.
     * @param Context $context
     * @param Customer $customerCollection
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressRepository $addressRepository
     * @param RegionFactory $regionFactory
     * @param LsCustomer $lscustomer
     * @param Data $helperData
     * @param CustomerInterface $CustomerInterface
     * @param CustomerHelper $customer
     * @param Xpath $xpath
     * @param CustomerAddressSync $CustomerAddressSync
     * @param IonHelper $IonHelper
     * @param CustomerMaster $customerMaster
     */
    public function __construct(
        Context $context,
        Customer $customerCollection,
        CustomerRepositoryInterface $customerRepository,
        AddressRepository $addressRepository,
        RegionFactory $regionFactory,
        LsCustomer $lscustomer,
        Data $helperData,
        CustomerInterface $CustomerInterface,
        CustomerHelper $customer,
        Xpath $xpath,
        CustomerAddressSync $CustomerAddressSync,
        IonHelper $IonHelper,
        CustomerMaster $customerMaster,
        ScopeInterface $scope
    )
    {
        parent::__construct($context,$customerCollection,$customerRepository,$addressRepository,$regionFactory,$lscustomer, $helperData,$CustomerInterface,$customer,$xpath,$CustomerAddressSync,$IonHelper,$customerMaster,$scope);
    }

    /**
     * @param $customerAddressData
     * @param $customerId
     * @param $storeId
     * @param string $billFlag
     * @param string $shipFlag
     * @throws \Exception
     */
    public function updateAddressFromPost($customerAddressData, $customerId, $storeId, $billFlag = '', $shipFlag = '')
    {
        $lsCustomer = $this->_lsCustomer->create();
        $updateCount = 0;
        if (!empty($customerAddressData)) {
            if (!isset($customerAddressData['country_id']) || !$customerAddressData['country_id']) {
                $customerAddressData['country_id'] = $this->_helper->getDefaultCountry($storeId);
            }
            // only the region exist in the customerData
            if (isset($customerAddressData['region']) && $customerAddressData['region']) {
                $regionModel = $this->_region->create();
                $regionData = $regionModel->loadByCode(
                    $customerAddressData['region'],
                    $customerAddressData['country_id']
                );
                $customerAddressData['region_id'] = $regionData->getRegionId();
            }
            //if billing address then set to default billing address
            if ($billFlag && $this->defaultBillingId) {
                $customerAddressData['entity_id'] = $this->defaultBillingId;
            }
            //Set phone number field if empty
            if(!isset($customerAddressData['telephone']) || !$customerAddressData['telephone']) {
                $customerAddressData['telephone'] = '0000000000';
            }
            $customerAddressData = $this->fillValuesForRequiredAttribute($customerAddressData);
            $lsCustomer->saveData(
                $customerAddressData,
                $customerId,
                $updateCount,
                $billFlag,
                $shipFlag
            );
        }
    }

    /**
     * @param $addressResp
     * @param $mappings
     * @param $namespace
     * @param $customerId
     * @param $storeId
     * @param string $billFlag
     * @param string $shipFlag
     * @return string
     * @throws \Exception
     */
    public function updateAddressData(
        $addressResp,
        $mappings,
        $namespace,
        $customerId,
        $storeId,
        $billFlag = '',
        $shipFlag = ''
    )
    {
        if (empty($addressResp) || !is_array($addressResp)) {
            return '';
        }
        foreach ($addressResp as $response) {
            $customerAddressData = [];
            foreach ($mappings as $field) {
                $erpValue = '';
                if ($this->isIonRequest) {
                    if (isset($response[$field[self::M3_ATTRIBUTE]])) {
                        $erpValue = $response[$field[self::M3_ATTRIBUTE]];
                    }
                } else {
                    if ($shipFlag) {
                        $response->registerXPathNamespace('ns', $namespace);
                        $erpObject = $response->xpath(Xpath::LOCAL_DEFAULT_NAMESPACE_CODE . $field[self::M3_ATTRIBUTE]);
                        if (!empty($erpObject)) {
                            $erpValue = $erpObject[0]->__toString();
                        }
                    } else {
                        $erpValue = current(
                            $this->xpathHelper->parseResponse(
                                [$response],
                                $namespace,
                                Xpath::DEFAULT_NAMESPACE_CODE . $field[self::M3_ATTRIBUTE]
                            )
                        );
                    }
                }

                if ($erpValue) {
                    $customerAddressData = $this->formCustomerAddressData($customerAddressData, $field, $erpValue);
                }
            }
            if (!empty($customerAddressData)) {
                $this->updateAddressFromPost($customerAddressData, $customerId, $storeId, $billFlag, $shipFlag);
            }
        }
        if ($this->defaultShippingId) {
            $lsCustomer = $this->_lsCustomer->create();
            $lsCustomer->setDefaultShipping($customerId, $this->defaultShippingId);
        }
    }

    /**
     * @param $customer
     * @param int $syncType
     * @param null $customerAddressData
     * @param null $namespace
     * @param bool $type
     * @param bool $isIon
     * @return array
     */
    public function initSync(
        $customer,
        $syncType = 0,
        $customerAddressData = null,
        $namespace = null,
        $type = false,
        $isIon = false
    )
    {
        $this->isIonRequest = $isIon;
        $cuno = null;
        if ($customer instanceof CustomerInterface) {
            $customAttribute = $customer->getCustomAttribute('sxe_customer_nr');
            if ($customAttribute) {
                $cuno = trim($customAttribute->getValue());
            }
        } else {
            $cuno = trim($this->_helper->getERPCustomerNo());
        }
        $websiteId = $customer->getWebsiteId();
        $webCustomerId = $customer->getId();
        $storeId = $this->_helper->getStoreIdByWebsiteId($websiteId);
        if ($cuno == null) {
            return [
                self::STATUS_VAL => 0,
                self::INFO_VAL =>
                    'Web Customer(' . $webCustomerId . ') | Please populate the SX.e/CSD Customer Number in Account-Information section.'
            ];
        } else {
            $msg = 'Web Customer(' . $webCustomerId . ') | SX.e/CSD Customer(' . $cuno . ') | ';
            switch ($syncType) {
                case 0:
                    $masterMap = $this->_getHelper()->getCustomerMasterMapping($storeId);
                    $namespace = Erpapi::BASE_NAMESPACE . '.' . Erpapi::CUSTOMER_ADDRESS_API;
                    $masterResults = $this->_importCustomerDetails($cuno, $customer, $masterMap, $customerAddressData, $namespace);
                    if ($masterResults) {
                        $val = 0;
                        $msg .= " Customer details could not be updated. Please check the logs for further details";
                    } else {
                        $msg .= " Customer details updated successfully";
                        $val = 1;
                    }
                    return [self::STATUS_VAL => $val, self::INFO_VAL => $msg, 'cuno' => $cuno];

                case 1:
                    $customerAddressType = '';
                    //billing address
                    if ($type === 1) {
                        $customerAddressType = 'Billing';
                        $namespace = Erpapi::BASE_NAMESPACE . '.' . Erpapi::CUSTOMER_ADDRESS_API;
                        $mapping = $this->getBillingAddressMap($storeId);
                    } elseif ($type === 2) {
                        //shipping address
                        $customerAddressType = 'Shipping';
                        $namespace = Erpapi::BASE_NAMESPACE . '.' . Erpapi::CUSTOMER_SHIPPING_ADDRESS_API;
                        $mapping = $this->getShippingAddressMap($storeId);
                    }
                    if (empty($mapping)) {
                        $msg .= " Customer " .
                            $customerAddressType .
                            " Address Mapping cannot be found | ";
                        return [self::STATUS_VAL => 0, self::INFO_VAL => $msg];
                    }
                    if ($customerAddressData == null) {
                        $msg .= "No Address received ";
                        return [self::STATUS_VAL => 0, self::INFO_VAL => $msg];
                    }
                    $this->_importAddresses(
                        $customer,
                        $mapping,
                        $customerAddressData,
                        $storeId,
                        $namespace,
                        $type
                    );
                    break;
            }
        }
    }

    /**
     * @param $batchCollection
     */
    public function updateCustomerCollection($batchCollection)
    {
        $customerCountList = [];
        foreach ($batchCollection as $customer) {
            try {
                $customerCountList[] = $customer;
                $scopeData['website'] = $customer->getWebsiteId();
                $this->scope->setRequest($scopeData)->setScopeData(true);
                $erpNr = $this->customerHelper->getCustomerNumber($customer);
                $billingAddressResp = $this->getBillingAddressList(trim($erpNr));
                $shipAddressesResp = $this->getShippingAddressList(trim($erpNr));
                $nameSpace = null;
                $customerId = $customer->getId();
                $customerInfo = $this->_customerRepository->getById($customerId);
                $type = '';
                $sync = $this->initSync(
                    $customerInfo,
                    0,
                    $billingAddressResp,
                    $nameSpace,
                    0,
                    $this->CustomerAddressSync->isIonRequest()
                );

                if ($billingAddressResp) {
                    $type = 'Billing';
                    $sync = $this->initSync(
                        $customerInfo,
                        1,
                        $billingAddressResp,
                        $nameSpace,
                        1,
                        $this->CustomerAddressSync->isIonRequest()
                    );
                }

                if ($shipAddressesResp) {
                    $type = 'Shipping';
                    $sync = $this->initSync(
                        $customerInfo,
                        1,
                        $shipAddressesResp,
                        $nameSpace,
                        2,
                        $this->CustomerAddressSync->isIonRequest()
                    );
                }
            } catch (LocalizedException $e) {
                $errorIds[] = $customer->getId();
                if ($type) {
                    $this->CustomerAddressSync->writeLog(__(
                        sprintf(
                            '%s Error while syncing %s address for the customer # %s',
                            $e->getMessage(),
                            $type,
                            $customer->getId()
                        )
                    ));
                } else {
                    $this->CustomerAddressSync->writeLog(__(
                        sprintf(
                            '%s Error while syncing the customer # %s',
                            $e->getMessage(),
                            $customer->getId()
                        )
                    ));
                }
                continue;
            }
        }
    }
}