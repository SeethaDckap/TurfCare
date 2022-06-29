<?php
/** Setubridge Technolabs
* http://www.setubridge.com/
* @author SetuBridge
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
**/
namespace SetuBridge\CartProductComment\Plugin;

use Magento\Framework\Session\SessionManagerInterface as CoreSession;

class UpdateItem 
{

    protected $_coreSession;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        CoreSession $coreSession
    )
    {
        $this->resourceConnection = $resourceConnection;
        $this->_coreSession = $coreSession;
    }

    public function beforeUpdateItem(\Magento\Checkout\Model\Cart $subject,$itemId)
    {
        $connection  = $this->resourceConnection->getConnection();
        $tableName   = $connection->getTableName('quote_item');

        $query = "SELECT product_comment,product_id FROM ".$tableName." WHERE item_id=".$itemId;

        $result = $this->resourceConnection->getConnection()->fetchRow($query);

        $productComment = $result['product_comment'];
        $cartProductId = $result['product_id'];
        if(isset($productComment) && isset($cartProductId) && $productComment && $cartProductId){
            
            if($this->_coreSession->getProductComment()){
                $this->_coreSession->unsProductComment();
            }
            if($this->_coreSession->getCartProductId()){
                $this->_coreSession->unsCartProductId();
            }
            
            $this->_coreSession->setProductComment($productComment);
            $this->_coreSession->setCartProductId($cartProductId);
        }

    }

    public function afterUpdateItem(\Magento\Checkout\Model\Cart $subject,$result)
    {
        if($this->_coreSession->getProductComment() && ($this->_coreSession->getCartProductId() == $result->getProductId())){
            $result->setProductComment($this->_coreSession->getProductComment());
            $this->_coreSession->unsProductComment();
            $this->_coreSession->unsCartProductId();
        }
        return $result;

    }
}

