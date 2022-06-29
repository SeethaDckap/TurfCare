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

class HeaderLink extends \Magento\Framework\View\Element\Html\Link
{

    /**
     * @var \Magedelight\Orderbysku\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magedelight\Orderbysku\Helper\Data
     */
    protected $storeManager;

    /**
     * @var Magento\Customer\Model\Session
     */
    protected $sessionFactory;

    /**
     * HeaderLink constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magedelight\Orderbysku\Helper\Data $helperData
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\SessionFactory $sessionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magedelight\Orderbysku\Helper\Data $helperData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->storeManager = $storeManager;
        $this->sessionFactory = $sessionFactory;
        parent::__construct($context, $data);
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
     * @return int
     */
    public function getGroupId()
    {
        if ($this->sessionFactory->create()->isLoggedIn()):
            return $this->sessionFactory->create()->getCustomer()->getGroupId();
        endif;
    }

    /**
     * @return mixed
     */
    public function getHref()
    {
        $storeId=$this->getStoreId();
        if(($this->getPath() !== null) && ($storeId== 3)) {
            $path=$this->getPath();
            if($path == 'partstream') {
                $path=$this->getPath().'?arilangcode=fr';
                $getUrl=rtrim($this->getUrl($path),'/');

                return $getUrl;
            } else {
                $path='#';

                return $this->getUrl($path);
            }
        } else {
            return $this->getUrl($this->getPath());
        }
    }
}
