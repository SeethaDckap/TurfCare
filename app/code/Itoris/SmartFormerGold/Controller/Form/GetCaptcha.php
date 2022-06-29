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

use Itoris\SmartFormerGold\plugins\captcha\alikon\alikoncaptcha;
use Itoris\SmartFormerGold\plugins\captcha\captchaform\captchaform;
use Itoris\SmartFormerGold\plugins\captcha\securimage\securimage;

class GetCaptcha extends \Magento\Framework\App\Action\Action {
    
    public function execute() {
        $subtask = $this->getRequest()->getParam('subtask');
        $formid = (int) $this->getRequest()->getParam('formid');
        $elementId = (int) $this->getRequest()->getParam('object');
        $action = (int) $this->getRequest()->getParam('action');
        if ($subtask == 'captcha' && $formid && $elementId) {
            $form = $this->_objectManager->create('Itoris\SmartFormerGold\Model\Form')->load($formid);
            if ($form->getId()) {
                $elements = $form->getConfig()->elements;
                if (isset($elements[$elementId])) {
                    $element = $this->_objectManager->create('Itoris\SmartFormerGold\Model\Element')
                                        ->setId($elementId)
                                        ->setForm($form)
                                        ->setConfig($elements[$elementId]);
                                        
                    $captchaType = (int) $element->getParam('captcha-type');
                    $width = (int) $element->getStyle('width');
                    $height = (int) $element->getStyle('height');
                    $base = substr(__FILE__,0,strpos(strtolower(__FILE__),'controller')-1);
                    switch ($captchaType) {
                        case 0:
                            $captcha = new alikoncaptcha() ;
                            $captcha->codelength = $element->getParam('captcha-length');
                            $captcha->chars = $element->getParam('captcha-symbols');
                            if ($width) $captcha->iwidth = $width;
                            if ($height) $captcha->iheight = $height;
                            if ($action == 'reload' || is_null($this->helper()->session($form->getId(), 'sec_code'))) {
                                $this->helper()->session($form->getId(), 'sec_code', $captcha->captchacode());
                            }
                            $captcha->image($this->helper()->session($form->getId(), 'sec_code'));
                        break;
                        
                        case 1:
                            $captcha = new captchaform();
                            $captcha->type='png';
                            $captcha->fontdir = $base.'/plugins/captcha/captchaform';
                            $captcha->backgrounddir = $base.'/plugins/captcha/captchaform/bg';
                            $captcha->codelength = $element->getParam('captcha-length');
                            $captcha->chars = $element->getParam('captcha-symbols');
                            $captcha->colors = array("FF0000", "990099", "0000FF");
                            if ($width) $captcha->width = $width;
                            if ($height) $captcha->height = $height;
                            if ($action == 'reload' || is_null($this->helper()->session($form->getId(), 'sec_code'))) {
                                $this->helper()->session($form->getId(), 'sec_code', $captcha->captchacode());
                            }
                            $captcha->image($this->helper()->session($form->getId(), 'sec_code'));
                        break;
                        
                        case 2:
                            $captcha = new securimage();
                            $captcha->ttf_file=$base.'/plugins/captcha/securimage/elephant.ttf';
                            $captcha->code_length = $element->getParam('captcha-length');
                            $captcha->charset = $element->getParam('captcha-symbols');
                            if ($width) $captcha->image_width = $width;
                            if ($height) $captcha->image_height = $height;                           
                            if ($action == 'reload' || is_null($this->helper()->session($form->getId(), 'sec_code'))) {
                                $this->helper()->session($form->getId(), 'sec_code', $captcha->captchacode());
                            }
                            $captcha->image($this->helper()->session($form->getId(), 'sec_code'));
                        break;
                    }
                }
                
            } 
        }
    }
    
    public function helper() {
        return \Magento\Framework\App\ObjectManager::getInstance()->get('Itoris\SmartFormerGold\Helper\Data');
    }
}