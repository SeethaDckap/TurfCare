<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace LeanSwift\Turfcare\CustomerData;

use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use LeanSwift\Turfcare\Helper\Data as HelperData;
use Magento\Checkout\CustomerData\DefaultItem as CoreDefaultItem;
use Magento\Cms\Block\Block as CmsBlock;


/**
 * Default item
 */
class DefaultItem extends CoreDefaultItem
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Msrp\Helper\Data
     */
    protected $msrpHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Catalog\Helper\Product\ConfigurationPool
     */
    protected $configurationPool;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var ItemResolverInterface
     */
    private $itemResolver;

    protected $_helperData;

    /**
     * @var CmsBlock
     */
    protected $cmsBlock;

    /**
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Msrp\Helper\Data $msrpHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Framework\Escaper|null $escaper
     * @param ItemResolverInterface|null $itemResolver
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Msrp\Helper\Data $msrpHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Framework\Escaper $escaper = null,
        ItemResolverInterface $itemResolver = null,
        HelperData $helperData,
        CmsBlock $cmsBlock

    ) {
        parent::__construct($imageHelper,$msrpHelper,$urlBuilder,$configurationPool,$checkoutHelper,$escaper,$itemResolver);
        $this->_helperData = $helperData;
        $this->cmsBlock = $cmsBlock;
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetItemData()
    {

        $imageHelper = $this->imageHelper->init($this->getProductForThumbnail(), 'mini_cart_product_thumbnail');
        $productName = $this->item->getProduct()->getName();
        $cartData = $this->_helperData->getCartItemData($this->item->getProduct());
        /* TUR-18 Add shipping Information */
        $customerDivision = $this->_helperData->getErpDivision();
        $shipping_information = ($customerDivision)?$this->cmsBlock->setBlockId('shipping-information')->toHtml():'';
        /* TUR-18 Add shipping Information */
        return [
            'product_qty' =>  $cartData['qty'],
            'product_erp' => $cartData['sxe_product_number'],
            'backordered_qty' => ($cartData['backorderedqty'])?$cartData['backorderedqty']:'',
            'options' => $this->getOptionList(),
            'qty' => $this->item->getQty() * 1,
            'item_id' => $this->item->getId(),
            'configure_url' => $this->getConfigureUrl(),
            'is_visible_in_site_visibility' => $this->item->getProduct()->isVisibleInSiteVisibility(),
            'product_id' => $this->item->getProduct()->getId(),
            'product_name' => $productName,
            'product_sku' => $this->item->getProduct()->getSku(),
            'product_url' => $this->getProductUrl(),
            'product_has_url' => $this->hasProductUrl(),
            'product_price' => $this->checkoutHelper->formatPrice($this->item->getCalculationPrice()),
            'product_price_value' => $this->item->getCalculationPrice(),
            'shipping_information' => $shipping_information,
            'product_image' => [
                'src' => $imageHelper->getUrl(),
                'alt' => $imageHelper->getLabel(),
                'width' => $imageHelper->getWidth(),
                'height' => $imageHelper->getHeight(),
            ],
            'canApplyMsrp' => $this->msrpHelper->isShowBeforeOrderConfirm($this->item->getProduct())
                && $this->msrpHelper->isMinimalPriceLessMsrp($this->item->getProduct()),
        ];
    }
}
