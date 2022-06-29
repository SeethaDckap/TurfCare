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

namespace Itoris\SmartFormerGold\lang;
 
class SfgLang {
    var $lang = '';
    
    static function defineLang($currentLang){
		//self::$lang = $currentLang;
		if (isset($this)) $this->lang = $currentLang;
    }
        
    static function getTranslations(){
        $language = 'en';
        SfgLang::defineLang($language);
        /*if (file_exists($GLOBALS['mosConfig_extension_path'].'/lang/'.$language.'.php')){
            include_once($GLOBALS['mosConfig_extension_path'].'/lang/'.$language.'.php');
            if (!isset($GLOBALS['sfg']['translations']) || $GLOBALS['sfg']['translations']['lang']!=$language){
                $GLOBALS['sfg']['translations'] = array();
                $GLOBALS['sfg']['translations'] = $lang;
            }
        }*/
        return;
    }
}
