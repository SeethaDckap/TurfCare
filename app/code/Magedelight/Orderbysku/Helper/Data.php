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

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->storeManager    = $storeManager;
        $this->request = $request;
        $this->session = $session;
        parent::__construct($context);
    }

    /**
     * @param $field
     * @param null $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue($field, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array|bool|null|int|text
     */
    public function isCustomerLoggedIn()
    {
        return $this->session->getCustomer()->getId();
    }

    /**
     * @param null $storeId
     * @return array|bool|int|text|null
     */
    public function isEnabled($storeId = null)
    {
        return $this->getConfigValue('orderbysku/general/enable', $storeId);
    }

    /**
     * @return string
     */
    public function getCurrentActionPath()
    {
        return $this->request->getFullActionName();
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @param null $storeId
     * @return array|bool|int|text|null
     */
    public function isEnabledForGuest($storeId = null)
    {
        return $this->getConfigValue('orderbysku/general/enable_for_guest_users', $storeId);
    }

    /**
     * @param null $storeId
     * @return array|bool|int|text|null
     */
    public function getPageTitle($storeId = null)
    {
        return $this->getConfigValue('orderbysku/general/page_title', $storeId);
    }

    /**
     * @param null $storeId
     * @return array|bool|int|text|null
     */
    public function getUploadFileNote($storeId = null)
    {
        return $this->getConfigValue('orderbysku/upload_file/file_data_note', $storeId);
    }

    /**
     * @param null $storeId
     * @return array|bool|int|text|null
     */
    public function getCustomerGroups($storeId = null)
    {
        return $this->getConfigValue('orderbysku/general/enable_for_customer_group', $storeId);
    }
}
