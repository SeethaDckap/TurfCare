<?php
/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Orderbysku
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Orderbysku\Block;

use Magento\Customer\Model\Session;
use Magento\Customer\Model\GroupManagement as GroupManagement;

class Orderbysku extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magedelight\Orderbysku\Helper\Data
     */
    protected $helperData;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * Orderbysku constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Session $customerSession
     * @param \Magento\Framework\Registry $registry
     * @param \Magedelight\Orderbysku\Helper\Data $helperData
     * @param \Magento\CatalogInventory\Helper\Minsaleqty $minsaleqty
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Session $customerSession,
        \Magento\Framework\Registry $registry,
        \Magedelight\Orderbysku\Helper\Data $helperData,
        \Magento\CatalogInventory\Helper\Minsaleqty $minsaleqty,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->helperData = $helperData;
        $this->minsaleQty = $minsaleqty;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return $this->_customerSession->isLoggedIn();
    }
    
    /**
     * @return object
     */
    public function getHelper()
    {
        return $this->helperData;
    }

    /**
     * get default qty values
     */
    public function getDefaultQtyValue()
    {
        $currentCustomerGroupId = $this->getCurrentCustomerGroupId();
        $maximumQty = intval($this->helperData->getConfigValue('cataloginventory/item_options/max_sale_qty'));
        $minimumQty = $this->minsaleQty->getConfigValue($currentCustomerGroupId, null);
        $responseArray = [];
        $responseArray['max_sale_qty'] = $maximumQty;
        $responseArray['min_sale_qty'] = $minimumQty;
        return $responseArray;
    }

    /**
     * @return int
     */
    public function getCurrentCustomerGroupId()
    {
        if ($this->_customerSession->isLoggedIn()) {
            return $customerGroup=$this->_customerSession->getCustomer()->getGroupId();
        } else {
            return GroupManagement::NOT_LOGGED_IN_ID;
        }
    }
}
