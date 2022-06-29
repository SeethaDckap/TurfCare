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

namespace Itoris\SmartFormerGold\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        //creating sample images
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $directoryList = $objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
        $filesPath = $directoryList->getPath('media').'/sfg/';
        
        if (!file_exists($filesPath.'images/')) mkdir($filesPath.'images/', 0777, true);
        if (!file_exists($filesPath.'files/')) mkdir($filesPath.'files/', 0777, true);
        $src = dirname(dirname(__FILE__)).'/view/frontend/web/images/front';
        $this->recurseCopy($src, $filesPath.'images/');

        $setup->startSetup();
        
        $setup->run("
            CREATE TABLE `{$setup->getTable('itoris_sfg_form')}` (
              `form_id` int(11) NOT NULL,
              `status` tinyint(4) NOT NULL,
              `name` varchar(255) NOT NULL,
              `description` text NOT NULL,
              `form` longtext NOT NULL,
              `params` text NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        
        $setup->run("
            CREATE TABLE `{$setup->getTable('itoris_sfg_submission_index')}` (
              `form_id` int(10) UNSIGNED NOT NULL,
              `submission_id` int(10) UNSIGNED NOT NULL,
              `customer_id` int(10) UNSIGNED NOT NULL,
              `unique_key` varchar(64) NOT NULL,
              `created` datetime NOT NULL,
              `updated` datetime NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        
        $setup->run("
            ALTER TABLE `{$setup->getTable('itoris_sfg_form')}` ADD PRIMARY KEY (`form_id`);
        ");
        
        $setup->run("
            ALTER TABLE `{$setup->getTable('itoris_sfg_form')}` CHANGE `form_id` `form_id` INT(11) NOT NULL AUTO_INCREMENT;
        ");
        
        $setup->run("
            ALTER TABLE `{$setup->getTable('itoris_sfg_submission_index')}`
              ADD UNIQUE KEY `form_submission_customer` (`form_id`,`submission_id`,`customer_id`) USING BTREE,
              ADD UNIQUE KEY `unique_key` (`unique_key`) USING BTREE,
              ADD KEY `form_id` (`form_id`),
              ADD KEY `submission_id` (`submission_id`),
              ADD KEY `customer_id` (`customer_id`);
        ");
        
        $setup->run("
            INSERT INTO {$setup->getTable('core_config_data')} (`scope`, `scope_id`, `path`, `value`) VALUES ('default', 0, 'itoris_smartformergold/general/enabled', '1');
        ");
        
        //creating sample forms        
        $res = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $con = $res->getConnection('write');
        $dbPrefix = $objectManager->get('Magento\Framework\App\DeploymentConfig')->get('db/table_prefix');
        $baseUrl = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl();
        $str = file_get_contents(__DIR__ . '/examples.json');
        $configs = json_decode($str);
        foreach($configs as $config) {
            if (isset($config->name) && isset($config->description)) {
                if (isset($config->database->structure)) {
                    //creating associated DB table
                    $con->query(str_replace('itoris_sfg_', $dbPrefix.'itoris_sfg_', $config->database->structure));
                    unset($config->database->structure);
                }
                $form = $objectManager->create('Itoris\SmartFormerGold\Model\Form');
                $form->setName($config->name)
                    ->setStatus(1)
                     ->setDescription($config->description)
                     ->setForm(str_ireplace(['{base_url}', 'itoris_sfg_'], [$baseUrl, $dbPrefix.'itoris_sfg_'], json_encode($config)))
                     ->save();
            }
        }
        
        $setup->run("
            INSERT INTO `{$setup->getTable('itoris_sfg_example_poll')}` (`id`, `answer1`, `answer2`, `answer3`, `answer4`, `answer5`, `answer6`) VALUES
            (1, 1, 0, 0, 1, 1, 0),
            (2, 0, 0, 1, 1, 0, 0),
            (3, 0, 1, 0, 0, 1, 0),
            (4, 1, 0, 0, 1, 1, 0),
            (5, 0, 1, 1, 1, 0, 1),
            (6, 1, 0, 0, 1, 1, 0),
            (7, 0, 1, 0, 1, 1, 1),
            (8, 0, 1, 0, 1, 0, 1),
            (9, 1, 0, 1, 0, 0, 1),
            (10, 0, 1, 1, 1, 1, 1),
            (11, 0, 1, 0, 1, 0, 0);
        ");
        
        $setup->endSetup();        
    }
    
    public function recurseCopy($src, $dst) { 
        $dir = opendir($src); 
        @mkdir($dst); 
        while(false !== ( $file = readdir($dir)) ) { 
            if (( $file != '.' ) && ( $file != '..' )) { 
                if ( is_dir($src . '/' . $file) ) { 
                    recurse_copy($src . '/' . $file,$dst . '/' . $file); 
                } 
                else { 
                    copy($src . '/' . $file,$dst . '/' . $file); 
                } 
            } 
        } 
        closedir($dir); 
    } 
}