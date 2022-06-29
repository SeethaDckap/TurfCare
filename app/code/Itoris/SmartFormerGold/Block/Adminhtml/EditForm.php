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
namespace Itoris\SmartFormerGold\Block\Adminhtml;

class EditForm extends \Magento\Framework\View\Element\Template
{
    public function _construct() {
        $this->_blockGroup = 'Itoris_SmartFormerGold';
        $this->_controller = 'adminhtml_forms';
        $this->_headerText = $this->escapeHtml(__('Edit Form'));
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        parent::_construct();
    }
    
    public function checkForJSMin(){
        $directoryList = $this->_objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
        $filesPath = $directoryList->getPath('static');
        $mediaWebBaseUrl = $this->getMediaWebBaseUrl();
        $pos = strpos($mediaWebBaseUrl, '/adminhtml/');
        if ($pos !== false) {
            $filesPath .= substr($mediaWebBaseUrl, $pos);
            if (!file_exists($filesPath.'/.htaccess')) {
                $htaccessFilePath = dirname(dirname(dirname(__FILE__))).'/view/adminhtml/web/.htaccess';
                if (file_exists($htaccessFilePath)) @copy($htaccessFilePath, $filesPath.'/.htaccess');                
            }
        }
    }
    
    public function getMediaWebBaseUrl() {
        return $this->getViewFileUrl('Itoris_SmartFormerGold');
    }
    
    public function getMediaUrl() {
        return $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
    
    public function getFormId() {
        return (int)$this->getRequest()->getParam('id', 0);
    }
    
    public function getSfgVersion() {
        return $this->_objectManager->get('Magento\Framework\Module\ModuleListInterface')->getOne('Itoris_SmartFormerGold')['setup_version'];
    }
    
    public function getCustomerGroups() {
        return $this->_objectManager->get('Magento\Customer\Model\Customer\Source\Group')->toOptionArray();
    }
}