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
 
namespace Itoris\SmartFormerGold\Controller\Adminhtml\Forms;

class SaveForm extends \Magento\Backend\App\Action {
    
    public function execute() {
        $fid = $this->getRequest()->getParam('fid', 0);
        $content = $this->getRequest()->getParam('content', '');
        $name = $this->getRequest()->getParam('name', '');
        $description = $this->getRequest()->getParam('description', '');
        if ($name) {
            $form = $this->_objectManager->create('Itoris\SmartFormerGold\Model\Form');
            if ($fid) $form->load($fid); else $form->setStatus(1);
            $form->setName($name)->setDescription($description)->setForm($content)->save();
            $this->helper()->_echo($form->getId().' '.'Form has been saved successfully');
        } else $this->helper()->_echo('Form should have name');
    }
    
    protected function _isAllowed() {
        return true;
    }
    
    public function helper() {
        return \Magento\Framework\App\ObjectManager::getInstance()->get('Itoris\SmartFormerGold\Helper\Data');
    }
}