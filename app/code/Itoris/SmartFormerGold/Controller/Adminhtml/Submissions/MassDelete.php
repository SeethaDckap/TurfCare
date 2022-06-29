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
 
namespace Itoris\SmartFormerGold\Controller\Adminhtml\Submissions;

class MassDelete extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $referer = $this->_redirect->getRefererUrl();
        $pos = strpos($referer, '/formid/');
        if ($pos !== false) $formId = intval(substr($referer, $pos + 8)); else $formId = 0;
        if ($formId) {
            $form = $this->_objectManager->get('Itoris\SmartFormerGold\Model\Form')->load($formId);
            $this->_objectManager->get('Magento\Framework\Registry')->register('sfg_current_form', $form);
            
            $filter = $this->_objectManager->create('Magento\Ui\Component\MassAction\Filter');
            $collectionFactory = $this->_objectManager->create('Itoris\SmartFormerGold\Model\ResourceModel\Submission\CollectionFactory');

            $collection = $filter->getCollection($collectionFactory->create());
            $itemIds = $collection->getAllIds();
        } else $itemIds = null;
        
        if (is_array($itemIds)) {
            $config = $form->getConfig();

            $res = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
            $con = $res->getConnection('write');            

            $con->query("delete from `{$config->database->name}` where `id` in (".implode(',',$itemIds).")");

            $this->messageManager->addSuccess(__('Selected items removed'));
        } else {
            $this->messageManager->addError(__('Please select items'));
        }

        $this->_redirect($this->_redirect->getRefererUrl());
    }
}