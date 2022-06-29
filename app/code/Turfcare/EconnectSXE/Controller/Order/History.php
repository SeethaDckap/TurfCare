<?php

namespace Turfcare\EconnectSXE\Controller\Order;

use LeanSwift\EconnectSXE\Helper\Data;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class History extends \LeanSwift\EconnectSXE\Controller\Order\History
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var Data
     */
    protected $_data;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helper
    ) {
        parent::__construct($context,$resultPageFactory,$helper);
    }

    /**
     * @return Page
     */
    public function execute()
    {
        $helperData = $this->_data;
        $storeId = $helperData->getStoreId();
        $isOrderHistoryEnabled = $this->_data->isOrderHistoryEnabled();
        $customerErpNumber = $this->_data->getERPCustomerNo();
        if ($isOrderHistoryEnabled && $customerErpNumber) {
            $resultPage = $this->_resultPageFactory->create();
            if ($navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation')) {
                $navigationBlock->setActive('econnectsxe/order/history');
            }
            $resultPage->getConfig()->getTitle()->set(__('Web Orders'));
            return $resultPage;
        } else {
            $this->_redirect('sales/order/history');
        }
    }
}
