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

class ExecQuery extends \Magento\Backend\App\Action {
    
    public function execute() {
        $q = $this->getRequest()->getParam('q', '');
		if (!$q) {$this->helper()->_echo('No request string received'); return;}
        $res = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $con = $res->getConnection('write');
        $tables = $con->fetchCol("SHOW TABLES");        
		$parts = explode('|',$q);
		$name = $parts[0];
	    foreach ($tables as $key => $value) $tables[$key] = strtolower($value);
	    if (!in_array(strtolower($name),$tables)) {
	    	$s='CREATE TABLE `'.$name.'` ('."\n";
	    	for ($i=1; $i<count($parts); $i+=7) if ($parts[$i]!='') {
	    		$s.='`'.$parts[$i+1].'` '.$parts[$i+2].' '.$parts[$i+3];
	    		if ($parts[$i+4]=='PRI' && !isset($primary)) $primary = $parts[$i];
	    		if ($parts[$i+5]!='') $s.=' DEFAULT '.$con->Quote($parts[$i+5]);
	    		$s.=' '.$parts[$i+6].' ,'."\n";
	    	}
	    	if (isset($primary)) $s.=' PRIMARY KEY (`'.$primary.'`) '."\n"; else $s = substr($s,0,strlen($s)-2)."\n";
	    	$s.=');';
            try {
                $con->query($s);
            } catch (\Exception $e) {
                $this->helper()->_echo('<b>SQL:</b><br /><b style="color:blue">'.str_replace("\n",'<br />',$s).'</b><br /><br /><b>'.'Database has returned error:'.'</b><br /><b style="color:red">'.$e->getMessage().'</b>'); return;
            }
	    } else {
			$rows = $con->fetchAll('SHOW COLUMNS FROM `'.$name.'`');
	    	$fields = Array();
	    	$fields2 = Array();
	    	foreach ($rows as $value) {
	    		$fields[] = $value['Field'];
	    		if (strtolower($value['Extra']) == 'auto_increment') {
	    			$s = 'ALTER TABLE `'.$name.'` CHANGE `'.$value['Field'].'` `'.$value['Field'].'` '.$value['Type'].' NOT NULL';
	    			if ($value['Default']!='') $s.=' DEFAULT '.$con->Quote($value['Default']);
                    try {
                        $con->query($s);
                    } catch (\Exception $e) {
                        //$this->helper()->_echo('<b>SQL:</b><br /><b style="color:blue">'.str_replace("\n",'<br />',$s).'</b><br /><br /><b>'.'Database has returned error:'.'</b><br /><b style="color:red">'.$e->getMessage().'</b>'); return;
                    }
	    		}
	    	}
	    	$s = 'ALTER TABLE `'.$name.'` DROP PRIMARY KEY';
            try {
                $con->query($s);
            } catch (\Exception $e) {
                //$this->helper()->_echo('<b>SQL:</b><br /><b style="color:blue">'.str_replace("\n",'<br />',$s).'</b><br /><br /><b>'.'Database has returned error:'.'</b><br /><b style="color:red">'.$e->getMessage().'</b>'); return;
            }
	    	for ($i=1; $i<count($parts); $i+=7) {
	    		$fields2[]=$parts[$i];
	    		if ($parts[$i]!='' && !in_array($parts[$i],$fields)) {
		    		if ($parts[$i+4]=='PRI') {
		    			$s = 'ALTER TABLE `'.$name.'` ADD PRIMARY KEY ( `'.$parts[$i].'` )';
                        try {
                            $con->query($s);
                        } catch (\Exception $e) {
                            $this->helper()->_echo('<b>SQL:</b><br /><b style="color:blue">'.str_replace("\n",'<br />',$s).'</b><br /><br /><b>'.'Database has returned error:'.'</b><br /><b style="color:red">'.$e->getMessage().'</b>'); return;
                        }
                    }
		    		$s = 'ALTER TABLE `'.$name.'` ADD `'.$parts[$i].'` '.$parts[$i+2].' '.$parts[$i+3];
		    		if ($parts[$i+5]!='') $s.=' DEFAULT '.$con->Quote($parts[$i+5]);
		    		$s.=' '.$parts[$i+6].' ;';
                    try {
                        $con->query($s);
                    } catch (\Exception $e) {
                        $this->helper()->_echo('<b>SQL:</b><br /><b style="color:blue">'.str_replace("\n",'<br />',$s).'</b><br /><br /><b>'.'Database has returned error:'.'</b><br /><b style="color:red">'.$e->getMessage().'</b>'); return;
                    }
		    	} else if ($parts[$i]!='' && in_array($parts[$i],$fields)) {
		    		if ($parts[$i+4]=='PRI') {
		    			$s = 'ALTER TABLE `'.$name.'` ADD PRIMARY KEY ( `'.$parts[$i].'` )';
                        try {
                            $con->query($s);
                        } catch (\Exception $e) {
                            $this->helper()->_echo('<b>SQL:</b><br /><b style="color:blue">'.str_replace("\n",'<br />',$s).'</b><br /><br /><b>'.'Database has returned error:'.'</b><br /><b style="color:red">'.$e->getMessage().'</b>'); return;
                        }
		    		}
		    		$s = 'ALTER TABLE `'.$name.'` CHANGE `'.$parts[$i].'` `'.$parts[$i+1].'` '.$parts[$i+2].' '.$parts[$i+3];
		    		if ($parts[$i+5]!='') $s.=' DEFAULT '.$con->Quote($parts[$i+5]);
		    		$s.=' '.$parts[$i+6].' ;';
                    try {
                        $con->query($s);
                    } catch (\Exception $e) {
                        $this->helper()->_echo('<b>SQL:</b><br /><b style="color:blue">'.str_replace("\n",'<br />',$s).'</b><br /><br /><b>'.'Database has returned error:'.'</b><br /><b style="color:red">'.$e->getMessage().'</b>'); return;
                    }
		    	}
	    	}
	    	foreach ($fields as $value) if (!in_array($value,$fields2)) {
	    		$s = 'ALTER TABLE `'.$name.'` DROP `'.$value.'` ;';
                try {
                    $con->query($s);
                } catch (\Exception $e) {
                    $this->helper()->_echo('<b>SQL:</b><br /><b style="color:blue">'.str_replace("\n",'<br />',$s).'</b><br /><br /><b>'.'Database has returned error:'.'</b><br /><b style="color:red">'.$e->getMessage().'</b>'); return;
                }
	    	}
	    }

    }
    
    protected function _isAllowed() {
        return true;
    }
    
    public function helper() {
        return \Magento\Framework\App\ObjectManager::getInstance()->get('Itoris\SmartFormerGold\Helper\Data');
    }
}