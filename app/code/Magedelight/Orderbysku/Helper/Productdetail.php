<?php

/**
 * Magedelight
 * Copyright (C) 2016 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Orderbysku
 * @copyright Copyright (c) 2016 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Orderbysku\Helper;

class Productdetail extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    protected $_stockItemRepository;

    /**
     * @var Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var Magento\Catalog\Helper\Output
     */
    protected $_productHelper;

    protected $moduleManager;

    /**
     * Productdetail constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Pricing\Render\Layout $layout
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param \Magento\Store\Model\StoreManager $storeManager
     * @param \Magento\Catalog\Helper\Output $producthelper
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magedelight\Orderbysku\Block\Product $productBlock
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Pricing\Render\Layout $layout,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Catalog\Helper\Output $producthelper,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magedelight\Orderbysku\Block\Product $productBlock,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Swatches\Helper\Data $swatchesHelper,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_objectManager = $objectManager;
        $this->_stockItemRepository = $stockItemRepository;
        $this->_productFactory = $productFactory;
        $this->_productHelper = $producthelper;
        $this->_filesystem = $filesystem;
        $this->_imageFactory = $imageFactory;
        $this->storeManager = $storeManager;
        $this->layoutFactory = $layoutFactory;
        $this->productBlock = $productBlock;
        $this->registry = $registry;
        $this->moduleManager = $moduleManager;
        $this->swatchesHelper = $swatchesHelper;
        parent::__construct($context);
    }

    /**
     * @param $image
     * @param null $width
     * @param null $height
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function resize($image, $width = null, $height = null)
    {
        if ($image == "no_selection" || is_null($image)) {
            $imageHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Catalog\Helper\Image::class);
            return $imageHelper->getDefaultPlaceholderUrl('small_image');
        } else {
            $absolutePath = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('catalog/product') . $image;
            $imageResized = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('catalog/product/' . $width) . $image;
            $imageResize = $this->_imageFactory->create();
            $imageResize->open($absolutePath);
            $imageResize->constrainOnly(true);
            $imageResize->keepTransparency(true);
            $imageResize->keepFrame(false);
            $imageResize->keepAspectRatio(true);
            $imageResize->resize($width, $height);
            $destination = $imageResized;
            $imageResize->save($destination);
            $resizedURL = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product/' . $width . $image;
            return $resizedURL;
        }
    }

    /**
     * @param $sku
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductBySku($sku)
    {
        $productdetails = [];
        $store = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
        $collection = $this->_productCollectionFactory->create()
                ->addWebsiteFilter($store->getWebsiteId())
                ->addStoreFilter($store->getId())
                ->addAttributeToSelect('*')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('url_key')
                ->addAttributeToFilter('status', 1)
                ->addAttributeToFilter('visibility', ['in' => [2, 3, 4]])
                ->addAttributeToFilter('sku', $sku);

        $priceHelper = $this->_objectManager->create('Magento\Framework\Pricing\Helper\Data');

        foreach ($collection as $product) {
            //if configurable product
            $productdetails['configure'] = false;
            if ($product->getTypeId() == 'configurable' || $product->getTypeId() == 'grouped' || $product->getTypeId() == 'virtual' || $product->getTypeId() == 'bundle' || $product->getTypeId() == 'downloadable') {
                $productdetails['messege'] = __('You need to choose options for your item.');
                $productdetails['configure'] = true;
            }

            $productStock = $this->_stockItemRepository->get($product->getId());
            $productIsInStock = $productStock->getIsInStock();
            $minSaleQty = $productStock->getMinSaleQty();
            $maxSaleQty = $productStock->getMaxSaleQty();

            if ($product->getTypeId() == 'configurable') {
                if ($product->isAvailable()) {
                    $productdetails['is_in_stock'] = __("In stock");
                } else {
                    $productdetails['is_in_stock'] = __("Out of stock");
                    return [];
                }
            } elseif ($product->getTypeId() === 'bundle') {

                $selectionCollection = $product->getTypeInstance(true)
                        ->getSelectionsCollection($product->getTypeInstance(true)->getOptionsIds($product), $product);
                if (empty($selectionCollection->getData())) {
                    $productdetails['is_in_stock'] = __("Out of stock");
                    return [];
                } else {
                    $productdetails['is_in_stock'] = __("In stock");
                }
            } else {
                if ($productIsInStock == true) {
                    $productdetails['is_in_stock'] = __("In stock");
                } else {
                    $productdetails['is_in_stock'] = __("Out of stock");
                }
            }

            $productdetails['options'] = $this->getProductOptions($product);

            if ($customOptions = $this->getCustomOptionsHtml($product)) {
                $productdetails['options']['custom_options'] = $customOptions;
            }

            if ($product->getTypeId() == 'grouped') {
                $finalPrice = '0';
                $usedProds = $product->getTypeInstance(true)->getAssociatedProducts($product);
                foreach ($usedProds as $child) {
                    if ($child->getId() != $product->getId()) {
                        $finalPrice += $child->getFinalPrice();
                    }
                }
                $priceWithCurrency = $priceHelper->currency($finalPrice, true, false);
                $priceWithoutCurrency = $finalPrice;
                $priceHtml = $this->getStartingFromPriceHtml($priceWithCurrency);
            } elseif ($product->getTypeId() == 'bundle') {
                $finalPrice  = $product->getPriceInfo()->getPrice('final_price')->getValue();
                $priceWithoutCurrency = $finalPrice;
                $priceWithCurrency = $priceHelper->currency($finalPrice, true, false);
                $priceHtml = $this->getStartingFromPriceHtml($priceWithCurrency);
            } else {
                $priceWithCurrency = $priceHelper->currency($product->getFinalPrice(), true, false);
                $priceWithoutCurrency = $product->getFinalPrice();
                $priceHtml = $this->getPriceHtml($product);
            }

            $imageHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Catalog\Helper\Image::class);
            $attributes = $this->getAdditionalData($product);
            $productdetails['type'] = $product->getTypeId();
            $productdetails['product_id'] = $product->getId();
            $productdetails['attributes'] = $attributes;
            $productdetails['name'] = $product->getName();
            $productdetails['sku'] = $product->getSku();
            $productdetails['description'] = $product->getDescription();
            $productdetails['shortdescription'] = $product->getShortDescription();
            $productdetails['price'] = $priceWithCurrency;
            $productdetails['price_without_cur'] = $priceWithoutCurrency;
            $productdetails['price_html'] = $priceHtml;
            $productdetails['product_url'] = $store->getBaseUrl() . $product->getUrlKey() . '.html';
            $productdetails['productimage'] = (!($product->getImage() == "no_selection")) ? $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage() : $imageHelper->getDefaultPlaceholderUrl('image');
            $productdetails['thumbnail'] = $this->resize($product->getSmallImage(), 100, 100);
            $productdetails['productMinQty'] = $minSaleQty;
            $productdetails['productMaxQty'] = $maxSaleQty;
            $qtyHtml = "<span>". __('Min: ').$productdetails['productMinQty']."</span>\n
                        <span style='white-space:nowrap'>". __('Max: ').$productdetails['productMaxQty']."</span>";
            $productdetails['productQtyHtml'] = $qtyHtml;
        }
        return $productdetails;
    }

    /**
     * @param $product
     * @return mixed
     */
    public function getPriceJsonConfig($product)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->priceCurrency = $objectManager->create('Magento\Framework\Pricing\PriceCurrencyInterface');
        $this->_localeFormat = $objectManager->create('Magento\Framework\Locale\FormatInterface');
        /* @var $product \Magento\Catalog\Model\Product */

        $tierPrices = [];
        $tierPricesList = $product->getPriceInfo()->getPrice('tier_price')->getTierPriceList();
        foreach ($tierPricesList as $tierPrice) {
            $tierPrices[] = $this->priceCurrency->convert($tierPrice['price']->getValue());
        }
        $config = [
            'productId' => $product->getId(),
            'priceFormat' => $this->_localeFormat->getPriceFormat(),
            'prices' => [
                'oldPrice' => [
                    'amount' => $this->priceCurrency->convert(
                        $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue()
                    ),
                    'adjustments' => [],
                ],
                'basePrice' => [
                    'amount' => $this->priceCurrency->convert(
                        $product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount()
                    ),
                    'adjustments' => [],
                ],
                'finalPrice' => [
                    'amount' => $this->priceCurrency->convert(
                        $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue()
                    ),
                    'adjustments' => [],
                ],
            ],
            'idSuffix' => '_clone',
            'tierPrices' => $tierPrices,
        ];

        $responseObject = new \Magento\Framework\DataObject();
        //$this->_eventManager->dispatch('catalog_product_view_config', ['response_object' => $responseObject]);
        if (is_array($responseObject->getAdditionalOptions())) {
            foreach ($responseObject->getAdditionalOptions() as $option => $value) {
                $config[$option] = $value;
            }
        }

        return $this->_jsonEncoder->encode($config);
    }

    /**
     * @param $product
     * @return string
     */
    public function getPriceHtml($product)
    {
        $_product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($product->getId());
        return $this->productBlock->getProductViewPriceHtml($_product, \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE);
    }

    /**
     * @param $priceWithCurrency
     * @return string
     */
    public function getStartingFromPriceHtml($priceWithCurrency)
    {
        $priceHtml = "<div class='price-box' itemprop='offers' itemscope itemtype='http://schema.org/Offer'>
                        <p class='grouped-price'>
                            <span class='price-label'>". __('Starting at')."</span>
                            <span class='price'>".$priceWithCurrency."</span>
                        </p>
                    </div>";
        return $priceHtml;
    }

    /**
     *
     * @param $product
     * @return array
     */
    public function getProductOptions($product)
    {
        $options = [];
        $_product = $this->_objectManager->get('\Magento\Catalog\Model\Product')->load($product->getId());
        $typeId = $_product->getTypeId();
        switch ($typeId) {
            case 'configurable':
                if ($_product->isAvailable()) {
                    $options['configurable_options'] = $this->getProductOptionsHtml($_product); //$this->_objectManager->get('Magento\ConfigurableProduct\Model\Product\Type\Configurable')->getConfigurableAttributesAsArray($_product);
                }
                break;
            case 'grouped':
                $options['group_options'] = $this->getGroupProductOptionsHtml($_product);
                break;
            case 'bundle':
                $options['bundle_options'] = $this->getBundleOptionsHtml($_product);
                break;
            case 'downloadable':
                $options['downloadable_options'] = $this->getDownloadableOptionsHtml($_product);
                $options['downloadable_samples'] = $this->getDownloadableSampleHtml($_product);
                break;
            case 'giftcard':
                $options['giftcard_options'] = $this->getGiftCardOptionsHtml($_product);
                break;
        }

        return $options;
    }

    /**
     * @param $product
     * @return mixed
     */
    public function getDownloadableSampleHtml($product)
    {
        $_product = $this->getLoadedProduct($product);
        $layout = $this->layoutFactory->create();

        $blockOption = $layout->createBlock('Magento\Downloadable\Block\Catalog\Product\Samples', 'samples')->setProduct($_product)
                ->setTemplate('Magento_Downloadable::catalog/product/samples.phtml');

        return $blockOption->toHtml();
    }

    /**
     * @param $product
     * @return mixed
     */
    public function getDownloadableOptionsHtml($product)
    {
        $_product = $this->getLoadedProduct($product);
        $layout = $this->layoutFactory->create();

        $blockOption = $layout->createBlock('Magento\Downloadable\Block\Catalog\Product\Links', 'type_downloadable_options')->setProduct($_product)
                ->setTemplate('Magedelight_Orderbysku::catalog/product/links.phtml');

        $price_renderer_block = $layout
                ->createBlock(
                    "Magento\Framework\Pricing\Render",
                    "product.price.render.default",
                    [
                            'data' => [
                                'price_render_handle' => 'catalog_product_prices',
                                'use_link_for_as_low_as' => 'true'
                            ]
                        ]
                )
                ->setData('area', 'frontend');

        $blockOption->setChild('product.price.render.default', $price_renderer_block);

        return $blockOption->toHtml();
    }

    /**
     *
     * @param $product
     * @return Object
     */
    public function getLoadedProduct($product)
    {
        return $_product = $this->_objectManager->get('\Magento\Catalog\Model\Product')->load($product->getId());
    }

    /**
     *
     * @param $product
     * @return array
     */
    public function getGroupProductOptionsHtml($product)
    {
        $_product = $this->getLoadedProduct($product);

        $layout = $this->layoutFactory->create();

        $blockOption = $layout->createBlock('Magento\GroupedProduct\Block\Product\View\Type\Grouped', 'product.info.grouped')->setProduct($_product)
                ->setTemplate('Magedelight_Orderbysku::product/view/type/grouped.phtml');

        $price_renderer_block = $layout
                ->createBlock(
                    "Magento\Framework\Pricing\Render",
                    "product.price.render.default",
                    [
                    'data' => [
                        'price_render_handle' => 'catalog_product_prices',
                        'use_link_for_as_low_as' => 'true'
                    ]
                        ]
                )
                ->setData('area', 'frontend');

        $blockOption->setChild('product.price.render.default', $price_renderer_block);

        return $blockOption->toHtml();
    }

    /**
     *
     * @param $product
     * @return array
     */
    public function getBundleOptionsHtml($product)
    {
        $_product = $this->getLoadedProduct($product);

        if ($this->registry->registry('current_product')) {
            $this->registry->unregister('current_product');
        }
        $this->registry->register('current_product', $_product);

        $layout = $this->layoutFactory->create();

        $blockOption = $layout->createBlock('Magento\Bundle\Block\Catalog\Product\View\Type\Bundle', 'type_bundle_options')->setProduct($_product)
                ->setTemplate('Magento_Bundle::catalog/product/view/type/bundle/options.phtml');

        $price_renderer_block = $layout
                ->createBlock(
                    "Magento\Framework\Pricing\Render",
                    "product.price.render.default",
                    [
                    'data' =>
                        [
                            'price_render_handle' => 'catalog_product_prices',
                            'use_link_for_as_low_as' => 'true'
                        ]
                    ]
                )
                ->setData('area', 'frontend');

        $bundle_price_renderer = $layout
                ->createBlock(
                    "Magento\Catalog\Pricing\Render",
                    "product.price.render.bundle.customization",
                    [
                        'data' =>
                            [
                                'price_render' => $price_renderer_block,
                                'price_type_code' => 'configured_price',
                                'zone' => 'item_view'
                            ]
                        ]
                );
                

        $blockOption->setChild('product.price.render.bundle.customization', $bundle_price_renderer);

        $block_links2 = $layout->createBlock('Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Multi', 'multi')->setProduct($_product);
        $blockOption->setChild('multi', $block_links2);

        $block_links3 = $layout->createBlock('Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Radio', 'radio')->setProduct($_product);
        $blockOption->setChild('radio', $block_links3);

        $block_links4 = $layout->createBlock('Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Select', 'select')->setProduct($_product);
        $blockOption->setChild('select', $block_links4);

        $block_links5 = $layout->createBlock('Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Checkbox', 'checkbox')->setProduct($_product);
        $blockOption->setChild('checkbox', $block_links5);
        
        return $blockOption->toHtml();
    }

    /**
     * @param $_product
     * @return string
     */
    public function getProductOptionsHtml($_product)
    {

        if ($this->registry->registry('current_product')) {
            $this->registry->unregister('current_product');
        }
        $this->registry->register('current_product', $_product);

        $store = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
        $layout = $this->layoutFactory->create();
        $blockOption = $layout->createBlock("Magento\Catalog\Block\Product\View\Options");

        $block_links1 = $layout->createBlock('Magento\Catalog\Block\Product\View\Options\Type\DefaultType', 'default')->setTemplate('Magento_Catalog::product/view/options/type/default.phtml');
        $blockOption->setChild('default', $block_links1);

        $block_links2 = $layout->createBlock('Magento\Catalog\Block\Product\View\Options\Type\Text', 'text')->setTemplate('Magento_Catalog::product/view/options/type/text.phtml');
        $blockOption->setChild('text', $block_links2);

        $block_links3 = $layout->createBlock('Magento\Catalog\Block\Product\View\Options\Type\File', 'file')->setTemplate('Magento_Catalog::product/view/options/type/file.phtml');
        $blockOption->setChild('file', $block_links3);

        $block_links4 = $layout->createBlock('Magento\Catalog\Block\Product\View\Options\Type\Select', 'select')->setTemplate('Magento_Catalog::product/view/options/type/select.phtml');
        $blockOption->setChild('select', $block_links4);

        $block_links5 = $layout->createBlock('Magento\Catalog\Block\Product\View\Options\Type\Date', 'date')->setTemplate('Magento_Catalog::product/view/options/type/date.phtml');
        $blockOption->setChild('date', $block_links5);

        $price_renderer_block = $layout
                ->createBlock(
                    "Magento\Framework\Pricing\Render",
                    "product.price.render.default",
                    [
                        'data' => [
                                'price_render_handle' => 'catalog_product_prices',
                                'use_link_for_as_low_as' => 'true'
                            ]
                        ]
                )
                ->setData('area', 'frontend');

        $blockOption->setChild('product.price.render.default', $price_renderer_block);

        $dropdown = '';
        if ($_product->getTypeId() == "configurable") {
            $isSwatchesProductAttribute = $this->swatchesHelper->isProductHasSwatch($_product);
            if ($isSwatchesProductAttribute) {
                $swatchBlock = $layout->createBlock("Magento\Swatches\Block\Product\Renderer\Listing\Configurable")->setTemplate("Magedelight_Orderbysku::product/view/swatchrendere.phtml");
                $dropdown = $swatchBlock->setProduct($_product)->toHtml();
            } else {
                $data = $_product->getTypeInstance()->getConfigurableOptions($_product);
                $eavModel = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Eav\Attribute');
                $attrOptions = [];
                foreach ($data as $key => $attr) {
                    $attrLoaded = $eavModel->load($key);
                    $dropdown .= "<label>" . __($attrLoaded->getStoreLabel()) . "</label>";
                    $dropdown .= "<select class='options' name='[super_attribute][" . $key . "]'>";
                    foreach ($attr as $p) {
                        $collection = $this->_productCollectionFactory->create()
                            ->addWebsiteFilter($store->getWebsiteId())
                            ->addStoreFilter($store->getId())
                            ->addAttributeToFilter('status', 1)
                            ->addAttributeToFilter('sku', $p['sku']);

                        if (count($collection) > 0) {
                            if (!isset($attrOptions[$attrLoaded->getAttributeCode()])) {
                                $dropdown .= "<option data-product-id=".$_product->getId()." name=" . $p['option_title'] . " value='" . $p['value_index'] . "'>" . $p['option_title'] . "</option>";
                            } elseif (!in_array($p['value_index'], $attrOptions[$attrLoaded->getAttributeCode()])) {
                                $dropdown .= "<option data-product-id=".$_product->getId()." name=" . $p['option_title'] . " value='" . $p['value_index'] . "'>" . $p['option_title'] . "</option>";
                            }
                            $attrOptions[$attrLoaded->getAttributeCode()][] = $p['value_index'];
                        }
                    }
                    $dropdown .= "</select>";
                }
            }
        }
        return $dropdown;
    }

    /**
     *
     * @param $product
     * @return array
     */
    public function getCustomOptionsHtml($product)
    {
        $_product = $this->_objectManager->get('\Magento\Catalog\Model\Product')->load($product->getId());

        if ($this->registry->registry('current_product')) {
            $this->registry->unregister('current_product');
        }
        $this->registry->register('current_product', $_product);

        $layout = $this->layoutFactory->create();

        $blockOption = $layout->createBlock("Magento\Catalog\Block\Product\View\Options")->setProduct($_product)
                ->setTemplate('Magedelight_Orderbysku::product/view/options.phtml');

        $block_links1 = $layout->createBlock('Magento\Catalog\Block\Product\View\Options\Type\DefaultType', 'default')->setTemplate('Magento_Catalog::product/view/options/type/default.phtml');
        $blockOption->setChild('default', $block_links1);

        $block_links2 = $layout->createBlock('Magento\Catalog\Block\Product\View\Options\Type\Text', 'text')->setTemplate('Magedelight_Orderbysku::product/view/options/type/text.phtml');
        $blockOption->setChild('text', $block_links2);

        $block_links3 = $layout->createBlock('Magento\Catalog\Block\Product\View\Options\Type\File', 'file')->setTemplate('Magedelight_Orderbysku::product/view/options/type/file.phtml');
        $blockOption->setChild('file', $block_links3);

        $block_links4 = $layout->createBlock('Magento\Catalog\Block\Product\View\Options\Type\Select', 'select')->setTemplate('Magento_Catalog::product/view/options/type/select.phtml');
        $blockOption->setChild('select', $block_links4);

        $block_links5 = $layout->createBlock('Magento\Catalog\Block\Product\View\Options\Type\Date', 'date')->setTemplate('Magedelight_Orderbysku::product/view/options/type/date.phtml');
        $blockOption->setChild('date', $block_links5);

        $price_renderer_block = $layout
            ->createBlock(
                "Magento\Framework\Pricing\Render",
                "product.price.render.default",
                [
                    'data' => [
                        'price_render_handle' => 'catalog_product_prices',
                        'use_link_for_as_low_as' => 'true'
                    ]
                ]
            )->setData('area', 'frontend');

        $blockOption->setChild('product.price.render.default', $price_renderer_block);
        $blockOption->setProduct($_product);

        if (!empty($blockOption->toHtml()) && strlen($blockOption->toHtml()) > 1) {
            return $blockOption->toHtml();
        } else {
            return false;
        }
    }

    /**
     *
     * @param $product
     * @param $excludeAttr
     * @return text
     */
    public function getAdditionalData($product, array $excludeAttr = [])
    {
        $_additional = [];
        $attributes = $product->getAttributes();

        foreach ($attributes as $attribute) {
            if ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
                $value = $attribute->getFrontend()->getValue($product);

                if (!$product->hasData($attribute->getAttributeCode())) {
                    $value = __('N/A');
                } elseif ((string) $value == '') {
                    $value = __('No');
                } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                    $value = $this->priceCurrency->convertAndFormat($value);
                }

                if (is_string($value) && strlen($value)) {
                    $_additional[$attribute->getAttributeCode()] = [
                        'label' => __($attribute->getStoreLabel()),
                        'value' => $value,
                        'code' => $attribute->getAttributeCode(),
                    ];
                }
            }
        }
        $attributehtml = '';
        $attributehtml.= '<div class="additional-attributes-wrapper table-wrapper">
        <table class="data table additional-attributes" id="product-attribute-specs-table">
            <caption class="table-caption">More Information</caption>
            <tbody>';
        foreach ($_additional as $_data) {
            $attributehtml.= '<tr><th class="col label">' . $_data['label'] . '</th><td class="col data">' . $this->_productHelper->productAttribute($product, $_data['value'], $_data['code']) . '</td></tr>';
        }
        $attributehtml.= '</tbody>
        </table>
    </div>';

        $html = '';
        $html.= '<div class="product info detailed">
           <div class="product data items tabscontant">
           <div class="data item title active"aria-labeledby="tab-label-one-title"
                     data-role="collapsible" id="tab-label-one">
                    <a class="data switch"
                       tabindex="-1"
                       data-toggle="switch"
                       href="#one"
                       id="tab-label-one-title">
                        Details
                    </a>
                </div>';
        if ($_additional) {
            $html.= '<div class="data item title"
                     aria-labeledby="tab-label-two-title"
                     data-role="collapsible" id="tab-label-two">
                    <a class="data switch"
                       tabindex="-1"
                       data-toggle="switch"
                       href="#two"
                       id="tab-label-two-title">
                    More Information                    
                    </a>
                </div>';
        }
        $html.= '<div class="data item content" id="one" data-role="content">
                ' . $product->getDescription() . '
            </div>';
        if ($_additional) {
            $html.= '<div class="data item content" id="two" data-role="content">' . $attributehtml . '</div>';
        }
        $html.= '</div>
    </div>';
        return $html;
    }

    /**
     *
     * @param $filename
     * @param $delimiter
     * @return array
     */
    public function csvToArray($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }

        $header = null;
        $data = [];
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (!$header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }
        return $data;
    }

    /**
     *
     * @param $filename
     * @param $delimiter
     * @return array
     */
    public function csvHeaderCheck($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }

        $header = null;
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if ($header === null) {
                    $header = $row;
                    break;
                }
            }
            fclose($handle);
        }

        $headercsvData = $header;

        $csvFormat = [];
        $columns = null;
        $headerData = ['sku', 'qty'];
        foreach ($headercsvData as $column) {
            if (!in_array($column, $headerData)) {
                if ($columns == null) {
                    $columns.= $column;
                } else {
                    $columns.= $delimiter . ' ' . $column;
                }
                $csvFormat['success'] = false;

                if ($columns == "") {
                    $csvFormat['message'] = __("Please upload proper csv file.");
                } else {
                    $csvFormat['message'] = __('Csv file is Wrong columns name with ' . $columns);
                }
            }
        }
        return $csvFormat;
    }

    /**
     * @param $sku
     * @param $qty
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function productIsAvailable($sku, $qty)
    {
        $store = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
        $product = $this->_productCollectionFactory->create()
            ->addWebsiteFilter($store->getWebsiteId())
            ->addStoreFilter($store->getId())
            ->addAttributeToSelect('*')
            ->addAttributeToSelect('image')
            ->addAttributeToSelect('url_key')
            ->addAttributeToFilter('status', 1)
            ->addAttributeToFilter('visibility', ['in' => [2, 3, 4]])
            ->addAttributeToFilter('sku', $sku)
            ->getFirstItem();

        $_product = $this->_productFactory->create();
        $_product->load($product->getIdBySku($sku));

        $outstocklist = [];
        $qtylist = [];
        $nonsimplelist = [];
        $invalidlist = [];
        $productwithOptionslist = [];
        $productskudetails = [];

        if ($_product->getSku() && in_array($_product->getVisibility(), [2, 3, 4])) {
            if ($product->getSku()) {
                $imageHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Catalog\Helper\Image::class);

                $productskudetails['name'] = $product->getName();
                $productskudetails['sku'] = $product->getSku();
                $productskudetails['typeid'] = $product->getTypeId();
                $productskudetails['qty'] = $qty;
                $productskudetails['productUrl'] = $_product->getProductUrl();
                $productskudetails['itemimage'] = (!($product->getImage() == "no_selection")) ? $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage() : $imageHelper->getDefaultPlaceholderUrl('image');

                if ($product->getTypeId() == "simple") {
                    $productStock = $this->_stockItemRepository->get($product->getId());
                    $productAvailableQty = $productStock->getQty();
                    $productIsInStock = $productStock->getIsInStock();
                } else {
                    $productStock = $this->_stockItemRepository->get($product->getId());
                    $productAvailableQty = $productStock->getMaxSaleQty();
                    $productskudetails['availability'] = 1;
                    $productIsInStock = $productStock->getIsInStock();
                }

                if ($product->getHasOptions()) {
                    $productskudetails['options'] = 1;
                    array_push($productwithOptionslist, $sku);
                }

                if ($productAvailableQty > $qty) {
                    if ($product->getStatus() == 1 && $productIsInStock == true) {
                        $productskudetails['massege'] = "<span style='color: green;'>" . __('Item Available.') . "</span>";
                        $productskudetails['availability'] = 1;
                    } else {
                        if ($product->getStatus() == 1) {
                            $productskudetails['massege'] = "<span style='color: red;'>" . __('Product is out of stock.') . "</span>";
                            $productskudetails['availability'] = 0;
                            array_push($outstocklist, $sku);
                        } else {
                            $productskudetails['massege'] = "<span style='color: red;'>" . __('Product is disabled.') . "</span>";
                            $productskudetails['availability'] = 0;
                            array_push($invalidlist, $sku);
                        }
                    }
                } else {
                    $productskudetails['massege'] = "<span style='color: red;'>" . __('Item quantity is not available') . "</span>";
                    $productskudetails['availability'] = 0;
                    array_push($qtylist, $sku);
                }
            } else {
                $productskudetails['name'] = $_product->getName();
                $productskudetails['sku'] = $_product->getSku();
                $productskudetails['typeid'] = $_product->getTypeId();
                $productskudetails['qty'] = $qty;
                $productskudetails['itemimage'] = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $_product->getImage();
                $productskudetails['massege'] = "<span style='color: red;'>" . __('Item quantity is not available') . "</span>";
                $productskudetails['availability'] = 0;

                array_push($qtylist, $sku);
            }

        } else {
            $productskudetails['massege'] = "<span style='color: red;'>" . __('Invalid sku') . "</span>";
            $productskudetails['availability'] = 0;
            array_push($invalidlist, $sku);
        }
        $productskudetails['outstocklist'] = $outstocklist;
        $productskudetails['qtylist'] = $qtylist;
        $productskudetails['nonsimplelist'] = $nonsimplelist;
        $productskudetails['invalidlist'] = $invalidlist;
        $productskudetails['productwithOptionslist'] = $productwithOptionslist;
        $productskudetails['typeid'] = $product->getTypeId();
        return $productskudetails;
    }

    /**
     * @param $product
     * @return string
     */
    public function getGiftCardOptionsHtml($product)
    {
        if (!$this->moduleManager->isOutputEnabled('Magento_GiftCard')) {
            return "";
        }

        $_product = $this->getLoadedProduct($product);
        $layout = $this->layoutFactory->create();
        $blockOption = $layout->createBlock('Magento\GiftCard\Block\Catalog\Product\View\Type\Giftcard')
            ->setProduct($_product)
            ->setTemplate('Magedelight_Orderbysku::product/view/type/giftcard.phtml');
        return $blockOption->toHtml();
    }

    /**
     * @param $product
     * @return array
     */
    public function getAmountSettingsJson($product)
    {
        $result = ['min' => 0, 'max' => 0];
        if ($product->getAllowOpenAmount()) {
            if ($v = $product->getOpenAmountMin()) {
                $result['min'] = $v;
            }
            if ($v = $product->getOpenAmountMax()) {
                $result['max'] = $v;
            }
        }
        return $result;
    }

    /**
     * @param $product
     * @return mixed
     */
    public function getOpenAmountMin($product)
    {
        $result = $this->getAmountSettingsJson($product);
        return $result['min'];
    }

    /**
     * @param $product
     * @return mixed
     */
    public function getOpenAmountMax($product)
    {
        $result = $this->getAmountSettingsJson($product);
        return $result['max'];
    }

    /**
     * @param float $amount
     * @return float
     */
    public function convertCurrency($amount)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->priceCurrency = $objectManager->create('Magento\Framework\Pricing\PriceCurrencyInterface');
        return $this->priceCurrency->convert($amount);
    }

    /**
     * @param float $amount
     * @param bool $includeContainer
     * @return string
     */
    public function convertAndFormatCurrency($amount, $includeContainer = true)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->priceCurrency = $objectManager->create('Magento\Framework\Pricing\PriceCurrencyInterface');
        return $this->priceCurrency->convertAndFormat($amount, $includeContainer);
    }
}
