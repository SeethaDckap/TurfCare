<?php
namespace LeanSwift\Turfcare\Block\Product;
use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ProductFactory;
class SupersededInformation extends View
{
    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     * @deprecated 102.0.0
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_productHelper;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    protected $productTypeConfig;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $_localeFormat;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @param Context $context
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Customer\Model\Session $customerSession
     * @param ProductRepositoryInterface|\Magento\Framework\Pricing\PriceCurrencyInterface $productRepository
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array $data
     * @codingStandardsIgnoreStart
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        ProductFactory $productFactory,
        array $data = []
    ){
        $this->productFactory = $productFactory;
        parent::__construct($context, $urlEncoder,$jsonEncoder,$string,$productHelper,$productTypeConfig,$localeFormat,$customerSession,$productRepository,$priceCurrency);
    }

    /**
     * @param $sku
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSupersededItemName($sku)
    {
        $supersededItemName = null;
        $productData = $this->isValidSku($sku);
        if(!empty($productData)){
            $supersededItemName = $productData->getName();
        }
        return $supersededItemName;
    }

    /**
     * @param $sku
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSupersededItemSku($sku)
    {
        $supersededItemSku = null;
        $productData = $this->isValidSku($sku);

        if(!empty($productData)){
            $supersededItemSku = $productData->getSku();
        }
        return $supersededItemSku;
    }

    /**
     * Get Product URL
     *
     * @param \Magento\Catalog\Model\Product $sku
     * @return string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSupersededProductURL($sku)
    {
        $productURL = null;
        $productData = $this->isValidSku($sku);
        if(!empty($productData)){
            $productURL = $productData->getProductUrl();
        }
        return $productURL;
    }

    /**
     * Check valid sku and return product data object
     * It iterates and works like a chain and return final superseded child data
     * @param $sku
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isValidSku($sku)
    {
        $productFactoryObject = $this->productFactory->create();
        $productData = null;
        $lastSuperseded = null;
        if($productFactoryObject->getIdBySku($sku)){
            //Default iteration level is 5. If needed increase into further.
            for($i=0;$i<=5;$i++){
                $productData = $this->productRepository->get($sku);
                if($productData->getStatus() == 1){
                    $lastSuperseded = $productData;
                    $supersededPart = $productData->getSupersedes();
                    if($supersededPart){
                        if($productFactoryObject->getIdBySku($supersededPart)){
                            $sku = $productData->getSupersedes();
                        }
                        $i++;
                    }else{
                        break;
                    }
                }else{
                    $productData = ($lastSuperseded) ? $lastSuperseded : $productData;
                    break;
                }
            }
        }

        return $productData;
    }
}