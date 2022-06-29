<?php

namespace ARI\Partstream\Controller\Index;

class AddProduct extends \Magento\Framework\App\Action\Action
{
	protected $helperProduct;
	protected $helperData;
    private $resultJsonFactory;
	private $productRepository; 
	private $_cart;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,        
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
		\Magento\Checkout\Model\Cart $cart,
		\LeanSwift\Turfcare\Helper\Productdetail $helperProduct,
		\LeanSwift\Turfcare\Helper\Data $helperData,
        array $data = []
    )
    {        
		$this->resultJsonFactory = $resultJsonFactory;
		$this->helperProduct = $helperProduct;
		$this->helperData = $helperData;
		$this->productRepository = $productRepository;
		$this->_cart = $cart;
        parent::__construct($context);
    }


	public function execute()
	{	

		$resultJson = $this->resultJsonFactory->create();
		
		//Parse partstream POST parameters
		parse_str($this->getRequest()->getParam("data"), $data);
        if (!isset($data['arisku']))
        {
            $origData = urldecode($this->getRequest()->getParam("data"));
            $origData = substr($origData, strpos($origData, "?") + 1);
            parse_str($origData, $data);
        }

		$sku=$data['arisku'];         
     	
		try {
			$producti=$this->productRepository->get($sku);
		    $product=$this->helperProduct->getProductBySku($sku);
			$custcodes=$this->helperData->getErpCustomerCodes(); //Array	
			} 
		catch (\Magento\Framework\Exception\NoSuchEntityException $e)
		{
			return $resultJson->setData(['add' => 0 ]);					
		}
		
					
		if ( (null!==($producti->getData('erp_product_category_code'))) && !empty($custcodes)) 
        {
            $brand=$producti->getData('erp_product_category_code');			
			if (in_array($brand, $custcodes)) {
				
			try{
				//add to cart
				$this->_cart->addProduct($producti, ["qty" => $data['ariqty']]);
				$this->_cart->save();
				return $resultJson->setData(['add' => 1]);
			} catch (\Exception $e) 
			{
				return $resultJson->setData(['add' => 0]);
			}
												
			}			

        } else 
		{
				return $resultJson->setData(['add' => 0]);
		}

		
		return $resultJson->setData(['add' => 0]);

	}


}







    





















