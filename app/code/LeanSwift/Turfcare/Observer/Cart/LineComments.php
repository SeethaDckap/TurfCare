<?php
namespace LeanSwift\Turfcare\Observer\Cart;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Update line comments added from quick order poroducts
 */
class LineComments implements ObserverInterface
{
    /**
     * Update line comments added from quick order poroducts
     *
     * {@inheritdoc}
     *
     *
     */
    protected $_request;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->_request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $productComment = '';
        $item = $observer->getEvent()->getQuoteItem();
        $productSku = $item->getSku();
        $quoteItems = $this->_request->getParam('items');

        if(is_array($quoteItems)){

            foreach($quoteItems as $line){
                if(isset($line['product_comment'])){
                    if($productSku == $line['sku']) {
                        $productComment = trim($line['product_comment']);

                    }else{
                        continue;
                    }
                }
            }
        }
        if($productComment) {
            $item->setProductComment($productComment)->save();
        }
        return $this;
    }
}
