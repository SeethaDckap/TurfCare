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
 
namespace Itoris\SmartFormerGold\Controller\Adminhtml\Forms;

class GetDbTableFields extends \Magento\Backend\App\Action {
    
    public function execute() {
		$table = $this->getRequest()->getParam('table', '');
        if ($table) {
            $res = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $con = $res->getConnection('read');
            $rows = $con->fetchAll( 'SHOW COLUMNS FROM `'.$table.'`' );
            if (!is_array($rows)) return;
            foreach ($rows as $value) $this->helper()->_echo('<b>'.$value['Field'].'</b> <span style="color:red">'.$value['Type'].'</span>|');
        }
    }
    
    protected function _isAllowed() {
        return true;
    }
    
    public function helper() {
        return \Magento\Framework\App\ObjectManager::getInstance()->get('Itoris\SmartFormerGold\Helper\Data');
    }
}