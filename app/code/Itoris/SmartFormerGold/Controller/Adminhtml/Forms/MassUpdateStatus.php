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

class MassUpdateStatus extends \Magento\Backend\App\Action
{
    public function execute()
    {
        if (!$this->_objectManager->get('Itoris\SmartFormerGold\Helper\Data')->isEnabled()) {
            return $this->_redirect($this->getUrl('smartformergold/forms/index'));
        }
        
        $itemIds = (array) $this->getRequest()->getParam('smartformergold_forms_grid');
        $status = (int) $this->getRequest()->getParam('status');
        
        if (is_array($itemIds)) {
            $res = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $con = $res->getConnection('write');            

            $con->query("update `{$res->getTableName('itoris_sfg_form')}` set `status`={$status} where `form_id` in (".implode(',', $itemIds).")");

            $this->messageManager->addSuccess(__('Selected forms were updated'));
        } else {
            $this->messageManager->addError(__('Please select forms'));
        }

        $this->_redirect($this->getUrl('*/*/index'));
    }
}