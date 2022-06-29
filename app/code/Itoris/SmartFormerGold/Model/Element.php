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

class Element extends \Magento\Framework\Model\AbstractModel
{
    
    private $styles = [];
    private $attributes = [];
    private $events = [];
    private $params = [];
    public $validationErrors = [];
    
    public function getElementHtml() {
        $this->setValue();
        if ($this->getForm()->getIsPdf()) $this->preparePDF();
        $html = '<';
        $html .= $this->getTag();
        $html .= $this->getAttributesHtml();
        $html .= $this->getEventsHtml();
        $html .= $this->getStylesHtml();
        if (in_array($this->getTag(), ['input', 'img'])) {
            $html .= '/>';
        } else {
            $html .= '>'.$this->getForm()->replaceVariables($this->getContent() . $this->evalPHP($this->getContentPHP())).'</'.$this->getTag().'>';
        }
        return $html;
    }
    
    public function setConfig($config) {
        parent::setConfig($config);
        $this->setTag($this->getConfig()->tag);
        foreach($config->params as $param) $this->setParam($param[0], $param[1]);
        foreach($config->styles as $style) $this->setStyle($style[0], $style[1], $style[2]);
        if (!$this->getStyle('position')) $this->setStyle('position', 'absolute');
        foreach($config->attributes as $attribute) $this->setAttribute($attribute[0], $attribute[1], $attribute[2]);
        foreach($config->events as $event) $this->setEvent($event[0], $event[1], $event[2]);
        $this->setContent(isset($this->getConfig()->content) ? $this->getConfig()->content : '');
        $this->setContentPHP(isset($this->getConfig()->contentphp) ? $this->getConfig()->contentphp : '');
        if (in_array($this->getTag(), ['input', 'select', 'textarea', 'canvas']) && !$this->getAttribute('name')) {
            $this->setAttribute('name', 'dummy_id_'.$this->getId().($this->getAttribute('multiple') ? '[]' : ''));
        }
        if (!empty($this->getParam('hidden-if')) || !empty($this->getParamPHP('hidden-if'))) {
            $this->setAttribute('sfg-hidden-if', $this->getParam('hidden-if') . $this->evalPHP($this->getParamPHP('hidden-if')));
        }
        $this->setValidators();
        $this->setButtonEvents();
        if ($this->getTag() == 'canvas') $this->setCanvasStyles();
        $this->setAttribute('sfg-element-id', $this->getId());
        $this->setAlias($this->getConfig()->alias ? $this->getConfig()->alias : $this->getAttribute('name') );
        return $this;
    }
    
    public function preparePDF() {
        if ($this->getTag() == 'select') {
             $this->setTag('input');
             $this->setAttribute('type', 'text');
             $this->setContent('');
             $this->setAttribute('value', $this->getPostedValue());
        }
        if ($this->getAttribute('type') == 'file') {
            $this->setAttribute('type', 'text');
            $this->setStyle('display', 'inline');
        }
        if ($this->getTag() == 'input' && !$this->getAttribute('value') && !$this->getStyle('height')) $this->setStyle('height', '21px');
        if ($this->getTag() == 'img') {
            $headers = get_headers($this->getAttribute('src'), 1);
            $type = $headers["Content-Type"];
            $data = "data:{$type};base64,".base64_encode(@file_get_contents($this->getAttribute('src')));
            $this->setAttribute('src', $data);
        }
        if ($this->getTag() == 'canvas') {
            $directoryList = $this->getForm()->_objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
            $filesPath = $directoryList->getPath('media').'/sfg/files/';
            if ($this->getPostedValue() && file_exists($filesPath.$this->getPostedValue())) {
                $this->setTag('img');
                $this->setContent('');
                $data = "data:image/png;base64,".base64_encode(@file_get_contents($filesPath.$this->getPostedValue()));
                $this->setAttribute('src', $data);
            }
        }
    }
    
    public function setContent($content, $values = null) {
        if ($this->getTag() == 'select' && !empty($content)) {
            $options = $this->getOptions($content, $values);
            $content = '';
            foreach($options as $option) {                
                $content .= '<option value="'.htmlspecialchars($option['value']).'"'.($option['selected'] ? ' selected="selected"' : '').'>'.htmlspecialchars($option['text']).'</option>';
            }
        }
        parent::setContent($content);
    }
    
    public function getOptions($content = null, $values = null) {
        $_options = [];
        if ($content === null) $content = $this->getConfig()->content;
        if (empty($content)) return $_options;
        $options = explode("\n", $content);
        foreach($options as $option) {
            $isSelected = strlen($option) > 0 && $option[0] == '*';
            if ($isSelected) $option = substr($option, 1);
            $value = $option;
            if (strpos($option, '|') !== false) list($value, $option) = explode('|', $option, 2);
            if (!is_null($values)) $isSelected = in_array($value, $values);
            $_options[] = ['value' => $value, 'text' => $option, 'selected' => $isSelected];
        }
        return $_options;
    }
    
    public function setValue() {
        $name = $this->getName();
        if (!$name) return;
        $values = $this->getForm()->getValues();
        if (!isset($values[$name]) && $this->getAttribute('multiple') && !is_null($this->getForm()->submitter)) $values[$name] = [];
        if (isset($values[$name])) {
            $_value = $values[$name];
            if (is_array($_value) && !$this->getAttribute('multiple') && !in_array($this->getAttribute('type'), ['checkbox', 'radio'])) {
                if (!isset($this->getForm()->arrayValues[$name])) $this->getForm()->arrayValues[$name] = $_value;
                if (count($this->getForm()->arrayValues[$name])) {
                    $value = array_values($this->getForm()->arrayValues[$name])[0];
                    unset($this->getForm()->arrayValues[$name][array_keys($this->getForm()->arrayValues[$name])[0]]);
                } else {
                    $value = null;
                }
            } else {
                $value = $_value;
            }
            if ($this->getTag() == 'textarea') {
                $this->setContent($value);
                $this->setContentPHP('');
            }
            if ($this->getTag() == 'input') {
                if (in_array($this->getAttribute('type'), ['checkbox', 'radio'])) {
                    if (!$this->getAttribute('value')) $this->setAttribute('value', 'on');
                    if (in_array($this->getAttribute('value'), array_values((array)$value))) $this->setAttribute('checked', 'checked');
                } else {
                    if ($this->getAttribute('type') == 'password') $value = '';
                    $this->setAttribute('value', $value, '');
                }
            }
            if ($this->getTag() == 'select') {
                $this->setContent($this->getConfig()->content, (array) $value);
            }
        }
        $files = $this->getForm()->getFiles();
        if ($this->getAttribute('type') == 'file' && isset($files[$name])) {
            $this->setAttribute('value', substr($files[$name], 64));
            $this->setStyle('display', 'none');
        }
    }
    
    public function getName() {
        return str_replace(['[',']',' '], '', $this->getAttribute('name'));
    }
    
    public function getPostedValue() {
        $name = $this->getName();
        $values = $this->getForm()->getValues();
        $files = $this->getForm()->getFiles();
        if ($name && isset($files[$name])) return $files[$name];
        return ($name && isset($values[$name])) ? $values[$name] : '';
    }
    
    public function setValidators(){
        if ($this->getConfig()->alias) $this->setAttribute('sfg_name', $this->getConfig()->alias);
        if ((int)$this->getParam('required')) $this->setAttribute('sfg_required', '1');
        if ((int)$this->getParam('group-required')) $this->setAttribute('sfg_group_required', '1');
        if ($this->getParam('equal-to')) $this->setAttribute('sfg_equal_to', $this->getParam('equal-to'));
        if ($this->getParam('validation')) $this->setAttribute('validation', $this->getParam('validation'));
    }
    
    public function validate() {
        $this->validationErrors = [];
        $values = $this->getForm()->getValues();
        if ($this->getParam('hidden-if')) {
            $condition = $this->getParam('hidden-if');
            preg_match_all( "/{{(.*)}}/U", $condition, $matches);
            if (is_array($matches)) {
                foreach((array) $matches[1] as $match){
                    if (isset($values[$match])) {
                        $condition = str_ireplace('{{'.$match.'}}', '"'.addslashes($values[$match]).'"', $condition);
                    } else {
                       $condition = str_ireplace('{{'.$match.'}}', '""', $condition); 
                    }
                }
            }
            $result = false;
            try {
                $evalFunc = @create_function('$php, $__this','return eval(str_replace(\'$this\',\'$__this\',$php));');
                $result = $evalFunc('return ('.$condition.') ? true : false;', $this);
                //eval('$result = ('.$condition.') ? true : false;');
            } catch(\Exception $e) { }
            if ($result) return [];
        }
        if ($this->getParam('captcha-field')) {
            $elements = $this->getForm()->getElementsByName($this->getParam('captcha-field'));
            if (!count($elements) || is_null($this->helper()->session($this->getForm()->getId(), 'sec_code')) || $elements[0]->getPostedValue() != $this->helper()->session($this->getForm()->getId(), 'sec_code')) {
                $this->validationErrors[] = __('Incorrect Captcha Code');
            }
        }
        if ((int) $this->getParam('required')) $this->checkForErrors('Required');
        if ((int) $this->getParam('group-required')) $this->checkForErrors('Group Required');
        if ($this->getParam('equal-to')) $this->checkForErrors('Check identical');
        $validator = $this->getParam('validation');
        if ($validator) $this->checkForErrors($validator);
        return $this->validationErrors;
    }
    
    private function checkForErrors($validator) {
        if (!isset($this->getForm()->validators[$validator])) return null;
        $evalFunc = @create_function('$php, $__this','return eval(str_replace(\'$this\',\'$__this\',$php));');
        $error = $evalFunc($this->getForm()->validators[$validator], $this);
        //$error = eval($this->getForm()->validators[$validator]);
        if ($error && !is_null($error)) {
            $this->validationErrors[] = $error;
        }
        return $error;
    }
    
    public function setButtonEvents(){
        switch ((int)$this->getParam('on-click-action')) {
            case 1:
                if ((int)$this->getParam('disable-validation')) {
                    $this->setEvent('onclick', "return sfgObject{$this->getForm()->getId()}.submitSimple(this)");
                } else {
                    $this->setEvent('onclick', "return sfgObject{$this->getForm()->getId()}.submit(this)");
                }
            break;
            case 2:
                $this->setAttribute('sfg-calendar', "1");
                $this->setAttribute('sfg-calendar-field', $this->getParam('date-input-field'));
                $this->setAttribute('sfg-calendar-format', $this->getParam('date-format'));                
                $this->setEvent('onclick', "return sfgObject{$this->getForm()->getId()}.showCalendar(this)");
            break;
            case 3:
                $this->setEvent('onclick', "return sfgObject{$this->getForm()->getId()}.getPdf(this)");
            break;
        }        
    }
    
    public function setCanvasStyles(){
        $this->setAttribute('width', $this->getStyle('width'));
        $this->setAttribute('height', $this->getStyle('height'));
        $this->setAttribute('canvas-pen-size', intval($this->getParam('canvas-pen-size')) ? intval($this->getParam('canvas-pen-size')) : 3);
        $this->setAttribute('canvas-pen-color', $this->getParam('canvas-pen-color') ? $this->getParam('canvas-pen-color') : '#000000');
        $this->setAttribute('canvas-background-color', $this->getParam('canvas-background-color') ? $this->getParam('canvas-background-color') : '#FFFFFF');
    }
    
    public function setStyle($name, $value, $phpValue = '') {
        $this->styles[$name] = ['value' => $value, 'php' => $phpValue];
        return $this;
    }
    
    public function setAttribute($name, $value, $phpValue = '') {
        if ($name == 'name') $value = strtolower($value);
        if ($this->getTag() == 'img' && $name == 'src' && in_array($value, ['{captcha0}', '{captcha1}', '{captcha2}'])) $value = $this->getCaptchaUrl();
        $this->attributes[$name] = ['value' => $value, 'php' => $phpValue];
        return $this;
    }
    
    public function removeAttribute($name) {
        unset($this->attributes[$name]);
        return $this;
    }
    
    public function setEvent($name, $value, $phpValue = '') {
        $this->events[$name] = ['value' => $value, 'php' => $phpValue];
        return $this;
    }
    
    public function setParam($name, $value, $phpValue = '') {
        $this->params[$name] = ['value' => $value, 'php' => $phpValue];
        return $this;
    }
    
    public function getStyle($name) {
        return isset($this->styles[$name]) ? $this->styles[$name]['value'] : '';
    }
    
    public function getStylePHP($name) {
        return isset($this->styles[$name]) ? $this->styles[$name]['php'] : '';
    }
    
    public function getAttribute($name) {
        return isset($this->attributes[$name]) ? $this->attributes[$name]['value'] : '';
    }
    
    public function getAttributePHP($name) {
        return isset($this->attributes[$name]) ? $this->attributes[$name]['php'] : '';
    }
    
    public function getEvent($name) {
        return isset($this->events[$name]) ? $this->events[$name]['value'] : '';
    }
    
    public function getEventPHP($name) {
        return isset($this->events[$name]) ? $this->events[$name]['php'] : '';
    }
    
    public function getParam($name) {
        return isset($this->params[$name]) ? $this->params[$name]['value'] : '';
    }
    
    public function getParamPHP($name) {
        return isset($this->params[$name]) ? $this->params[$name]['php'] : '';
    }    
    
    public function getAttributesHtml() {
        $html = '';
        foreach($this->attributes as $name => $attribute) {
            $value = htmlspecialchars($attribute['value']) . $this->evalPHP($attribute['php']);
            if ($name != 'sfg-hidden-if') $value = $this->getForm()->replaceVariables($value);
            if ($value) $html .= ' ' . $name . '="' . $value. '"';
        }
        return $html;
    }
    
    public function getEventsHtml() {
        $html = '';
        foreach($this->events as $name => $event) {
            $value = htmlspecialchars($event['value']) . $this->evalPHP($event['php']);
            $value = $this->getForm()->replaceVariables($value);
            if ($value) $html .= ' ' . $name . '="' . $value. '"';
        }
        return $html;
    }
    
    public function getStylesHtml() {
        if (empty($this->styles)) return '';
        $html = ' style="';
        foreach($this->styles as $name => $style) {
            $value = htmlspecialchars($style['value']) . $this->evalPHP($style['php']);
            $value = $this->getForm()->replaceVariables($value);
            if ($value) $html .= $name . ':' . $value . ';';
        }
        $html .= '"';
        return $html;
    }
    
    public function getCaptchaUrl() {
        $form = $this->getForm();
        $urlBuilder = $form->_objectManager->get('Magento\Framework\UrlInterface');
        return $urlBuilder->getUrl('sfg/form/getCaptcha').'?subtask=captcha&object='.$this->getId().'&formid='.$form->getId().'&tmp='.rand();
    }
    
    private function evalPHP($php) {
        if (!$php) return '';
        ob_start();
        $evalFunc = @create_function('$php, $__this','eval(str_replace(\'$this\',\'$__this\',$php));');
        $evalFunc($php, $this);
        //eval($php);
        return ob_get_clean();
    }
    
    public function helper() {
        return \Magento\Framework\App\ObjectManager::getInstance()->get('Itoris\SmartFormerGold\Helper\Data');
    }
}