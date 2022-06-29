<?php
/** Setubridge Technolabs
* http://www.setubridge.com/
* @author SetuBridge
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
**/
namespace SetuBridge\CartProductComment\Plugin;

use Magento\Checkout\Model\Session as CheckoutSession;
use SetuBridge\CartProductComment\Helper\Data;

class DefaultConfigProvider
{
    /**
    * @var CheckoutSession
    */
    protected $checkoutSession;
    /**
    * Constructor
    *
    * @param CheckoutSession $checkoutSession
    */
    public function __construct(
        CheckoutSession $checkoutSession,
        Data $helperData
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->helper = $helperData;
    }
    public function afterGetConfig(
        \Magento\Checkout\Model\DefaultConfigProvider $subject,
        array $result
    ) {
        if($this->helper->getStatus()){
            $items = $result['totalsData']['items'];
            foreach ($items as $index => $item) {
                $quoteItem = $this->checkoutSession->getQuote()->getItemById($item['item_id']);
                
                $comment = __('Comment');
                
                if($quoteItem->getProductComment()){
                    $result['quoteItemData'][$index]['productComment'] = $comment.': '.$quoteItem->getProductComment();
                }
            }
        }
        return $result;
    }
}