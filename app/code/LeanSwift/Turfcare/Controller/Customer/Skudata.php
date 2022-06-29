<?php
/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Orderbysku
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace LeanSwift\Turfcare\Controller\Customer;

use Magedelight\Orderbysku\Controller\Customer\Skudata as CoreSkuData;

class Skudata extends CoreSkuData
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context,$urlBuilder,$resultJsonFactory);

    }

    /**
     * Rewrite Default customer account page
     *
     * @return Json Array
     */
    public function execute()
    {

        $result = $this->resultJsonFactory->create();
        $files = $this->getRequest()->getFiles();
        $file = [];
        foreach ($files as $_file) {
            $file = $_file;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if ($ext !== "csv") {
            $response['error'] = true;
            $response['message'] = __('Please enter valid CSV file format only.');
            $result->setData($response);
            return $result;
        }
           
        $checkData = $this->_objectManager->create('Magedelight\Orderbysku\Helper\Productdetail')->csvHeaderCheck($file['tmp_name']);
        if ($checkData && $checkData['success'] == false) {
            $response['error'] = true;
            $response['message'] = $checkData['message'];
            $result->setData($response);
            return $result;
        } else {
            $data = $this->_objectManager->create('Magedelight\Orderbysku\Helper\Productdetail')->csvToArray($file['tmp_name']);
            $productsAvailability = [];
            $outOfStockList = [];
            $qtyList = [];
            $nonSimpleList = [];
            $invalidList = [];
            $productWithOptionsList = [];
            $typeId = [];

            /*Validate product data is empty */
            $dataCount = count($data);
            if ($dataCount == '0') {
                $response['error'] = true;
                $response['message'] = __('Please enter valid product data.');
                $result->setData($response);
                return $result;
            }

            foreach ($data as $skulist) {
                if ($skulist['sku'] && $skulist['qty']) {
                    $productAvailability = $this->_objectManager->create('Magedelight\Orderbysku\Helper\Productdetail')->productIsAvailable($skulist['sku'], $skulist['qty']);
                    if(isset($skulist['product_comment'])){
                        $productAvailability['product_comment'] = $skulist['product_comment'];
                    }

                    if ($productAvailability['outstocklist']) {
                        array_push($outOfStockList, $productAvailability['outstocklist'][0]);
                    }
                    if ($productAvailability['qtylist']) {
                        array_push($qtyList, $productAvailability['qtylist'][0]);
                    }
                    if ($productAvailability['nonsimplelist']) {
                         array_push($nonSimpleList, $productAvailability['nonsimplelist'][0]);
                    }
                    if ($productAvailability['invalidlist']) {
                        array_push($invalidList, $productAvailability['invalidlist'][0]);
                    }

                    if ($productAvailability['productwithOptionslist']) {
                        array_push($productWithOptionsList, $productAvailability['productwithOptionslist'][0]);
                    }

                    if ($productAvailability['typeid']) {
                        array_push($typeId, $productAvailability['typeid'][0]);
                    }
                    /* MAIN DATA */
                    array_push($productsAvailability, $productAvailability);
                }
            }
                
            $response['maindata'] = $productsAvailability;
            $response['outstocklist'] = $outOfStockList;
            $response['qtylist'] = $qtyList;
            $response['typeid'] = $typeId;
            $response['nonsimplelist'] = $nonSimpleList;
            $response['invalidlist'] = $invalidList;
            $response['productwithOptionslist'] = $productWithOptionsList;
            $response['optionurl'] = $this->urlBuilder->getUrl('orderbysku/customer/product');
            $result->setData($response);
            return $result;
        }
    }
}
