<?php
namespace LeanSwift\Turfcare\Model\Layer\Filter;

use WeltPixel\LayeredNavigation\Helper\Data as LayerHelper;
use WeltPixel\LayeredNavigation\Model\Layer\Filter as LayerFilter;
use LeanSwift\Turfcare\Helper\Data;
use Magento\Catalog\Model\Category as CategoryCollection;

class Category extends \WeltPixel\LayeredNavigation\Model\Layer\Filter\Category
{
	/** @var \WeltPixel\LayeredNavigation\Helper\Data */
	protected $_moduleHelper;

	/** @var bool Is Filterable Flag */
	protected $_isFilter = false;

	/** @var \Magento\Framework\Escaper */
	private $escaper;

	/** @var  \Magento\Catalog\Model\Layer\Filter\DataProvider\Category */
	private $dataProvider;

    /**
     * @var Data
     */
	public $helper;

    /**
     * @var CategoryCollection
     */
	public $categoryCollection;

    /**
     * Category constructor.
     * @param \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory $categoryDataProviderFactory
     * @param LayerHelper $moduleHelper
     * @param LayerFilter $layerFilter
     * @param array $data
     */
	public function __construct(
		\Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Catalog\Model\Layer $layer,
		\Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
		\Magento\Framework\Escaper $escaper,
		\Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory $categoryDataProviderFactory,
		LayerHelper $moduleHelper,
        LayerFilter $layerFilter,
		Data $helper,
		CategoryCollection $categoryCollection,
		array $data = []
	)
	{
		parent::__construct(
			$filterItemFactory,
			$storeManager,
			$layer,
			$itemDataBuilder,
			$escaper,
			$categoryDataProviderFactory,
            $moduleHelper,
			$layerFilter
		);
        $this->escaper       = $escaper;
        $this->_moduleHelper = $moduleHelper;
        $this->_layerFilter  = $layerFilter;
        $this->dataProvider  = $categoryDataProviderFactory->create(['layer' => $this->getLayer()]);
        $this->helper = $helper;
        $this->categoryCollection = $categoryCollection;
	}

    /**
     * @return array
     * @throws \Magento\Framework\Exception\StateException
     */
	protected function _getItemsData()
	{

		if (!$this->_moduleHelper->isEnabled()) {
			return parent::_getItemsData();
		}
		/** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
		$productCollection = $this->getLayer()->getProductCollection();

		if ($this->_isFilter) {
			$productCollection = $productCollection->getCollectionClone()
				->removeAttributeSearch('category_ids');
		}
		$optionsFacetedData = $productCollection->getFacetedData('category');
		$category           = $this->dataProvider->getCategory();
		$categories         = $category->getChildrenCategories();

		$collectionSize = $productCollection->getSize();
		//$allowedProductIds = $this->helper->getDealerItems();

        //Category based restrictions
        $displaySettings = $this->getCustomAttributeValue($category->getId());


        if ($category->getIsActive() && $displaySettings) {
            foreach ($categories as $category) {
                    $categoryId = $category->getId();
                    $allow = true;
//                    $allow = false;
//                    if(count($allowedProductIds) > 0) {
//
//                        //Restriction for dealer category filter
//                        if (count($category->getProductCollection()->getAllIds()) > 0) {
//                            $categoryIds = $category->getProductCollection()->getAllIds();
//                            foreach ($categoryIds as $cat) {
//                                $allow = false;
//                                if (in_array($cat, $allowedProductIds)) {
//                                    $allow = true;
//                                    break;
//                                } else {
//                                    continue;
//                                }
//                            }
//                        }
//                    }else{
//                        $allow = true;
//                    }

                    if($allow) {
                        $childDisplaySettings = $this->getCustomAttributeValue($categoryId);
                        $count = isset($optionsFacetedData[$categoryId]) ? $optionsFacetedData[$categoryId]['count'] : 0;
                        if ($category->getIsActive() && $childDisplaySettings
                            && $this->_layerFilter->isOptionReducesResults($this, $count, $collectionSize)
                        ) {
                            $this->itemDataBuilder->addItemData(
                                $this->escaper->escapeHtml($category->getName()),
                                $categoryId,
                                $count
                            );
                        }
                    }
            }
        }
		return $this->itemDataBuilder->build();
	}

    /**
     * Category layer filter restrictions
     *
     * @param $categoryId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
	public function getCustomAttributeValue($categoryId){
        $customerSession = $this->helper->getCustomerSession();
        if(!$customerSession->isLoggedIn()) {
            $categoryIds = $this->helper->hideCategories();
            if(count($categoryIds) && in_array($categoryId,$categoryIds))
            {
                $status = true;
            }else{
                $status = false;
            }
        }else{
            $status = true;
        }
        return $status;
    }
}
