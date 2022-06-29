<?php
namespace LeanSwift\Turfcare\Plugin\Checkout;

use Magento\Checkout\Model\Session as CheckoutSession;
use LeanSwift\Turfcare\Helper\Data;
use Magento\Cms\Block\Block as CmsBlock;

class DefaultConfigProvider
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var CmsBlock
     */
    protected $cmsBlock;

    /**
     * TUR-18 Add shipping Information in the result
     *
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        Data $helperData,
        CmsBlock $cmsBlock
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helperData;
        $this->cmsBlock = $cmsBlock;
    }
    public function afterGetConfig(
        \Magento\Checkout\Model\DefaultConfigProvider $subject,
        array $result
    ) {

        /* TUR-18 Add shipping Information */
        $customerDivision = $this->helper->getErpDivision();
        $shipping_information = ($customerDivision)?$this->cmsBlock->setBlockId('shipping-information')->toHtml():'';

        $result['shippingInformation']  = $shipping_information;

        return $result;
    }
}
