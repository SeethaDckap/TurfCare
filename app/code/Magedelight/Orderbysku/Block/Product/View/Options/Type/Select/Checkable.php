<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magedelight\Orderbysku\Block\Product\View\Options\Type\Select;

use Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface;
use Magento\Catalog\Block\Product\View\Options\AbstractOptions;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Pricing\Price\CustomOptionPriceInterface;

/**
 * Represent needed logic for checkbox and radio button option types
 */
class Checkable extends \Magento\Catalog\Block\Product\View\Options\Type\Select\Checkable
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Catalog::product/composite/fieldset/options/view/checkable.phtml';

    /**
     * Returns formated price
     *
     * @param ProductCustomOptionValuesInterface $value
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function formatPrice(ProductCustomOptionValuesInterface $value): string
    {
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        return $this->_formatPrice(
            [
                'is_percent' => $value->getPriceType() === 'percent',
                'pricing_value' => $value->getPrice($value->getPriceType() === 'percent')
            ]
        );
    }

    /**
     * Returns current currency for store
     *
     * @param ProductCustomOptionValuesInterface $value
     * @return float|string
     */
    public function getCurrencyByStore(ProductCustomOptionValuesInterface $value)
    {
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        return $this->pricingHelper->currencyByStore(
            $value->getPrice(true),
            $this->getProduct()->getStore(),
            false
        );
    }

    /**
     * Returns preconfigured value for given option
     *
     * @param Option $option
     * @return string|array|null
     */
    public function getPreconfiguredValue(Option $option)
    {
        return $this->getProduct()->getPreconfiguredValues()->getData('options/' . $option->getId());
    }

    /**
     * @param array $value
     * @param bool $flag
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _formatPrice($value, $flag = true)
    {
        if ($value['pricing_value'] == 0) {
            return '';
        }

        $sign = '+';
        if ($value['pricing_value'] < 0) {
            $sign = '-';
            $value['pricing_value'] = 0 - $value['pricing_value'];
        }

        $priceStr = $sign;

        $customOptionPrice = $this->getProduct()->getPriceInfo()->getPrice('custom_option_price');
        $context = [CustomOptionPriceInterface::CONFIGURATION_OPTION_FLAG => true];
        $optionAmount = $customOptionPrice->getCustomAmount($value['pricing_value'], null, $context);

        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $this->getLayout()->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }
        $priceStr .= $priceRender->renderAmount(
            $optionAmount,
            $customOptionPrice,
            $this->getProduct()
        );

        if ($flag) {
            $priceStr = '<span class="price-notice">' . $priceStr . '</span>';
        }

        return $priceStr;
    }
}
