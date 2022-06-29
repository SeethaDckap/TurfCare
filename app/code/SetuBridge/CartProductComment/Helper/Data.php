<?php
/** Setubridge Technolabs
* http://www.setubridge.com/
* @author SetuBridge
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
**/
namespace SetuBridge\CartProductComment\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->serializer =$serializer;
    }

    public function getConfigValues($code,$storeId = null){
        return $this->scopeConfig->getValue('cartproductcomment/general/'.$code,\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
    }

    public function getStatus(){
        return $this->getConfigValues('active');
    }

    public function getPlaceholderText(){

        if($this->getStatus()){
            return $this->getConfigValues('placeholder_text');
        }

        return false;
    } 
}