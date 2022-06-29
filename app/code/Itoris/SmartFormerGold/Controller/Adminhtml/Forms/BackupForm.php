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

class BackupForm extends \Magento\Backend\App\Action
{
    public function execute()
    {
        if (!$this->_objectManager->get('Itoris\SmartFormerGold\Helper\Data')->isEnabled()) {
            return $this->_redirect($this->getUrl('smartformergold/forms/index'));
        }
        
        $id = (int) $this->getRequest()->getParam('id');
        $res = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $con = $res->getConnection('read');
        
        if ($id) {
            $form = $this->_objectManager->create('Itoris\SmartFormerGold\Model\Form');
            $form->load($id);
            $baseUrl = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl();
            $form->setForm(str_ireplace($baseUrl, '{base_url}', $form->getForm()));
            $config = $form->getConfig();
            if (isset($config->database->name) && $config->database->name) {
                $structure = $con->fetchRow("show create table `{$config->database->name}`");
                $config->database->structure = str_ireplace("CREATE TABLE", "CREATE TABLE IF NOT EXISTS", $structure['Create Table']);
            }
            $data = [$config];
            $str = json_encode($data);
            $fileFactory = $this->_objectManager->get('Magento\Framework\App\Response\Http\FileFactory');                  
            $fileName = "SFGForm{$id}Backup".date('Y-m-d').'.json';
            $this->getResponse()->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                ->setHeader('Content-type', 'application/octet-stream', true)
                ->setHeader('Content-Length', strlen($str), true)
                ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"', true)
                ->setHeader('Last-Modified', date('r'), true)
                ->sendHeaders();
            $this->getResponse()->setBody($str);

        } else {
            $this->messageManager->addError(__('Please select form'));
            $this->_redirect($this->getUrl('*/*/index'));
        }
    }
}