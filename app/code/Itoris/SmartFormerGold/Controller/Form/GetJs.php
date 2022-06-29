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

class GetJs extends \Magento\Framework\App\Action\Action {
    
    public function execute() {        
        $formid = (int) $this->getRequest()->getParam('formid', 0);
        $object = $this->getRequest()->getParam('object');
        $form = $this->_objectManager->get('Itoris\SmartFormerGold\Model\Form')->load($formid);
        if ($form->getId()) {
            $config = $form->getConfig();
            header('Content-type: text/javascript');
            if ($object == 'validators') {
                $this->helper()->_echo("sfgObject{$form->getId()}.validators={");
                foreach($config->validators as $key => $validator) {
                    $this->helper()->_echo('"'.htmlspecialchars($validator->alias).'": function(obj, sfgName, obj2){'."\n".$validator->js."\n}");
                    if ($key < count($config->validators) - 1) $this->helper()->_echo(',');
                    $this->helper()->_echo("\n");
                }
                $this->helper()->_echo("}");
            }
            if ($object == 'custom') {
                $this->helper()->_echo(str_replace(['sfgObject'], ['sfgObject'.$form->getId()], $config->globaljs));             
            }
            $this->helper()->safeExit();
        }
    }
    
    public function helper() {
        return \Magento\Framework\App\ObjectManager::getInstance()->get('Itoris\SmartFormerGold\Helper\Data');
    }
}