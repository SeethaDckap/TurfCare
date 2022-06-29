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
 
namespace Itoris\SmartFormerGold\Controller\Adminhtml\Submissions;

class Save extends \Magento\Backend\App\Action {
    
    public function execute() {
        try {
            $formId = (int)$this->getRequest()->getParam('formid');
            $submissionId = (int)$this->getRequest()->getParam('id');
            $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->_registry = $this->_objectManager->get('Magento\Framework\Registry');
            $form = $this->_objectManager->create('Itoris\SmartFormerGold\Model\Form')->load($formId);
            $form->getConfig();
            $this->_registry->register('sfg_current_form', $form);
            $submission = $this->_objectManager->create('Itoris\SmartFormerGold\Model\Submission')->load($submissionId);

            foreach(array_keys($submission->getData()) as $key) {
                $submission->setData($key, $this->getRequest()->getParam($key));
            }
            $submission->save();
            $this->messageManager->addSuccess(__('The submission has been saved'));
        } catch(\Exception $e) {
            $this->messageManager->addError(__('Something went wrong while saving this submission'));
        }
        if ($this->getRequest()->getParam('back')) {
            $this->_redirect('smartformergold/submissions/edit', ['formid' => $formId, 'id' => $submissionId]);
        } else {
            $this->_redirect('smartformergold/submissions/index', ['formid' => $formId]);
        }
    }
}