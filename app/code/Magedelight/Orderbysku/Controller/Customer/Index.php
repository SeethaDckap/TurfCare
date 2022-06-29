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

namespace Magedelight\Orderbysku\Controller\Customer;

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
     *
     * @return void
     */
    public function execute()
    {
        $isEnabled = $this->helperData->isEnabled();
        $pageTitle = $this->helperData->getPageTitle();
        $customerGroups = $this->helperData->getCustomerGroups();
        $customerGroupId = $this->session->getCustomer()->getGroupId();
        $allowCustomer = false;
        if ($customerGroups) {
            $customerGroupArray = explode(',', $customerGroups);
            if (in_array($customerGroupId, $customerGroupArray)) {
                $allowCustomer = true;
            }
        }
        if ($isEnabled && $allowCustomer) {
            if (!$this->session->isLoggedIn()) {
                $this->_redirect("customer/account/login");
            }
            $resultRedirect = $this->resultPageFactory->create();
            $resultRedirect->getConfig()->getTitle()->set($pageTitle);
            $block = $resultRedirect->getLayout()->getBlock('customer.account.link.orderbysku');
            if ($block) {
                $resultRedirect->getLayout()->unsetElement('customer-account-navigation-checkout-sku-link');
                $block->setRefererUrl($this->_redirect->getRefererUrl());
            }
        } else {
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }
        return $resultRedirect;
    }
}
