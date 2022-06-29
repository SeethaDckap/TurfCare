<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_SMARTFORMER_GOLD
 * @copyright  Copyright (c) 2017 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */
namespace Itoris\SmartFormerGold\Block\Customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class FormsList extends \Magento\Customer\Block\Account\Dashboard
{

    protected $_collection;

    protected $_collectionFactory;

    protected $currentCustomer;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        \Itoris\SmartFormerGold\Model\ResourceModel\Customer\Submission\CollectionFactory $collectionFactory,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        parent::__construct(
            $context,
            $customerSession,
            $subscriberFactory,
            $customerRepository,
            $customerAccountManagement,
            $data
        );
        $this->currentCustomer = $currentCustomer;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    public function getToolbarHtml()
    {
        return $this->getChildHtml('toolbar');
    }

    protected function _prepareLayout()
    {
        if ($this->getForms()) {
            $toolbar = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'customer_sfg_forms_list.toolbar'
            )->setCollection(
                $this->getForms()
            );

            $this->setChild('toolbar', $toolbar);
        }
        return parent::_prepareLayout();
    }

    public function getForms()
    {
        if (!($customerId = $this->currentCustomer->getCustomerId())) {
            return false;
        }
        if (!$this->_collection) {
            $this->_collection = $this->_collectionFactory->create()->addCustomerFilter($customerId);
        }
        return $this->_collection;
    }

    public function getFormName($formId) {
        $form = $this->_objectManager->get('Itoris\SmartFormerGold\Model\Form')->load((int)$formId);
        return $form->getId() ? $form->getName() : __('n/a');
    }
    
    public function getDetailsLink($key)
    {
        return $this->getUrl('sfg/customer/view/', ['id' => $key]);
    }
    
    public function getEditLink($formId, $key)
    {
        $form = $this->_objectManager->get('Itoris\SmartFormerGold\Model\Form')->load((int)$formId);
        if (!$form->getConfig()->allow_editing) return;
        return $this->getUrl('sfg/customer/edit/', ['id' => $key]);
    }
    
    public function getPdfLink($key)
    {
        return $this->getUrl('sfg/customer/pdf/', ['id' => $key]);
    }
    
    public function dateFormat($date)
    {
        return $this->formatDate($date, \IntlDateFormatter::LONG);
    }

}
