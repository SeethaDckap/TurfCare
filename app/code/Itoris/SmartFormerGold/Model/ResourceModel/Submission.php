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
namespace Itoris\SmartFormerGold\Model\ResourceModel;

class Submission extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct() {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_registry = $this->_objectManager->get('Magento\Framework\Registry');
        $form = $this->_registry->registry('sfg_current_form');
        $config = json_decode($form->getForm());
        $this->_init($config->database->name, 'id');
    }
}