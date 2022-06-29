<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magedelight\Orderbysku\Block\Product\View\Options\Type\Select;

use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Pricing\Price\CustomOptionPriceInterface;
use Magento\Framework\View\Element\Html\Select;

/**
 * Represent needed logic for dropdown and multi-select
 */
class Multiple extends \Magento\Catalog\Block\Product\View\Options\Type\Select\Multiple
{
    /**
     * @inheritdoc
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _toHtml()
    {
        $option = $this->getOption();
        $optionType = $option->getType();
        $configValue = $this->getProduct()->getPreconfiguredValues()->getData('options/' . $option->getId());
        $require = $option->getIsRequire() ? ' required' : '';
        $extraParams = '';
        /** @var Select $select */
        $select = $this->getLayout()->createBlock(
            Select::class
        )->setData(
            [
                'id' => 'select_' . $option->getId(),
                'class' => $require . ' product-custom-option admin__control-select'
            ]
        );
        $select = $this->insertSelectOption($select, $option);
        $select = $this->processSelectOption($select, $option);
        if ($optionType === Option::OPTION_TYPE_MULTIPLE) {
            $extraParams = ' multiple="multiple"';
        }
        if (!$this->getSkipJsReloadPrice()) {
            $extraParams .= ' onchange="opConfig.reloadPrice()"';
        }
        $extraParams .= ' data-selector="' . $select->getName() . '"';
        $select->setExtraParams($extraParams);
        if ($configValue) {
            $select->setValue($configValue);
        }
        return $select->getHtml();
    }

    /**
     * Returns select with inserted option give as a parameter
     *
     * @param Select $select
     * @param Option $option
     * @return Select
     */
    private function insertSelectOption(Select $select, Option $option): Select
    {
        $require = $option->getIsRequire() ? ' required' : '';
        if ($option->getType() === Option::OPTION_TYPE_DROP_DOWN) {
            $select->setName('options[' . $option->getId() . ']')->addOption('', __('-- Please Select --'));
        } else {
            $select->setName('options[' . $option->getId() . '][]');
            $select->setClass('multiselect admin__control-multiselect' . $require . ' product-custom-option');
        }

        return $select;
    }

    /**
     * Returns select with formatted option prices
     *
     * @param Select $select
     * @param Option $option
     * @return Select
     */
    private function processSelectOption(Select $select, Option $option): Select
    {
        $store = $this->getProduct()->getStore();
        foreach ($option->getValues() as $_value) {
            $isPercentPriceType = $_value->getPriceType() === 'percent';
            $priceStr = $this->_formatPrice(
                [
                    'is_percent' => $isPercentPriceType,
                    'pricing_value' => $_value->getPrice($isPercentPriceType)
                ],
                false
            );
            $select->addOption(
                $_value->getOptionTypeId(),
                $_value->getTitle() . ' ' . strip_tags($priceStr) . '',
                [
                    'price' => $this->pricingHelper->currencyByStore(
                        $_value->getPrice(true),
                        $store,
                        false
                    )
                ]
            );
        }

        return $select;
    }

    /**
     * Return formatted price
     *
     * @param array $value
     * @param bool $flag
     * @return string
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
