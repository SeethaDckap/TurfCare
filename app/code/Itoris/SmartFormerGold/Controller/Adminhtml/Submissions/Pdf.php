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

use Magento\Framework\Controller\ResultFactory;

class Pdf extends \Magento\Backend\App\Action {
    
    public function execute() {
        $formId = (int)$this->getRequest()->getParam('formid');
        $id = (int)$this->getRequest()->getParam('id');
        $block = $this->_objectManager->create('Itoris\SmartFormerGold\Block\Adminhtml\Submissions\Pdf')->setTemplate('Itoris_SmartFormerGold::pdf.phtml');
        $html = '<html><head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <style>
              body { font-family: DejaVu Sans, sans-serif; }
            </style>
            </head><body>'.$block->toHtml().'</body></html>';
        $dompdf = $this->_objectManager->get('Itoris\SmartFormerGold\Helper\Data')->getDomPdfObject();
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser
        $dompdf->stream('Form_'.$formId.'_Submission_'.$id.'.pdf');
    }
}