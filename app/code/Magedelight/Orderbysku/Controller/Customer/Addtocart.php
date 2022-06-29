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

class Addtocart extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected $swatchesHelper;

    /**
     * Addtocart constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Swatches\Helper\Data $swatchesHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Swatches\Helper\Data $swatchesHelper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->cart = $cart;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->swatchesHelper = $swatchesHelper;
        $this->_objectManager = $context->getObjectManager();
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $productsData = $this->getRequest()->getParams();
        $unavailableProduct = [];
        $invalidSku = [];
        $cartValidate = false;
        $storeId = $this->storeManager->getStore()->getId();

        try {
            if ($this->getRequest()->getParam('form') === 'csv') {
                $uploader = $this->_objectManager->create('Magento\MediaStorage\Model\File\Uploader', ['fileId' => 'file']);
                $csvFile = $uploader->validateFile()['tmp_name'];
            }
        } catch (\Exception $e) {
            if ($e->getCode() != '666') {
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            }
        }

        try {
            $totalItems = $productsData['items'];
            $totalItemCount = count($totalItems);
            foreach ($totalItems as $productData) {
                if ($this->_objectManager->create('Magento\Catalog\Model\Product')->getIdBySku(trim($productData['sku']))) {
                    $_product = $this->productRepository->get(trim($productData['sku']), false, null, true)->setData('store_id', $storeId);
                    $productData['product'] = $_product->getId();
                    $productAvailability = $this->_objectManager->create('Magedelight\Orderbysku\Helper\Productdetail')->productIsAvailable(trim($productData['sku']), $productData['qty']);
                    $productType = $_product->getTypeId();
                    if ($productType == "configurable" && $this->swatchesHelper->isProductHasSwatch($_product)) {
                        $productData['super_attribute'] = $this->getRequest()->getParams()['super_attribute'][$_product->getId()];
                    }

                    if ($productAvailability['availability'] == 1) {
                        $productData['form_key'] = $productsData['form_key'];
                        $this->cart->addProduct($_product, $productData);
                        $cartValidate = true;
                    } else {
                        $unavailableProduct[] = $productData['sku'];
                    }
                } else {
                    $invalidSku[] = $productData['sku'];
                }
            }

            $this->cart->save();
            $this->cart->setCartWasUpdated(true);

            if ($cartValidate) {
                $this->messageManager->addSuccess(__('Added to cart successfully.'));
            }

            if ($unavailableProduct) {
                if ($totalItemCount == 1) {
                    $this->messageManager->addError(__('Product not available'));
                } else {
                    $this->messageManager->addError(__('Product not added to cart with SKU - ' . implode(', ', $unavailableProduct) . ''));
                }
            }
            if ($invalidSku) {
                if ($totalItemCount == 1) {
                    $this->messageManager->addError(__('Please enter valid SKU'));
                } else {
                    $this->messageManager->addError(__('Invalid product SKU -' . implode(', ', $invalidSku) . ''));
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addException($e, __('%1', $e->getMessage()));
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went Wrong. Please try again.'));
        }

        if ($cartValidate) {
            $this->_redirect("checkout/cart/index");
        } else {
            $this->_redirect($this->_redirect->getRefererUrl());
        }
    }
}
