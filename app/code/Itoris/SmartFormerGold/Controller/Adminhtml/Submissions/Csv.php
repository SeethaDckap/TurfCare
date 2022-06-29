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

class Csv extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $formId = (int)$this->getRequest()->getParam('formid');
        $id = (int)$this->getRequest()->getParam('id');

        if ($formId && $id) {
            $form = $this->_objectManager->get('Itoris\SmartFormerGold\Model\Form')->load($formId);
            $config = $form->getConfig();
            $aliases = $form->getAllAliases();

            $res = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $con = $res->getConnection('read');            

            $submission = $con->fetchRow("select * from `{$config->database->name}` where `id` = {$id}");
            
            $output = '"'.__('Field').'","'.__('Value').'"'."\n";
            $output .= '"'.__('Form').'","'.str_replace('"','""', $form->getName()).'"'."\n";

            foreach($submission as $key => $value) {
                $output .= '"'.str_replace('"','""', isset($aliases[$key]) ? $aliases[$key] : $key).'",';
                $element = $form->getElementByDbName($key);
                if ($element && ($element->getAttribute('type') == 'file' || $element->getTag() == 'canvas')) {
                    $output .= '"'.str_replace('"','""', substr($value, 64)).'"'."\n";
                } else {
                    $output .= '"'.str_replace('"','""', $value).'"'."\n";
                }
            }
                
            $this->getResponse()->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                ->setHeader('Content-type', 'application/octet-stream', true)
                ->setHeader('Content-Length', strlen($output), true)
                ->setHeader('Content-Disposition', 'attachment; filename="Form_'.$formId.'_Submission_'.$id.'.csv"', true)
                ->setHeader('Last-Modified', date('r'), true)
                ->sendHeaders();
            $this->getResponse()->setBody($output);
        } else {
            $this->messageManager->addError(__('Invalid Input'));
            $this->_redirect($this->_redirect->getRefererUrl());
        }
    }
}