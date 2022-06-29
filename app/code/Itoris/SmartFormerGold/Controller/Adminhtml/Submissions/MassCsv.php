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

class MassCsv extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $referer = $this->getRequest()->getServer('HTTP_REFERER');
        $pos = strpos($referer, '/formid/');
        if ($pos !== false) $formId = intval(substr($referer, $pos + 8)); else $formId = 0;
        if ($formId) {
            $form = $this->_objectManager->get('Itoris\SmartFormerGold\Model\Form')->load($formId);
            $this->_objectManager->get('Magento\Framework\Registry')->register('sfg_current_form', $form);
            
            $filter = $this->_objectManager->create('Magento\Ui\Component\MassAction\Filter');
            $collectionFactory = $this->_objectManager->create('Itoris\SmartFormerGold\Model\ResourceModel\Submission\CollectionFactory');

            $collection = $filter->getCollection($collectionFactory->create());
            $itemIds = $collection->getAllIds();
        } else $itemIds = null;

        if (is_array($itemIds)) {
            $config = $form->getConfig();
            $aliases = $form->getAllAliases();

            $res = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $con = $res->getConnection('read');            

            $submissions = $con->fetchAll("select * from `{$config->database->name}` where `id` in (".implode(',',$itemIds).")");
            
            $output = '';

            foreach($submissions as $index => $row) {
                if ($index == 0) {
                    $_output = [];
                    foreach($row as $key => $value) {
                        $_output[] = '"'.str_replace('"','""', isset($aliases[$key]) ? $aliases[$key] : $key).'"';
                    }
                    $output .= implode(',', $_output) . "\n";
                }
                $_output = [];
                foreach($row as $key => $value) {
                    $element = $form->getElementByDbName($key);
                    if ($element && $element->getAttribute('type') == 'file') {
                        $_output[] = '"'.str_replace('"','""', substr($value, 64)).'"';
                    } else {
                        $_output[] = '"'.str_replace('"','""', $value).'"';
                    }
                }
                $output .= implode(',', $_output) . "\n";
            }
            $this->getResponse()->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                ->setHeader('Content-type', 'application/octet-stream', true)
                ->setHeader('Content-Length', strlen($output), true)
                ->setHeader('Content-Disposition', 'attachment; filename="Form_'.$formId.'_Submissions.csv"', true)
                ->setHeader('Last-Modified', date('r'), true)
                ->sendHeaders();
            $this->getResponse()->setBody($output);
        } else {
            $this->messageManager->addError(__('Please select items'));
            $this->_redirect($this->_redirect->getRefererUrl());
        }
    }
}