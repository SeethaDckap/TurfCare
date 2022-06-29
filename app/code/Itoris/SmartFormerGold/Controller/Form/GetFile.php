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
 
namespace Itoris\SmartFormerGold\Controller\Form;
use Magento\Framework\Controller\ResultFactory; 

class GetFile extends \Magento\Framework\App\Action\Action {
    
    public function execute() {
        $formid = (int) $this->getRequest()->getParam('formid', 0);
        $object = (int) $this->getRequest()->getParam('object');
        
        $submissionId = trim($this->getRequest()->getParam('id'));
        $fileKey = trim($this->getRequest()->getParam('filekey'));
        
        $form = $this->_objectManager->create('Itoris\SmartFormerGold\Model\Form')->load($formid);
        
        if ($submissionId && $fileKey) {
            $index = $this->_objectManager->create('Itoris\SmartFormerGold\Model\Customer\Submission')->load($submissionId, 'unique_key');
            if ($index->getUniqueKey() == $submissionId && (int)$form->getCurrentCustomer()->getId() == (int)$index->getCustomerId()) {
                $form->load($index->getFormId());
                if ($form->getId()) {
                    $element = $form->getElementByDbName($fileKey);
                    if ($element) $object = $element->getId();
                    $this->_objectManager->get('Magento\Framework\Registry')->register('sfg_current_form', $form);
                    $submission = $this->_objectManager->get('Itoris\SmartFormerGold\Model\Submission')->load($index->getSubmissionId());
                    if ($submission->getId()) {
                        $form->setFiles([$fileKey => $submission->getData($fileKey)]);
                    }
                }
            }
        }
        
        if ($form->getId()) {
            $config = $form->getConfig();
            $element = $form->getElementById($object);
            $files = $form->getFiles();
            if ($element && isset($files[$element->getName()])) {
                $directoryList = $this->_objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
                $filesPath = $directoryList->getPath('media').'/sfg/files/';
                $fileFactory = $this->_objectManager->get('Magento\Framework\App\Response\Http\FileFactory');
                $fullFileName = $files[$element->getName()];
                if ($fullFileName && file_exists($filesPath.$fullFileName)) {                    
                    $fileName = substr($fullFileName, 64);
                    $this->getResponse()->setHttpResponseCode(200)
                        ->setHeader('Pragma', 'public', true)
                        ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                        ->setHeader('Content-type', 'application/octet-stream', true)
                        ->setHeader('Content-Length', filesize($filesPath.$fullFileName), true)
                        ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"', true)
                        ->setHeader('Last-Modified', date('r'), true)
                        ->sendHeaders();
                    $this->getResponse()->setBody(file_get_contents($filesPath.$fullFileName));
                    return;
                }
            }
        }
        
        $this->messageManager->addError( __('File not found') );
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}