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

namespace Itoris\SmartFormerGold\Helper;

use Itoris\SmartFormerGold\plugins\dompdf\src\Dompdf as Dompdf;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Backend\App\ConfigInterface $backendConfig
    ) {
        $this->_backendConfig = $backendConfig;
        $this->_objectManager = $objectManager;
        parent::__construct($context);
    }

    public function isEnabled() {
        return (int)$this->_backendConfig->getValue('itoris_smartformergold/general/enabled') &&
                count(explode('|', $this->_backendConfig->getValue('itoris_core/installed/Itoris_SmartFormerGold'))) == 2;
    }
    
    public function getDomPdfObject() {
        require_once dirname(dirname(__FILE__)) . '/plugins/dompdf/autoload.inc.php';
        return new Dompdf();
    }
    
    public function safeExit(){
        $exitFunc = @create_function('','exit;');
        $exitFunc();
    }
    
    public function _echo($txt){
        $echoFunc = @create_function('$txt','echo $txt;');
        $echoFunc($txt);
    }  
    
    public function session($formId = null, $variable = null, $value = null, $remove = false){
        $session = $this->_objectManager->get('Magento\Customer\Model\Session');
        if (!is_array($session->getSfg())) $session->setSfg([]);
        $sfg = $session->getSfg();
        if (!isset($sfg[(int)$formId]) || !is_array($sfg[(int)$formId])) {
            $sfg[(int)$formId] = [];
            $session->setSfg($sfg);
        }
        if ($remove) {
            if (is_null($variable)) {
                $sfg[(int)$formId] = [];
            } else {
                unset($sfg[(int)$formId][$variable]);
            }
            $session->setSfg($sfg);
            return;
        }
        if (!is_null($value)) {
            $sfg[(int)$formId][$variable] = $value;
            $session->setSfg($sfg);
        }
        return isset($sfg[$formId][$variable]) ? $sfg[$formId][$variable] : null;
    }
}