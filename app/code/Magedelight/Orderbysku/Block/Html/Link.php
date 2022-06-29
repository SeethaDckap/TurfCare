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

namespace Magedelight\Orderbysku\Block\Html;

class Link extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    
    /**
     * @var \Magedelight\Orderbysku\Helper\Data
     */
    protected $helperData;

    /**
     * @param Magento\Framework\View\Element\Template\Context $context
     * @param Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param Magento\Customer\Model\Session $customerSession
     * @param Magedelight\Orderbysku\Helper\Data $helperData
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Customer\Model\Session $customerSession,
        \Magedelight\Orderbysku\Helper\Data $helperData,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->helperData = $helperData;
        parent::__construct($context, $defaultPath, $data);
    }

    /**
     * @return text
     */
    protected function _toHtml()
    {
        $customerGroups = $this->helperData->getCustomerGroups();
        $allowCustomer = false;
        if ($customerGroups) {
            $customerGroupArray = explode(',', $customerGroups);
            if (in_array($this->getGroupId(), $customerGroupArray)) {
                $allowCustomer = true;
            }
        }
        if ($allowCustomer) {
            return parent::_toHtml();
        }
        return null;
    }

    /**
     * @return int
     */
    public function getGroupId()
    {
        if ($this->_customerSession->isLoggedIn()):
            return $this->_customerSession->getCustomer()->getGroupId();
        endif;
    }
}
