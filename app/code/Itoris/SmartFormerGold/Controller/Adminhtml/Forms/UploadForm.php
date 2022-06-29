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

class UploadForm extends \Magento\Backend\App\Action
{
    public function execute()
    {
        if (!$this->_objectManager->get('Itoris\SmartFormerGold\Helper\Data')->isEnabled()) {
            return $this->_redirect($this->getUrl('smartformergold/forms/index'));
        }
        
        $file = $this->getRequest()->getFiles()->get('form_file');
        $res = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $con = $res->getConnection('write');
        $baseUrl = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl();

        if ($file['error'] == 0) {
            $str = file_get_contents($file['tmp_name']);
            $configs = json_decode($str);
            if (is_array($configs)) {
                $n = 0;
                foreach($configs as $config) {
                    if (isset($config->name) && isset($config->description)) {
                        if (isset($config->database->structure)) {
                            //creating associated DB table
                            $con->query($config->database->structure);
                            unset($config->database->structure);
                        }
                        $form = $this->_objectManager->create('Itoris\SmartFormerGold\Model\Form');
                        $form->setName($config->name)
                             ->setDescription($config->description)
                             ->setForm(str_ireplace('{base_url}', $baseUrl, json_encode($config)))
                             ->save();
                        $n++;
                    }
                }
                $this->messageManager->addSuccess(__('%1 of %2 forms have been imported successfully', $n, count($configs)));                
            } else {
                $this->messageManager->addError(__('Incorrect file format'));
            }
        } else {
            $this->messageManager->addError(__('Please select a file'));
        }
    
        $this->_redirect($this->getUrl('*/*/index'));
    }
}