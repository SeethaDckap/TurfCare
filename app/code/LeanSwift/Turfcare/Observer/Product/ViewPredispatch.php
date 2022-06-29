<?php
namespace LeanSwift\Turfcare\Observer\Product;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use LeanSwift\Turfcare\Helper\Data;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Response\Http;
use Magento\Framework\UrlInterface;
use \Magento\Framework\App\Action\Context;

/**
 * Observer for event controller_action_predispatch_catalog_product_view
 */
class ViewPredispatch implements ObserverInterface
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Context
     */
    private $context;
    /**
     * @var Http
     */
    protected $_redirect;

    /**
     * @var UrlInterface
     */
    protected $_urlInterface;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * ViewPredispatch constructor.
     * @param Context $context
     * @param Data $pageHelper
     * @param ProductRepository $productRepository
     * @param Http $redirect
     * @param UrlInterface $urlInterface
     */
    public function __construct(
        Context $context,
        Data $pageHelper,
        ProductRepository $productRepository,
        Http $redirect,
        UrlInterface $urlInterface
    ) {
        $this->context = $context;
        $this->_redirect = $redirect;
        $this->_urlInterface = $urlInterface;
        $this->productRepository = $productRepository;
        $this->helper = $pageHelper;
    }

    /**
     * Redirect 404 if the guest customer trying to access products
     *
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $customerSession = $this->helper->getCustomerSession();
//        if(!$customerSession->isLoggedIn()){
//            $request = $observer->getEvent()->getRequest();
//            $productId = $request->getParam('id');
//            $result = $this->helper->hideCategories();
//            $product = $this->productRepository->getById($productId);
//            if ($categoryIds = $product->getCustomAttribute('category_ids')) {
//                foreach ($categoryIds->getValue() as $categoryId) {
//                      if(!in_array($categoryId,$result)){
//                          $this->_redirect->setRedirect($this->_urlInterface->getUrl("noroute"));
//                      }
//                }
//            }
//        }
        return;
    }
}
