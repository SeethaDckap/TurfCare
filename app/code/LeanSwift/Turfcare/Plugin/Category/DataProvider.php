<?php
namespace LeanSwift\Turfcare\Plugin\Category;
use Magento\Catalog\Model\Category\DataProvider as CategoryDataProvider;

class DataProvider extends  CategoryDataProvider
{
    /**
     * Enable use default option for custom attributes
     *
     * @return array
     */
    protected function getFieldsMap()
    {
        $parentFieldMap = parent::getFieldsMap();
        array_push($parentFieldMap['general'],'is_active_for_logged_in_users');
        return $parentFieldMap;
    }
}
