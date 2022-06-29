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

class GetFile extends \Magento\Backend\App\Action {
    
    public function execute() {
        $fullFileName = $this->getRequest()->getParam('fileName');
        $directoryList = $this->_objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
        $filesPath = $directoryList->getPath('media').'/sfg/files/';
        $fileFactory = $this->_objectManager->get('Magento\Framework\App\Response\Http\FileFactory');
        if ($fullFileName && file_exists($filesPath.$fullFileName)) {                    
            $fileName = substr($fullFileName, 64);
            $this->getResponse()->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                ->setHeader('Content-type', 'application/octet-stream', true)
                ->setHeader('Content-Length', filesize($filesPath.$fullFileName), true)
                ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"', true)
                ->setHeader('Last-Modified', date('r'), true)
                ->sendHeaders();
            $this->getResponse()->setBody(file_get_contents($filesPath.$fullFileName));
        }
    }
}