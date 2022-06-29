<?php

namespace LeanSwift\Turfcare\Observer\Category;

use Magento\Framework\Event\ObserverInterface;
use LeanSwift\Turfcare\Helper\Data;
use Magento\Framework\App\Response\Http;
use Magento\Framework\UrlInterface;

/**
 * observer for event controller_action_predispatch_catalog_category_view
 */
class ViewPredispatch implements ObserverInterface
{

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Http
     */
    protected $_redirect;

    /**
     * @var UrlInterface
     */
    protected $_urlInterface;

    /**
     * ViewPredispatch constructor.
     * @param Data $pageHelper
     * @param Http $redirect
     * @param UrlInterface $urlInterface
     */
    public function __construct(
        Data $pageHelper,
        Http $redirect,
        UrlInterface $urlInterface
    ) {
        $this->_redirect = $redirect;
        $this->_urlInterface = $urlInterface;
        $this->helper = $pageHelper;
    }
    /**
     * Redirect 404 if the guest customer trying to access category
     *
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
//        $customerSession = $this->helper->getCustomerSession();
//        if(!$customerSession->isLoggedIn()) {
//            /** @var \Magento\Framework\App\RequestInterface $request */
//            $request = $observer->getEvent()->getRequest();
//            $categoryId = $request->getParam('id');
//            $result = $this->helper->hideCategories();
//            if(!in_array($categoryId,$result)){
//                $this->_redirect->setRedirect($this->_urlInterface->getUrl("customer/account/login"));
//            }
//            if (!$categoryId) {
//                $this->_redirect->setRedirect($this->_urlInterface->getUrl("customer/account/login"));
//            }
//            return;
//        }
        return;
    }
}
