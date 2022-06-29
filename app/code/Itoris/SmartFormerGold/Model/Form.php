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
namespace Itoris\SmartFormerGold\Model;

class Form extends \Magento\Framework\Model\AbstractModel
{
    private $config;
    private $allElements = [];
    public $submitter = null;
    public $arrayValues = [];
    public $validators = [];
    public $validationErrors = [];
    public $messages = ['fail' => [], 'warn' => [], 'info' => []];
    
    protected function _construct() {
        $this->_init('Itoris\SmartFormerGold\Model\ResourceModel\Form');
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }
    
    public function getConfig() {
        if (!$this->config) {
            if (!(int)$this->getFormId()) return (object) [];
            $this->config = (object) json_decode($this->getForm());
            $this->prepareValidators();
        }
        if (is_null($this->helper()->session($this->getId(), 'data'))) $this->helper()->session($this->getId(), 'data', []);
        if (is_null($this->helper()->session($this->getId(), 'files'))) $this->helper()->session($this->getId(), 'files', []);
        if (is_null($this->helper()->session($this->getId(), 'page'))) $this->helper()->session($this->getId(), 'page', 0);
        if (is_null($this->helper()->session($this->getId(), 'dbid'))) $this->helper()->session($this->getId(), 'dbid', 0);
        $this->setRecordId((int) $this->helper()->session($this->getId(), 'dbid'));
        $this->loadValuesFromSession();
        return $this->config;
    }
    
    public function getAllElements($reload = false) {
        if (!empty($this->allElements) && !$reload) return $this->allElements;
        foreach($this->config->elements as $key => $element) {
            $this->allElements[$key] = $this->_objectManager->create('Itoris\SmartFormerGold\Model\Element')
                                    ->setId($key)
                                    ->setForm($this)
                                    ->setConfig($element);
        }
        return $this->allElements;
    }   
    
    public function getPageElements() {
        $elements = [];
        foreach($this->config->elements as $key => $element) {
            if ($element->page == $this->getPage()) {
                $elements[$key] = $this->_objectManager->create('Itoris\SmartFormerGold\Model\Element')
                                        ->setId($key)
                                        ->setForm($this)
                                        ->setConfig($element);
            }
        }
        return $elements;
    }
    
    public function getElementById($id) {
        if (!$this->config->elements[$id]) return null;
        $element = $this->config->elements[$id];
        return $this->_objectManager->create('Itoris\SmartFormerGold\Model\Element')
                                        ->setId($id)
                                        ->setForm($this)
                                        ->setConfig($element);
    }
    
    public function getElementsByName($name) {
        $name = str_replace(['[',']',' '], '', $name);
        $elements = [];
        foreach($this->getAllElements() as $element) {
            if ($element->getName() == $name) $elements[] = $element;           
        }
        return $elements;
    }
    
    public function getElementsByTagName($tag) {
        $elements = [];
        foreach($this->getAllElements() as $element) {
            if ($element->getTag() == $tag) $elements[] = $element;           
        }
        return $elements;
    }    
    
    public function getElementByDbName($name) {
        if ($this->getConfig()->database) {
            foreach((array) $this->getConfig()->database->map as $pair) {
                if ($name == $pair[0]) {
                    $elements = $this->getElementsByName($pair[1]);
                    return count($elements) ? $elements[0] : null;
                }
            }
        }
        return null;
    }
    
    private function loadValuesFromSession(){
        $values = is_null($this->helper()->session($this->getId(), 'data')) ? [] : (array) $this->helper()->session($this->getId(), 'data');
        $files = is_null($this->helper()->session($this->getId(), 'files')) ? [] : (array) $this->helper()->session($this->getId(), 'files');
        $this->setValues($values);
        $this->setFiles($files);
        return $this;
    }
    
    public function setValues($values){
        parent::setValues($values);
        $this->helper()->session($this->getId(), 'data', (array) $this->getValues());
        return $this;
    }
    
    public function setValue($name, $value) {
        $values = (array) $this->getValues();
        $values[$name] = $value;
        return $this->setValues($values);
    }
    
    public function getValue($name) {
        $values = (array) $this->getValues();
        return isset($values[$name]) ? $values[$name] : null;
    }
    
    public function setFiles($values){
        parent::setFiles($values);
        $this->helper()->session($this->getId(), 'files', (array) $this->getFiles());
        return $this;
    }
    
    public function setFile($name, $file) {
        $files = (array) $this->getFiles();
        $files[$name] = $file;
        return $this->setFiles($files);
    }
    
    public function getFile($name) {
        $files = (array) $this->getFiles();
        return isset($files[$name]) ? $files[$name] : null;
    }
    
    public function setPage($number) {
        $this->helper()->session($this->getId(), 'page', (int) $number);
        return $this;
    }
    
    public function getPage() {
        return (int) $this->helper()->session($this->getId(), 'page');
    }
    
    private function prepareValidators() {
        $validators = $this->getConfig()->validators;
        foreach($validators as $validator) $this->validators[$validator->alias] = $validator->php;
        return $this;
    }
    
    public function getAllAliases() {
        $aliases = [];
        foreach($this->getAllElements() as $element) {
            if ($element->getName()) {
                $aliases[$element->getName()] = $element->getAlias();
            }
        }
        return $aliases;
    }
    
    public function validatePost() {
        $this->validationErrors = [];
        if (!is_null($this->submitter)) {
            $submitter = $this->getElementById($this->submitter);
            if (is_null($submitter) || (int)$submitter->getParam('disable-validation')) return $this->validationErrors;
            foreach($this->getPageElements() as $element) {
                foreach($element->validate() as $error) $this->validationErrors[] = $error;            
            }
        }
        return $this->validationErrors;
    }
    
    public function dispatch() {
        if (is_null($this->submitter) || !empty($this->validationErrors)) return $this;
        $submitter = $this->getElementById($this->submitter);
        if (is_null($submitter)) return $this;
        switch($submitter->getParam('after-submit')) {
            case 0:
                //next page
                $this->setPage($this->getPage() + 1);
                break;
            case 1:
                //previous page
                if ($this->getPage() > 0) $this->setPage($this->getPage() - 1);
                break;
            case 2:
                //selected page
                $this->setPage((int) $submitter->getParam('selected-page'));
                break;
            case 3:
                //stay on current page
                break;
            case 4:
                //redirect
                $this->getBlock()->redirect($submitter->getParam('redirect-url'));
                break;
        }
        if ((int) $submitter->getParam('save-data')) $this->saveRecord();
        if ((int) $submitter->getParam('email-to-admin')) {            
            $this->email((int) $submitter->getParam('admin-email-template'), $submitter->getParam('admin-email'), $this->getFiles());
        }
        $values = $this->getValues();
        if ((int) $submitter->getParam('email-to-user') && isset($values[$submitter->getParam('user-email-addr-field')])) {
            $this->email((int) $submitter->getParam('user-email-template'), $values[$submitter->getParam('user-email-addr-field')], $this->getFiles());
        }
    }
    
    public function email($templateId, $recipient, $files = []) {
        $directoryList = $this->_objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
        $filesPath = $directoryList->getPath('media').'/sfg/files/';
        $emailTemplate = $this->getEmailTemplate($templateId);
        $emails = $this->getRecipientArray($recipient);
        if (is_null($emailTemplate) || empty($emails)) return false;
        $ccs = !empty($emailTemplate->cc) ? $this->getRecipientArray($emailTemplate->cc) : [];
        $bccs = !empty($emailTemplate->bcc) ? $this->getRecipientArray($emailTemplate->bcc) : [];
        foreach($emails as $email) {
            $_body = '<html><head><title>'.$emailTemplate->subject.'</title></head><body>'.$emailTemplate->body.'</body></html>';            
            $template = $this->_objectManager->create('Magento\Email\Model\Template');
            $template->setData('template_text', $_body);
            $template->setData('template_subject', $emailTemplate->subject);
            $body = $template->getProcessedTemplate([]);
            $subject = $template->getProcessedTemplateSubject([]);
            
            $message = $this->_objectManager->create('Magento\Framework\Mail\Message');
            if (!method_exists($message, 'createAttachment')) $message = $this->_objectManager->create('Itoris\SmartFormerGold\Model\MailMessage');
            foreach($files as $file) {
                if (!file_exists($filesPath.$file)) continue;
                $message->createAttachment(
                    file_get_contents($filesPath.$file),
                    \Zend_Mime::TYPE_OCTETSTREAM,
                    \Zend_Mime::DISPOSITION_ATTACHMENT,
                    \Zend_Mime::ENCODING_BASE64,
                    basename(substr($file, 64))
                );
            }
            $message->setMessageType((int)$emailTemplate->format ? 'text/html' : 'text/plain')
                ->setBody($this->replaceVariables($body))
                ->setSubject($this->replaceVariables($subject))
                ->setFrom($this->replaceVariables($emailTemplate->fromEmail), $this->replaceVariables($emailTemplate->fromName))
                ->addTo($this->replaceVariables($email))
                ->setReplyTo($this->replaceVariables($emailTemplate->fromEmail));
            foreach($ccs as $cc) $message->addCc($cc);
            foreach($bccs as $bcc) $message->addBcc($bcc);
            $mailTransportFactory = $this->_objectManager->create('Magento\Framework\Mail\TransportInterfaceFactory');
            $mailTransport = $mailTransportFactory->create(['message' => clone $message]);
            $mailTransport->sendMessage();
        }
    }
    
    public function getEmailTemplate($id) {
        foreach($this->config->email_templates as $emailTemplate) {
            if ($emailTemplate->id == $id) return $emailTemplate;
        }
        return null;
    }
    
    public function getRecipientArray($emails) {
        $_emails = [];
        foreach(explode(';', str_replace(',', ';', $emails)) as $email) {
            $email = trim($email);
            if ($email) $_emails[] = $email;
        }
        return $_emails;
    }
    
    public function replaceVariables($text) {
        foreach($this->getValues() as $key => $value) {
            $text = str_ireplace('{{'.$key.'}}', is_array($value) ? htmlspecialchars(implode(', ', $value)) : str_replace("\n", '<br />', htmlspecialchars($value)), $text);
            $text = str_ireplace('{{%'.$key.'%}}', is_array($value) ? implode(', ', $value) : str_replace("\n", '<br />', $value), $text);
        }
        foreach($this->getFiles() as $key => $value) {
            $text = str_ireplace('{{'.$key.'}}', htmlspecialchars(substr($value, 64)), $text);
        }
        $all_vars = '';
        foreach($this->getValues() as $key => $value) {
            if (empty($value)) continue;
            $element = $this->getElementsByName($key);
            if (!isset($element[0])) continue;
            $all_vars .= '<tr><td valign="top" align="left"><b>'.$element[0]->getAlias().'</b></td><td valign="top" align="left">'.(is_array($value) ? htmlspecialchars(implode(', ', $value)) : str_replace("\n", '<br />', htmlspecialchars($value))).'</td></tr>';
        }
        foreach($this->getFiles() as $key => $value) {
            if (empty($value)) continue;
            $element = $this->getElementsByName($key);
            if (!isset($element[0])) continue;
            $all_vars .= '<tr><td valign="top" align="left"><b>'.$element[0]->getAlias().'</b></td><td valign="top" align="left">'.htmlspecialchars(substr($value, 64)).'</td></tr>';
        }

        $text = str_ireplace('{{%all_data%}}', '<table cellpadding="0" cellspacing="10">'.$all_vars.'</table>', $text);
        
        //removing uknown vars having no spaces
        $text = preg_replace('/{{[^}|^\ ]*}}/', '', $text);
        
        return $text;
    }
    
    public function saveRecord(){
        $res = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $con = $res->getConnection('write');
        $config = $this->getConfig();
        $hasErrors = false;
        if (isset($config->database->name) && isset($config->database->map)
            && !empty($config->database->name) && !empty($config->database->map)) {
                $keys = [];
                try{
                    $values = array_merge($this->getValues(), $this->getFiles());
                    foreach($config->database->map as $pair) {
                        if ($pair[0] == 'id') continue; else $pair[1] = trim(str_replace('[]', '', $pair[1]));
                        $value = isset($values[$pair[1]]) ? $values[$pair[1]] : '';
                        if (is_array($value)) $value = implode('|', $value);
                        if ($pair[1]) $keys[] = "`{$pair[0]}`=".$con->quote($value);
                    }
                    if ($this->getRecordId()) {
                        $con->query("update `{$config->database->name}` set ".implode(',', $keys)." where `id`=".intval($this->getRecordId()));                    
                    } else {
                        $con->query("insert into `{$config->database->name}` set ".implode(',', $keys));
                        $this->setRecordId((int) $con->fetchOne("select max(`id`) from `{$config->database->name}`"));
                    }
                    //update index
                    $customerId = (int)$this->getCurrentCustomer()->getId();
                    $key = $con->fetchOne("select `unique_key` from `{$res->getTableName('itoris_sfg_submission_index')}` where `form_id`={$this->getFormId()} and `submission_id`={$this->getRecordId()} and `customer_id`={$customerId}");
                    if ($key) {
                        $con->query("update `{$res->getTableName('itoris_sfg_submission_index')}` set `updated`='{$this->getCurrentDateTime()}' where `unique_key`='{$key}'");
                    } else {
                        $key = bin2hex(openssl_random_pseudo_bytes(32));
                        $con->query("insert into `{$res->getTableName('itoris_sfg_submission_index')}` set `form_id`={$this->getFormId()}, `submission_id`={$this->getRecordId()}, `customer_id`={$customerId}, `unique_key`='{$key}', `created`='{$this->getCurrentDateTime()}'");
                    }
                } catch(\Exception $e) {
                    $hasErrors  = true;
                }
        } else $hasErrors  = true;
        
        if ($hasErrors) $this->messages['fail'][] = __('Cannot save the submission. Please contact the site administrator.');
    }
    
    public function setRecordId($id) {
        parent::setRecordId($id);
        $this->helper()->session($this->getId(), 'dbid', $id);
    }
    
    public function getCurrentCustomer(){
        return $this->_objectManager->get('Magento\Customer\Model\Session')->getCustomer();
    }
    
    public function getCurrentDateTime(){
        return $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->gmtDate();
    }
    
    public function helper() {
        return \Magento\Framework\App\ObjectManager::getInstance()->get('Itoris\SmartFormerGold\Helper\Data');
    }
}