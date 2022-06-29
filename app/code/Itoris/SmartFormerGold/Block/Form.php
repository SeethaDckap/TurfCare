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
 
namespace Itoris\SmartFormerGold\Block;

class Form extends \Magento\Framework\View\Element\Template {
    
    protected $_template = 'Itoris_SmartFormerGold::form.phtml';
    
    public $form;
    public $page_html = '';
    
    public function getForm(){
        if (!$this->form) {
            $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();        
            if (!$this->getFormId()) $this->setFormId((int) $this->getRequest()->getParam('formid', 0));        
            $this->form = $this->_objectManager->create('Itoris\SmartFormerGold\Model\Form')->load($this->getFormId());
            $this->form->setBlock($this);
            $post = $this->getRequest()->getPost()->toArray();
            $this->config = $this->form->getConfig();
            if (!isset($post['sfg_submitter'])) {
                if (is_null($this->helper()->session($this->getFormId(), 'redirect_action')) || !$this->config->allow_editing) {
                    $this->helper()->session($this->getFormId(), 'data', []);
                    $this->helper()->session($this->getFormId(), 'files', []);
                    $this->helper()->session($this->getFormId(), 'page', 0);
                    $this->helper()->session($this->getFormId(), 'dbid', 0);
                    $this->form->setRecordId(0);
                }
                $this->helper()->session($this->getFormId(), 'redirect_action', null, true);
            }
        }
        return $this->form;
    }
    
    public function getPageHtml(){
        if ($this->getForm()->getId()) {
            $html = '';
            foreach($this->getForm()->getPageElements() as $element) {
                $html .= $element->getElementHtml() . "\n";
            }
            return $html;
        }
        return __('No form id specified');
    }

    public function getPageWidth() {
        $width = 0;
        if ($this->getForm()->getId()) {
            foreach($this->getForm()->getPageElements() as $element) {
                $_left = (float) $element->getStyle('left');
                $_width = (float) $element->getStyle('width');
                if ($_left + $_width > $width) $width = $_left + $_width;
            }
        }
        return $width;
    }
    
    public function getPageHeight() {
        $height = 0;
        if ($this->getForm()->getId()) {
            foreach($this->getForm()->getPageElements() as $element) {
                $_top = (float) $element->getStyle('top');
                $_height = (float) $element->getStyle('height');
                if ($_top + $_height > $height) $height = $_top + $_height;
            }
        }
        return $height;
    }
    
    public function toHtml() {
        if ($this->getRequest()->getParam('sfg_pdf')) {
            $this->getForm()->setIsPdf(true);
            $this->getPostedValues();
            $html = '<html>
                <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <style>
                  body { font-family: DejaVu Sans, sans-serif; }
                  .sfg-pdf {position:related;}
                  .pdf-hidden {display:none}
                  '.$this->getForm()->getConfig()->globalcss.'
                </style>
                </head>
                <body><div class="sfg-pdf">'.$this->getPageHtml().'</div></body>
                </html>';
            $dompdf = $this->helper()->getDomPdfObject();
            $dompdf->loadHtml($html);
            $submitter = $this->getForm()->getElementById((int) $this->getForm()->submitter);
            $dompdf->setPaper($submitter->getParam('pdf-page-size'), $submitter->getParam('pdf-orientation'));
            $dompdf->render();
            $dompdf->stream();
            $this->helper()->safeExit();
        }        
        
        /* see $this->renderForm() called from eval */
        if (!$this->getForm()->getId() || !$this->getForm()->getStatus()
            || !$this->helper()->isEnabled()) return __("The form is not available yet");
        
        $currentCustomerGroupId = $this->getForm()->getCurrentCustomer()->getId() ? (int) $this->getForm()->getCurrentCustomer()->getGroupId() : 0;
        if (!$this->getForm()->getRecordId() && !in_array(-1, $this->getForm()->getConfig()->ext_access)
            && !in_array($currentCustomerGroupId, $this->getForm()->getConfig()->ext_access)) return __("You have no access to the form");

        if (!$this->getForm()->getRecordId() && $this->getForm()->getCurrentCustomer()->getId() && $this->getForm()->getConfig()->maxsubmissions) {
            $res = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $con = $res->getConnection('read');
            $count = (int) $con->fetchOne("select count(*) from {$res->getTableName('itoris_sfg_submission_index')} where `form_id`={$this->getForm()->getId()} and `customer_id`={$this->getForm()->getCurrentCustomer()->getId()}");
            if ($count >= (int) $this->getForm()->getConfig()->maxsubmissions) return __("You have exceeded the maximum number of submissions for this form");
        }
        
        if ($this->getForm()->getId()) {
            ob_start();
            //eval($this->getForm()->getConfig()->globalphp);
            $evalFunc = @create_function('$php, $__this','eval(str_replace(\'$this\',\'$__this\',$php));');
            $evalFunc($this->getForm()->getConfig()->globalphp, $this);
            $html = ob_get_clean();
            if ((int)$this->getRequest()->getParam('isAjax')) {
                $this->helper()->_echo($html);
                $this->helper()->safeExit();
            } else {
                return $html;
            }
        }
    }
    
    public function renderForm(){
        $html = $this->getForm()->replaceVariables($this->form->getConfig()->globalhtml);
        $this->page_html = str_replace('<%form_html%>', parent::toHtml(), $html);
        return $this->page_html;        
    }
    
    public function setPage($number) {
        $this->getForm()->setPage($number);
        return $this;
    }
    
    public function getPostedValues(){
        $directoryList = $this->_objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
        $filesPath = $directoryList->getPath('media').'/sfg/files/';
        $post = $this->getRequest()->getPost()->toArray();
        if (isset($post['sfg_submitter'])) {
            $submitter = $this->getForm()->getElementById((int) $post['sfg_submitter']);
            $this->getForm()->submitter = $submitter->getConfig()->page == $this->getForm()->getPage() ? (int) $post['sfg_submitter'] : null;
        } else $this->getForm()->submitter = null;
        $values = $this->getForm()->getValues();
        $files = $this->getForm()->getFiles();
        if (!is_null($this->getForm()->submitter)) {
            foreach($this->getForm()->getPageElements() as $element) {
                //making sure unchecked checkboxes and radios are removed
                unset($values[$element->getName()]);
                if ($element->getAttribute('type') == 'file') {
                    if (!file_exists($filesPath)) mkdir($filesPath, 0644, true);
                    //pick up files
                    $file = $this->getRequest()->getFiles()->get($element->getName());
                    if (isset($post[$element->getName()]) || $file && !$file['error']) {
                        if (isset($files[$element->getName()]) && file_exists($filesPath.$files[$element->getName()]) && !$this->getForm()->getRecordId()) {
                            unlink($filesPath.$files[$element->getName()]);
                        }
                        unset($files[$element->getName()]);
                    }
                    if ($file && !$file['error']) {
                        $randomString = bin2hex(openssl_random_pseudo_bytes(32));
                        $fileName = $randomString.$file['name'];
                        if (move_uploaded_file($file['tmp_name'], $filesPath.$fileName)) {
                            $files[$element->getName()] = $fileName;
                        }
                    }
                    unset($post[$element->getName()]);
                }
                if ($element->getTag() == 'canvas' && isset($post[$element->getName()])) {
                    if (!file_exists($filesPath)) mkdir($filesPath, 0644, true);
                    $img = $post[$element->getName()];
                    $img = str_replace('data:image/png;base64,', '', $img);
                    $img = str_replace(' ', '+', $img);
                    $fileData = base64_decode($img);
                    if (!isset($files[$element->getName()])) {
                        $randomString = bin2hex(openssl_random_pseudo_bytes(32));
                        $fileName = $randomString.'image.png';
                    } else {
                        $fileName = $files[$element->getName()];
                    }
                    file_put_contents($filesPath.$fileName, $fileData);
                    $files[$element->getName()] = $fileName;
                    unset($post[$element->getName()]);
                }
            }
        }

        $this->getForm()->setValues(array_merge($values, $post));
        $this->getForm()->setFiles($files);
        return $this->getForm()->getValues();
    }
    
    public function validate() {
        return $this->getForm()->validatePost();
    }
    
    public function dispatch() {
        return $this->getForm()->dispatch();
    }
    
    public function email($templateId, $recipient, $files = []) {
        return $this->getForm()->email($templateId, $recipient, $files);
    }   
    
    public function getErrorMessages(){
        $html = '';
        $messages = $this->getForm()->messages;
        foreach($this->getForm()->validationErrors as $error) $messages['fail'][] = $error;
        foreach($messages as $severity => $_messages) {
            foreach($_messages as $message) {
                $html .= '<div class="sfg-'.$severity.'">'.htmlspecialchars($message).'</div>';
            }
        }
        return $html;
    }
    
    public function redirect($url) {
        $this->setRedirect($url);
        return $this;
    }
    
    public function getCanvasDataArray() {
        $directoryList = $this->_objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
        $filesPath = $directoryList->getPath('media').'/sfg/files/';
        $canvasData = [];
        $files = $this->getForm()->getFiles();
        foreach($this->getForm()->getElementsByTagName('canvas') as $element) {
            if (isset($files[$element->getName()]) && file_exists($filesPath.$files[$element->getName()])) {
                $canvasData[$element->getName()] = 'data:image/png;base64,'.base64_encode(file_get_contents($filesPath.$files[$element->getName()]));
            }
        }
        return $canvasData;
    }
    
    public function helper() {
        return \Magento\Framework\App\ObjectManager::getInstance()->get('Itoris\SmartFormerGold\Helper\Data');
    }
    
}
