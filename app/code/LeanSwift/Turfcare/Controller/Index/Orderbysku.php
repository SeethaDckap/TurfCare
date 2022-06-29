<?php

namespace LeanSwift\Turfcare\Controller\Index;

use Magento\Customer\Model\Session;
use Magedelight\Orderbysku\Controller\Index\Index;
use Magento\Framework\Controller\Result\RedirectFactory;

class Orderbysku extends Index
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Magedelight\Orderbysku\Helper\Data
     */
    protected $helperData;

    /**
     * @var Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $_redirectFactory;


    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     * @param \Magedelight\Orderbysku\Helper\Data helperData
     * @param \Magento\Framework\Controller\Result\ForwardFactory resultForwardFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magedelight\Orderbysku\Helper\Data $helperData,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        RedirectFactory $redirectFactory
    ) {
        $this->_redirectFactory = $redirectFactory;
        parent::__construct($context,$customerSession,$resultPageFactory,$helperData,$resultForwardFactory);
    }

    /**
     * Default customer account page
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $isEnabled = $this->helperData->isEnabled();
        $pageTitle = $this->helperData->getPageTitle();
        $storeId = $this->helperData->getStoreId();
        $allowCustomer = false;
        if ($this->session->isLoggedIn()) {
            $customerGroups = $this->helperData->getCustomerGroups();
            $customerGroupId = $this->session->getCustomer()->getGroupId();
            if ($customerGroups) {
                $customerGroupArray = explode(',', $customerGroups);
                if (in_array($customerGroupId, $customerGroupArray)) {
                    $allowCustomer = true;
                }
            }
        } else {
            if ($this->helperData->isEnabledForGuest($storeId)) {
                $allowCustomer = true;
            }
        }

        if ($isEnabled && $allowCustomer) {
            if (!$this->session->isLoggedIn() && !$this->helperData->isEnabledForGuest($storeId)) {
                $this->_redirect("customer/account/login");
            }
            $resultRedirect = $this->resultPageFactory->create();
            $resultRedirect->getConfig()->getTitle()->set($pageTitle);
            $block = $resultRedirect->getLayout()->getBlock('customer.account.link.orderbysku');
            if ($block) {
                $block->setRefererUrl($this->_redirect->getRefererUrl());
            }
        } else {
            /*TC-252 redirect customer to login page */
           $redirect = $this->_redirectFactory->create();
            $resultRedirect = $redirect->setPath('customer/account');
        }

        return $resultRedirect;
    }
}
