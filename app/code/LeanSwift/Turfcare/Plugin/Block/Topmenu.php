<?php

namespace LeanSwift\Turfcare\Plugin\Block;

use Magento\Catalog\Model\Category;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Tree\Node;
use Magento\Customer\Model\SessionFactory;


/**
 * Plugin for top menu block
 */
class Topmenu extends \WeltPixel\NavigationLinks\Plugin\Block\Topmenu
{
    /**
     * Catalog category
     *
     * @var \Magento\Catalog\Helper\Category
     */
    public $catalogCategory;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\StateDependentCollectionFactory
     */
    public $collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    public $layerResolver;

    public function __construct(
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Catalog\Model\ResourceModel\Category\StateDependentCollectionFactory $categoryCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        SessionFactory $customerSession
    ) {
        $this->_customerSession = $customerSession;
        $this->collectionFactory = $categoryCollectionFactory;
        parent::__construct($catalogCategory, $categoryCollectionFactory,$storeManager,$layerResolver);
    }
       /**
     * Get Category Tree with restriction
     *
     * @param int $storeId
     * @param int $rootId
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getCategoryTree($storeId, $rootId)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addAttributeToSelect(array(
        		'name',
		        'is_active_for_logged_in_users',
		        'weltpixel_category_url',
		        'weltpixel_category_url_newtab',
		        'weltpixel_mm_display_mode',
		        'weltpixel_mm_columns_number',
		        'weltpixel_mm_column_width',
		        'weltpixel_mm_top_block_type',
		        'weltpixel_mm_top_block_cms',
		        'weltpixel_mm_top_block',
		        'weltpixel_mm_right_block',
                'weltpixel_mm_right_block_type',
                'weltpixel_mm_right_block_cms',
                'weltpixel_mm_bottom_block_type',
                'weltpixel_mm_bottom_block_cms',
		        'weltpixel_mm_bottom_block',
                'weltpixel_mm_left_block_type',
                'weltpixel_mm_left_block_cms',
		        'weltpixel_mm_left_block',
		        'weltpixel_mm_mob_hide_allcat',
            )
        );
        $collection->addFieldToFilter('path', ['like' => '1/' . $rootId . '/%']); //load only from store root
        $collection->addAttributeToFilter('include_in_menu', 1);
        $collection->addIsActiveFilter();
        $collection->addNavigationMaxDepthFilter();
        $collection->addUrlRewriteToResult();
        $collection->addOrder('level', Collection::SORT_ORDER_ASC);
        $collection->addOrder('position', Collection::SORT_ORDER_ASC);
        $collection->addOrder('parent_id', Collection::SORT_ORDER_ASC);
        $collection->addOrder('entity_id', Collection::SORT_ORDER_ASC);
        $customerSession = $this->_customerSession->create();
        $loggedInStatus = $customerSession->isLoggedIn();
        if(!$loggedInStatus) {
            $collection->addAttributeToFilter('is_active_for_logged_in_users', array('eq' => 0));
        }
        return $collection;
    }
}
