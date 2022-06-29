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

class CloneForm extends \Magento\Backend\App\Action
{
    public function execute()
    {
        if (!$this->_objectManager->get('Itoris\SmartFormerGold\Helper\Data')->isEnabled()) {
            return $this->_redirect($this->getUrl('smartformergold/forms/index'));
        }
        
        $id = (int) $this->getRequest()->getParam('id');
        
        if ($id) {
            $form = $this->_objectManager->create('Itoris\SmartFormerGold\Model\Form');
            $form->load($id);
            $config = $form->getConfig();
            $config->name = __('Copy of').' '.$form->getName();
            $form->setName($config->name)->setForm(json_encode($config))->setFormId(null)->save();
            
            $this->messageManager->addSuccess(__('Form was cloned'));
        } else {
            $this->messageManager->addError(__('Please select form'));
        }

        $this->_redirect($this->getUrl('*/*/index'));
    }
}