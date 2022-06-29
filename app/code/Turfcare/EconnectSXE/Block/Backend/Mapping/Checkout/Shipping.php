<?php
namespace Turfcare\EconnectSXE\Block\Backend\Mapping\Checkout;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\Factory;
use LeanSwift\EconnectSXE\Block\Backend\AbstractField;
use Magento\Shipping\Model\Config\Source\Allmethods as AllMethods;

/**
 * Class Shipping
 * @package Turfcare\EconnectSXE\Block\Backend\Mapping\Checkout
 */
class Shipping extends AbstractField
{
    /**
     * const shipping method code
     */
    const SHIPPING_METHOD = 'shipping_method';

    /**
     * const M3 delivery method
     */
    const M3_DELIVERY_METHOD = 'm3_delivery_method';

    /**
     * @var Factory
     */
    protected $_elementFactory;

    /**
     * @var AllMethods
     */
    protected $_activeCarrier;

    /**
     * Shipping constructor.
     * @param Context $context
     * @param Factory $elementFactory
     * @param AllMethods $shippingMethods
     * @param array $data
     */
    public function __construct(
        Context $context,
        Factory $elementFactory,
        Allmethods $shippingMethods,
        array $data = []
    ) {
        $this->_elementFactory = $elementFactory;
        $this->_activeCarrier = $shippingMethods;
        parent::__construct($context, $data);
    }

    /**
     * Magento active shipping carrier mapping.
     *
     * @param string $columnName
     * @return mixed|string
     * @throws \Exception
     */
    public function renderCellTemplate($columnName)
    {
        $options = null;
        if ($columnName == self::SHIPPING_METHOD && isset($this->_columns[$columnName])) {
            $activeCarriers = $this->_activeCarrier->toOptionArray(true);

            foreach ($activeCarriers as $carrier) {
                if ($carrier['label'] == '') {
                    $options[] = '--No Shipping Method--';
                } else {
                    unset($options[0]);
                    $value = $carrier['value'];
                    foreach ($value as $subValue) {
                        if($subValue['value'] == "flatrate_flatrate") {
                            $options[$subValue['value']] = 'Delivery';
                        }else{
                            $options[$subValue['value']] = 'Customer Pickup';
                        }
                    }
                }
            }
            $element = $this->_elementFactory->create('select');
            $element->setForm(
                $this->getForm()
            )->setName(
                $this->_getCellInputElementName($columnName)
            )->setHtmlId(
                $this->_getCellInputElementId('<%- _id %>', $columnName)
            )->setValues(
                $options
            )->setStyle(self::WIDTH_STYLE_4);

            return str_replace("\n", '', $element->getElementHtml());
        }

        if (empty($this->_columns[$columnName])) {
            throw new \Exception('Wrong column name specified.');
        }

        $column = $this->_columns[$columnName];
        $inputName = $this->_getCellInputElementName($columnName);

        $mappingElement = '<input type="text" style="' . self::WIDTH_STYLE_3 . '" id="' . $this->_getCellInputElementId(
                '<%- _id %>',
                $columnName
            ) .
            '"' .
            ' name="' .
            $inputName .
            '" value="<%- ' .
            $columnName .
            ' %>" ' .
            ($column['size'] ? 'size="' .
                $column['size'] .
                '"' : '') .
            ' class="' .
            (isset(
                $column['class']
            ) ? $column['class'] : 'input-text') . '"' . (isset(
                $column['style']
            ) ? ' style="' . $column['style'] . '"' : '') . '/>';

        return $mappingElement;
    }

    /**
     * Magento constructor for shipping active carrier mapping.
     */
    protected function _prepareToRender()
    {
        $this->addColumn(self::SHIPPING_METHOD, ['label' => __('Shipping Method')]);
        $this->addColumn(self::M3_DELIVERY_METHOD, ['label' => __('M3 Delivery Method')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}