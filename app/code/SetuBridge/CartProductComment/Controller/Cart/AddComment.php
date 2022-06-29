<?php
/** Setubridge Technolabs
* http://www.setubridge.com/
* @author SetuBridge
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
**/
namespace SetuBridge\CartProductComment\Controller\Cart;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Cart as CustomerCart;

class AddComment extends \Magento\Framework\App\Action\Action
{

    /**
    * @var \Magento\Checkout\Model\Cart
    */
    protected $cart;
    /**
    * @var \Magento\Checkout\Model\Session
    */
    protected $checkoutSession;

    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        CustomerCart $cart
    ) 
    {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->cart = $cart;
    }


    public function execute()
    {    

        $jsonFactory=$this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            $params =  $this->getRequest()->getParams();
            $quoteItemId = $params['item_id'];

            $item = $this->cart->getQuote()->getItemById($quoteItemId);
            $productComment = '';
            if($item){
                $productComment = $params['product_comment'];
                $item->setProductComment($productComment);
                $result = $this->cart->getQuote()->save();

                $jsonFactory->setData(['error'=>false,'message' => "Comment has been successfully added."]);
                return $jsonFactory;
            }
            else{
                $jsonFactory->setData(['error' => true,'message' => "We can\'t find the quote item."]);
                return $jsonFactory;
            }
        } catch (\Exception $e) {
            $jsonFactory->setData(['error' => true,'message' => $e->getMessage()]);
            return $jsonFactory;
        }  
    }

}
