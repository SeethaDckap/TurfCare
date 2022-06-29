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
namespace Itoris\SmartFormerGold\Block\Adminhtml\Forms\Renderer;

class SubmissionsUrl extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $res = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $con = $res->getConnection('read');
        $tables = $con->fetchCol("SHOW TABLES");
        $form = $this->_objectManager->create('Itoris\SmartFormerGold\Model\Form')->load($row->getId());
        $config = $form->getConfig();
        if (isset($config->database->name) && isset($config->database->map)
            && !empty($config->database->name) && !empty($config->database->map)
            && in_array($config->database->name, $tables)) {
            $count = (int) $con->fetchOne("select count(*) from {$config->database->name}");
            $url = $this->getUrl('*/submissions/index', ['formid' => $row->getId()]);
            return  '<a href="'.$url.'">'.$count.'</a>';
        } else return 'n/a';
    }
}
