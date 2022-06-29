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
 
namespace Itoris\SmartFormerGold\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

class Edit extends \Magento\Framework\App\Action\Action {

    protected $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    
    public function execute() {
        $submissionId = trim($this->getRequest()->getParam('id'));        
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        
        if ($submissionId) {
            $form = $this->_objectManager->create('Itoris\SmartFormerGold\Model\Form');
            $index = $this->_objectManager->create('Itoris\SmartFormerGold\Model\Customer\Submission')->load($submissionId, 'unique_key');
            if ($index->getUniqueKey() == $submissionId) {
                //if ((int)$form->getCurrentCustomer()->getId() == (int)$index->getCustomerId()) {
                    $form->load($index->getFormId());
                    if ($form->getId()) {
                        $this->helper()->session($form->getId(), null, null, true);
                        $this->_objectManager->get('Magento\Framework\Registry')->register('sfg_current_form', $form);
                        $submission = $this->_objectManager->get('Itoris\SmartFormerGold\Model\Submission')->load($index->getSubmissionId());
                        if ($submission->getId()) {
                            $form->setRecordId($submission->getId());
                            foreach($submission->getData() as $key => $value) if ($value) {
                                $element = $form->getElementByDbName($key);
                                if ($element && $element->getId()) {
                                    if ($element->getAttribute('type') == 'file' || $element->getTag() == 'canvas') {
                                        $form->setFile($element->getName(), $value);
                                    } else {
                                        $form->setValue($element->getName(), $value);
                                    }
                                }
                            }
                        }
                        $this->helper()->session($form->getId(), 'redirect_action', 'submission_edit');
                        $resultRedirect->setUrl($this->_objectManager->get('Magento\Framework\UrlInterface')->getUrl('sfg/form/index', ['formid' => $form->getId()]));
                        return $resultRedirect;
                    }
                //} else $this->messageManager->addError( __('Please log in to edit the form') );
            } else $this->messageManager->addError( __('Form not found') );
        }
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
    
    public function helper() {
        return \Magento\Framework\App\ObjectManager::getInstance()->get('Itoris\SmartFormerGold\Helper\Data');
    }
}