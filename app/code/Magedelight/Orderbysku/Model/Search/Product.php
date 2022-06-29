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

namespace Magedelight\Orderbysku\Model\Search;

/**
 * Product model. Return product data used in search autocomplete
 */
class Product
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
        \Magento\Catalog\Model\Product\Visibility $productVisibility
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->objectManager = $objectManager;
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
                ->addAttributeToSelect(['name', 'sku', 'price', 'thumbnail','status','Visibility'])
                ->addAttributeToFilter('sku', ['like' => '%'.$queryText.'%'])
                ->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()])
                ->setVisibility($this->productVisibility->getVisibleInSiteIds());
        
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
        $product = $this->objectManager->create('Magedelight\Orderbysku\Block\Autocomplete\ProductAgregator')
            ->setProduct($product);

        $data = [
            'name'              => $product->getName(),
            'sku'               => $product->getSku(),
            'image'             => $product->getSmallImage(),
            'reviews_rating'    => $product->getReviewsRating(),
            'short_description' => $product->getShortDescription(),
            'description'       => $product->getDescription(),
            'price'             => $product->getPrice(),
            'url'               => $product->getUrl()
        ];

        return $data;
    }
}
