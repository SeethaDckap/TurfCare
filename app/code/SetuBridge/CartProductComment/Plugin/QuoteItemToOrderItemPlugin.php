<?php
/** Setubridge Technolabs
* http://www.setubridge.com/
* @author SetuBridge
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
**/
namespace SetuBridge\CartProductComment\Plugin;

use Magento\Quote\Model\Quote\Item\ToOrderItem;

class QuoteItemToOrderItemPlugin 
{

    protected $helper;

    public function __construct(
        \SetuBridge\CartProductComment\Helper\Data $helper,
        \Magento\Quote\Model\Quote\ItemFactory $itemFactory,
        \Magento\Quote\Model\ResourceModel\Quote\Item $itemResourceModel
    )
    {
        $this->helper = $helper;
        $this->itemFactory = $itemFactory;
        $this->itemResourceModel = $itemResourceModel;
    }

    public function aroundConvert(ToOrderItem $subject, callable $proceed, $quoteItem, $data = [])
    {

        $commentStatus = false;
        $orderItem = $proceed($quoteItem, $data);
        if(!$orderItem->getParentItemId()){

            if($quoteItem->getProductComment()){
                $commentStatus = true;
                $productComment = $quoteItem->getProductComment();                
            }else if($quoteItem->getQuoteItemId()){
                $commentStatus = true;
                $productCommentItem = $this->itemFactory->create()->getCollection()->addFieldToSelect("product_comment")->addFieldToFilter('item_id',$quoteItem->getQuoteItemId())->getData();

                if (array_key_exists(0, $productCommentItem) && array_key_exists('product_comment', $productCommentItem[0])) {
                    $productComment = $productCommentItem[0]['product_comment'];
                }

            }
            if($commentStatus){
                if(isset($productComment) && $productComment){

                    $productComment = preg_replace( "/\r|\n/", "", $productComment );

                    if ($additionalOptionsQuote = $quoteItem->getOptionByCode('additional_options')) {
                        $additionalOptionsQuote=$this->serializer->unserialize($additionalOptionsQuote->getValue());
                    }

                    $additionalOptionsQuote[] = [
                        'label' => __('Comment'),
                        'value' => $productComment
                    ];

                    if($additionalOptionsOrder = $orderItem->getProductOptionByCode('additional_options')){
                        $additionalOptions = array_merge($additionalOptionsQuote, $additionalOptionsOrder);
                    }
                    else{
                        $additionalOptions = $additionalOptionsQuote;
                    }
                    if(!empty($additionalOptions)){
                        $options = $orderItem->getProductOptions();
                        $options['additional_options'] = $additionalOptions;
                        $orderItem->setProductOptions($options);
                    }
                }
            }

        }

        return $orderItem;
    }
}

