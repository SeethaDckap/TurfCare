<?php
/** Setubridge Technolabs
* http://www.setubridge.com/
* @author SetuBridge
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
**/
namespace SetuBridge\CartProductComment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Checkout\Model\Cart\Interceptor;
use Magento\Framework\App\RequestInterface;

class UpdateItemObserver implements ObserverInterface
{
    protected $_request;

    public function __construct(
        RequestInterface $request
    ) {
        $this->_request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $cart = $observer->getData('cart');

        $info = $observer->getData('info');

        $items = $cart->getQuote()->getItems();

        foreach ($items as $item) {

            if(!empty($info[$item->getItemId()])){
                if(array_key_exists('product_comment',$info[$item->getItemId()])){
                    $productComment = $info[$item->getItemId()]['product_comment'];
                    if(isset($productComment) && $productComment){
                        $item->setProductComment($productComment);
                    }
                }
            }
        }
    }
}