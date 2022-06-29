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

class GetImageList extends \Magento\Backend\App\Action {
    
	public function execute() {
        $start=''; $prev_dir = '';
		$start=$this->getRequest()->getParam('start', '');
        $parts = explode('/', $start);
        if (count($parts) > 1) {
            unset($parts[count($parts)-1]);
            unset($parts[count($parts)-1]);
            $prev_dir = implode('/', $parts).(count($parts) ? '/' : '');
        } else $prev_dir = '';
		$files = array();
		$dirs = array();
        $basePath = $this->_objectManager->get('\Magento\Framework\App\Filesystem\DirectoryList')->getPath('media').'/';
        $mediaUrl = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $formBlock = $this->_objectManager->get('Itoris\SmartFormerGold\Block\Adminhtml\EditForm');
		if ($handle = @opendir($basePath.$start)) {
			while (false !== ($file = readdir($handle)))
				if ($file != "." && $file != "..") {
					if (is_file($basePath.$start.$file)) $files[]=$file;
					if (is_dir($basePath.$start.$file)) $dirs[]=$file;
				}
			closedir($handle);
		}
		$this->helper()->_echo('<div><i>'.__('Base').':</i> '.$mediaUrl.$start.'</div>');
		$this->helper()->_echo('<table cellpadding=0 cellspacing=0 class="imagesList">');
		$c=0;
		if ($start) $this->helper()->_echo('<tr class="imagesListTr0" onclick="imagesListChangeLevel(\''.$prev_dir.'\')"><td class="imagesListIcon"><img src="'.$formBlock->getViewFileUrl('Itoris_SmartFormerGold::images/dir_up.png').'"></td><td>...</td><td>&nbsp;</td></tr>');
		foreach ($dirs as $value) {$c=1-$c; $this->helper()->_echo('<tr class="imagesListTr'.$c.'" onclick="imagesListChangeLevel(\''.$start.$value.'/\')"><td class="imagesListIcon"><img src="'.$formBlock->getViewFileUrl('Itoris_SmartFormerGold::images/folder.png').'"></td><td><b>'.$value.'</b></td><td>&nbsp;</td></tr>'); }
		foreach ($files as $value) {
			$dim = $this->resizeImage($basePath.$start.$value,200,100,2);
			if (intval($dim[0])>0 && intval($dim[1])>0) {
				$c=1-$c;
				$this->helper()->_echo('<tr class="imagesListTr'.$c.'" onclick="applyImage(\''.$mediaUrl.$start.$value.'\')"><td class="imagesListIcon"><img src="'.$formBlock->getViewFileUrl('Itoris_SmartFormerGold::images/imagefile.png').'"></td><td><b>'.$value.'</b><br />'.$dim[0].' x '.$dim[1].'</td><td style="text-align:center"><img width="'.$dim[0].' height="'.$dim[1].'" src="'.$mediaUrl.$start.$value.'"></td></tr>');
			}
		}
		$this->helper()->_echo('</table>');
	}

	public function resizeImage($file,$width,$height,$mode=0) {
        $basePath = $this->_objectManager->get('\Magento\Framework\App\Filesystem\DirectoryList')->getPath('media').'/';
        $mediaUrl = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

		$file=str_replace($mediaUrl,$basePath,$file);
		if (!file_exists($file)) return;
		list($w, $h, $type, $attr) = @getimagesize($file);
		if ($width<1 || $height<1 || $w<1 || $h<1) return;
		if ($width/$height>$w/$h) $k=$height/$h; else $k=$width/$w;
		$dx=round($w*$k);
		$dy=round($h*$k);
		if ($w<=$width && $h<=$height) {$dx=$w; $dy=$h; }
		if ($mode==0) return "width=$dx height=$dy";
		if ($mode==1) return "width:{$dx}px; height:{$dy}px; ";
		return array($dx,$dy);
	}
    
    protected function _isAllowed() {
        return true;
    }
    
    public function helper() {
        return \Magento\Framework\App\ObjectManager::getInstance()->get('Itoris\SmartFormerGold\Helper\Data');
    }
}