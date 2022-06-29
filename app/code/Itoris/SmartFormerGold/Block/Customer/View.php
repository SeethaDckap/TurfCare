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

class View extends \Magento\Customer\Block\Account\Dashboard
{

    protected $currentCustomer;
    public $form;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        array $data = []
    ) {
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
        $this->_registry = $this->_objectManager->get('Magento\Framework\Registry');
        $directoryList = $this->_objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
        $this->_filesPath = $directoryList->getPath('media').'/sfg/files/';
    }

    public function getSubmission()
    {
        $customerId = $this->currentCustomer->getCustomerId();
        $key = $this->getRequest()->getParam('id');
        if (!$customerId || !$key) return false;
        $this->index = $this->_objectManager->create('Itoris\SmartFormerGold\Model\Customer\Submission')->load($key, 'unique_key');
        if ($customerId != $this->index['customer_id']) return false;
        $this->form = $this->_objectManager->get('Itoris\SmartFormerGold\Model\Form')->load((int)$this->index['form_id']);
        if (!$this->form->getId()) return false;
        $config = $this->form->getConfig();
        $this->_registry->register('sfg_current_form', $this->form);
        $this->submission = $this->_objectManager->get('Itoris\SmartFormerGold\Model\Submission')->load((int)$this->index['submission_id']);
        $aliases = $this->form->getAllAliases();
        $aliases = array_merge($aliases, ['system_form_name' => __('Form'), 'system_created' => __('Created At'), 'system_updated' => __('Updated At')]);
        $_data = array_merge(
            ['system_form_name' => $this->getFormName(),
            'system_created' => $this->index['created'] ? $this->dateFormat($this->index['created']) : 'n/a',
            'system_updated' => $this->index['updated'] ? ($this->index['updated'] != '0000-00-00 00:00:00' ? $this->dateFormat($this->index['updated']) : __('Never')) : 'n/a'],
            $this->submission->getData()
        );
        unset($_data['id']);
        $data = [];
        foreach($_data as $key => $value) {
            $element = $this->form->getElementByDbName($key);
            $text = htmlspecialchars($value);
            if ($element && $element->getAttribute('type') == 'file') {
                if ($value) {
                    $fileName = substr($value, 64);
                    if (file_exists($this->_filesPath.$value)) {
                        $url = $this->getUrl('sfg/form/getFile', ['id' => $this->getRequest()->getParam('id'), 'filekey' => $key]);
                        $text = '<a href="javascript://" onclick="document.location=\''.htmlspecialchars($url).'\'; event.stopPropagation()">'.htmlspecialchars($fileName).'</a>';
                    } else {
                        $text = htmlspecialchars($fileName);
                    }
                }
            }
            if ($element && $element->getTag() == 'canvas') {
                if ($value) {
                    $fileName = substr($value, 64);
                    if (file_exists($this->_filesPath.$value)) {
                        $png = 'data:image/png;base64,'.base64_encode(file_get_contents($this->_filesPath.$value));
                        $text = '<img class="sfg-canvas" src="'.$png.'" alt="image" />';
                    }
                }
            }
            $data[isset($aliases[$key]) ? (string) $aliases[$key] : $key] = $text;
        }
        return $data;
    }

    public function getFormName() {
        return $this->form->getId() ? $this->form->getName() : __('n/a');
    }
    
    public function getEditLink()
    {
        return $this->getUrl('sfg/customer/edit/', ['id' => $this->index['unique_key']]);
    }
    
    public function getPdfLink()
    {
        return $this->getUrl('sfg/customer/pdf/', ['id' => $this->index['unique_key']]);
    }
    
    public function dateFormat($date) {
        return $this->formatDate($date, \IntlDateFormatter::LONG);
    }
    
    public function getBackUrl(){
        return $this->getUrl('sfg/customer/');
    }

}
