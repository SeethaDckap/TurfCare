<?phpnamespace ARI\Partstream\Controller\Index;class GetProduct extends \Magento\Framework\App\Action\Action{	protected $helperProduct;	protected $helperData;    private $resultJsonFactory;	private $productRepository;     public function __construct(        \Magento\Framework\App\Action\Context $context,                \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,		\LeanSwift\Turfcare\Helper\Productdetail $helperProduct,		\LeanSwift\Turfcare\Helper\Data $helperData,        array $data = []    )    {        		$this->resultJsonFactory = $resultJsonFactory;		$this->helperProduct = $helperProduct;		$this->helperData = $helperData;		$this->productRepository = $productRepository;        parent::__construct($context);    }	public function execute()	{			$custprice="";		$available=0;		$brand="";		$resultJson = $this->resultJsonFactory->create();				//Parse partstream POST parameters		parse_str($this->getRequest()->getParam("data"), $data);        if (!isset($data['arisku']))        {            $origData = urldecode($this->getRequest()->getParam("data"));            $origData = substr($origData, strpos($origData, "?") + 1);            parse_str($origData, $data);        }		$sku=$data['arisku'];              			try {			$producti=$this->productRepository->get($sku);		    $product=$this->helperProduct->getProductBySku($sku);			$custcodes=$this->helperData->getErpCustomerCodes(); //Array							} catch (\Magento\Framework\Exception\NoSuchEntityException $e)		{		return $resultJson->setData([			'sku' => $sku,			'available' => 0	        ]);							}												if (isset($product["customer_price"]))        {            $custprice=$product["customer_price"];        }							if ( (null!==($producti->getData('erp_product_category_code'))) && !empty($custcodes))         {            $brand=$producti->getData('erp_product_category_code');						if (in_array($brand, $custcodes)) {				$available=1;			}			        }		 $qtyInc = (int)$producti->getExtensionAttributes()->getStockItem()->getQtyIncrements();		 $qtyMin = (int)$producti->getExtensionAttributes()->getStockItem()->getMinSaleQty();        return $resultJson->setData([			'sku' => $sku,            'price' => $product["price"],            'special_price' => number_format($custprice,2),			'brand' => $brand,			'available' => $available,			'name' => $product["name"],			'qtyInc' => $qtyInc,			'qtyMin' => $qtyMin,			'all' => $product        ]);	}}    