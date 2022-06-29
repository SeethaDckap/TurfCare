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

namespace Magedelight\Orderbysku\Controller\Index;

use Magento\Customer\Model\Session;

class Index extends \Magento\Framework\App\Action\Action
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
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->session = $customerSession;
        $this->helperData = $helperData;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
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
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }

        return $resultRedirect;
    }
}
