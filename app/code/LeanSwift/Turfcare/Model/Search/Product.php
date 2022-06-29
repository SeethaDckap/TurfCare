<?php

namespace LeanSwift\Turfcare\Model\Search;
use Magedelight\Orderbysku\Model\Search\Product as CoreProductSearch;
use LeanSwift\EconnectSXE\Model\Catalog\Product\Price;
use LeanSwift\Turfcare\Helper\Data as DataHelper;

class Product extends CoreProductSearch
{
    /**
     * @var \Magento\Search\Helper\Data
     */
    protected $searchHelper;

    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    protected $layerResolver;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Customer Price Object Interface
     *
     * @var PriceInterface
     */
    protected $_customerPriceModel;

    /**
     * Data Helper
     *
     * @var $_dataHelper
     */
    protected $_dataHelper;

    /**
     * Product constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        Price $_customerPriceModel,
        DataHelper $_dataHelper
    ) {
        $this->_customerPriceModel = $_customerPriceModel;
        $this->_dataHelper = $_dataHelper;
        parent::__construct($objectManager,$productCollectionFactory,$productStatus,$productVisibility);
    }

    /**
     * @param $queryText
     * @return mixed
     */
    public function getResponseDataByString($queryText)
    {
        $responseData['code'] = 'suggestion';
        $responseData['data'] = [];

        $productCollection = $this->getProductCollection($queryText);

        foreach ($productCollection as $product) {
            $responseData['data'][] = $this->getProductData($product);
            $responseData['string'] = $queryText;
        }

        $responseData['size'] = $productCollection->getSize();
        
        return $responseData;
    }

    /**
     * Retrive product collection by query text
     *
     * @param  string $queryText
     * @return mixed
     */
    protected function getProductCollection($queryText)
    {
        $productCollection = $this->_productCollectionFactory->create()
                ->addAttributeToSelect(['name', 'sku', 'price', 'thumbnail','status','Visibility','supersedes'])
                ->addAttributeToFilter('sku', ['like' => '%'.$queryText.'%'])
                ->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()])
                ->setVisibility($this->productVisibility->getVisibleInSiteIds());
                //TC-252 Suggestion rendering changes
                 if(!$productCollection->getSize() > 0 ){
                     $productCollection->clear()->getSelect()->reset(\Zend_Db_Select::WHERE);
                     $productCollection->addAttributeToSelect(['name', 'sku', 'price', 'thumbnail','status','Visibility','supersedes'])->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()])
                         ->setVisibility($this->productVisibility->getVisibleInSiteIds())->addAttributeToFilter('supersedes', ['like' => '%'.$queryText.'%']);
                     $productCollection->load();
                 }

        $productCollection->getSelect()->limit(10);

        return $productCollection;
    }

    /**
     * Retrieve all product data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    protected function getProductData($product)
    {
        $productModel = $product;

        $product = $this->objectManager->create('Magedelight\Orderbysku\Block\Autocomplete\ProductAgregator')
            ->setProduct($product);
        $formattedPrice = $this->objectManager->create('\Magento\Framework\Pricing\Helper\Data')
            ->currency($productModel->getPrice());

        //TC-252 Customer price call related changes */\
        $priceHelper = $this->objectManager->create('Magento\Framework\Pricing\Helper\Data');
        $productObject = $this->getLoadedProduct($productModel->getId());
        $store = $this->objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
        $customerObject = $this->_dataHelper->getCustomerSession()->getCustomer();
;
        $customerPrice = $this->_customerPriceModel->getCustomerPrice($productObject,$customerObject,$store->getId());
        $customerPriceWithCurrency = $priceHelper->currency($customerPrice, true, false);
        $supersededValue = ($productModel->getSupersedes())?:'';

        $data = [
            'name'              => $productModel->getName(),
            'sku'               => $productModel->getSku(),
            'image'             => $productModel->getSmallImage(),
            'reviews_rating'    => $productModel->getReviewsRating(),
            'short_description' => $productModel->getShortDescription(),
            'description'       => $productModel->getDescription(),
            'price'             => $formattedPrice,
            'customer_price'    => ($customerPriceWithCurrency)?:'NA',
            'url'               => $productModel->getUrl(),
            'supersedes'        => $supersededValue
        ];

        return $data;
    }

    /**
     *
     * @param $product
     * @return Object
     */
    public function getLoadedProduct($productId)
    {
        return $_product = $this->objectManager->get('\Magento\Catalog\Model\Product')->load($productId);
    }
}
