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
namespace Itoris\SmartFormerGold\Block\Adminhtml\Submissions;

class Pdf extends \Magento\Backend\Block\Widget\Container
{
    public function getSubmission()
    {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $directoryList = $this->_objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
        $this->_filesPath = $directoryList->getPath('media').'/sfg/files/';
        $formId = (int)$this->getRequest()->getParam('formid');
        $id = (int)$this->getRequest()->getParam('id');
        if (!$formId && !$id) return false;
        $this->form = $this->_objectManager->create('Itoris\SmartFormerGold\Model\Form')->load($formId);
        if (!$this->form->getId()) return false;
        $this->_objectManager->get('Magento\Framework\Registry')->register('sfg_current_form', $this->form);
        $this->submission = $this->_objectManager->create('Itoris\SmartFormerGold\Model\Submission')->load($id);
        if (!$this->submission->getId()) return false;
        $this->index = $this->_objectManager->create('Itoris\SmartFormerGold\Model\Customer\Submission')
                        ->getCollection()
                        ->addFieldToFilter('form_id', $this->form->getId())
                        ->addFieldToFilter('submission_id', $this->submission->getId())
                        ->getFirstItem();
        
        $config = $this->form->getConfig();
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
                    $text = htmlspecialchars($fileName);
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
    
    public function dateFormat($date) {
        return $this->formatDate($date, \IntlDateFormatter::LONG);
    }
    
    public function getFormName() {
        return $this->form->getId() ? $this->form->getName() : __('n/a');
    }
}