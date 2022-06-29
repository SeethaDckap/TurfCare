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

class ProductCustomOptionPrice extends \Magento\Framework\App\Action\Action
{
    /**
     * SimpleProductCustomOption constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRepository = $productRepository;
        $this->pricingHelper = $pricingHelper;
    }

    /**
     * Used to retrieve final price for child product.
     */
    public function execute()
    {
        $customOptionProductPrice = $this->getRequest()->getParam('customOptionProductPrice');
        $mainProductId = $this->getRequest()->getParam('mainProductId');
        $result = $this->resultJsonFactory->create();
        try {
            $html = $this->getProductCustomOptionProductPrice($mainProductId, $customOptionProductPrice);
            $result->setData(['success' => true]);
            $result->setData(['html' => $html]);
            return $result;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result->setData(['msg' => $e->getMessage()]);
        } catch (\Exception $e) {
            $result->setData(['msg' => $e->getMessage()]);
        }
        return $result;
    }

    /**
     * @param $mainProductId
     * @param $customOptionsProductPrice
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductCustomOptionProductPrice($mainProductId, $customOptionsProductPrice)
    {
        $product = $this->productRepository->getById($mainProductId);
        $productPrice = $product->getPrice();
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
