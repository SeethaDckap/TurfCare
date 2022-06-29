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

use Magento\Framework\App\Action\Context;

class ConfigureProductPrice extends \Magento\Framework\App\Action\Action
{
    /**
     * ConfigureProductPrice constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable
     * @param \Magedelight\Orderbysku\Block\Product $productBlock
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magedelight\Orderbysku\Block\Product $productBlock,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRepository = $productRepository;
        $this->configurable = $configurable;
        $this->productBlock = $productBlock;
        $this->storeManager = $storeManager;
        $this->imageHelper = $imageHelper;
        $this->pricingHelper = $pricingHelper;
        parent::__construct($context);
    }

    /**
     * Used to retrive final price for child product.
     */
    public function execute()
    {
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product/';
        $imageUrl = $this->imageHelper->getDefaultPlaceholderUrl('thumbnail');

        $params = $this->getRequest()->getParams();
        $result = $this->resultJsonFactory->create();
        try {
            $configId = $this->getRequest()->getParam('configureProductId');
            $superAttribute = $this->getRequest()->getParam('supperAttribute');
            $customOptionsPrice = $this->getRequest()->getParam('customOptionsPrice');
            $childProduct = $this->getChildFromProductAttribute($configId, $superAttribute);
            if ($childProduct->getThumbnail() != "") {
                $imageUrl = $mediaUrl.$childProduct->getThumbnail();
            }
            $imageNameHtml = "<img src='".$imageUrl."'>";
            $finalPrice = $childProduct->getFinalPrice();
            $html = $this->getProductCustomOptionProductPrice($configId, $finalPrice, $customOptionsPrice);
            $result->setData(['success' => true]);
            $result->setData(['html' => $html, 'image' => $imageNameHtml,'final_price' => $finalPrice]);
            return $result;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result->setData(['msg' => $e->getMessage()]);
        } catch (\Exception $e) {
            $result->setData(['msg' => 'We cannot find the category.']);
        }
        return $result;
    }

    /**
     * @param $configId
     * @param $superAttribute
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getChildFromProductAttribute($configId, $superAttribute)
    {
        $_configProduct = $this->productRepository->getById($configId);
        $usedChild = $this->configurable->getProductByAttributes($superAttribute, $_configProduct);
        return $usedChild;
    }


    /**
     * @param $mainProductId
     * @param $productPrice
     * @param $customOptionsProductPrice
     * @return string
     */
    public function getProductCustomOptionProductPrice($mainProductId, $productPrice, $customOptionsProductPrice)
    {
        $finalCalculation = intval($productPrice) + intval($customOptionsProductPrice);
        $formattedPrice = $this->getFormattedPrice($finalCalculation);
        $htmlString = "<div class='price-box price-final_price' data-role='priceBox' data-product-id='$mainProductId'>
                        <span class='price-container price-final_price tax weee' itemprop='offers' itemscope='' itemtype='http://schema.org/Offer'>
                            <span data-price-amount='$formattedPrice' data-price-type='finalPrice' class='price-wrapper '>
                            <span class='price'>$formattedPrice</span></span></span>    
                    </div>";
        return $htmlString;
    }

    /**
     * @param $price
     * @param bool $format
     * @param bool $includeContainer
     * @return float|string
     */
    public function getFormattedPrice($price, $format = true, $includeContainer = true)
    {
        return $this->pricingHelper->currency($price, $format, $includeContainer);
    }
}
