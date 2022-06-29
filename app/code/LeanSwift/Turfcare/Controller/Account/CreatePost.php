<?php
namespace LeanSwift\Turfcare\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Controller\ResultFactory;
use LeanSwift\Turfcare\Helper\Data as HelperData;


class CreatePost extends \Magento\Customer\Controller\Account\CreatePost
{
    /**
     * XML_PATH_EMAIL_RECIPIENT
     */
    const XML_PATH_EMAIL_RECIPIENT = 'trans_email/ident_general/email';

    /**
     * @var AccountRedirect
     */
    private $accountRedirect;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var StateInterface
     */
    protected $_inlineTranslation;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * CreatePost constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param AccountManagementInterface $accountManagement
     * @param Address $addressHelper
     * @param UrlFactory $urlFactory
     * @param FormFactory $formFactory
     * @param SubscriberFactory $subscriberFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param AddressInterfaceFactory $addressDataFactory
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param CustomerUrl $customerUrl
     * @param Registration $registration
     * @param Escaper $escaper
     * @param CustomerExtractor $customerExtractor
     * @param DataObjectHelper $dataObjectHelper
     * @param AccountRedirect $accountRedirect
     * @param Validator|null $formKeyValidator
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $stateInterface
     * @param HelperData $helperData
     */
    public function __construct(Context $context, Session $customerSession, ScopeConfigInterface $scopeConfig, StoreManagerInterface $storeManager, AccountManagementInterface $accountManagement, Address $addressHelper, UrlFactory $urlFactory, FormFactory $formFactory, SubscriberFactory $subscriberFactory, RegionInterfaceFactory $regionDataFactory, AddressInterfaceFactory $addressDataFactory, CustomerInterfaceFactory $customerDataFactory, CustomerUrl $customerUrl, Registration $registration, Escaper $escaper, CustomerExtractor $customerExtractor, DataObjectHelper $dataObjectHelper, AccountRedirect $accountRedirect, Validator $formKeyValidator = null, TransportBuilder $transportBuilder, StateInterface $stateInterface, HelperData $helperData)
    {
        parent::__construct($context, $customerSession, $scopeConfig, $storeManager, $accountManagement, $addressHelper, $urlFactory, $formFactory, $subscriberFactory, $regionDataFactory, $addressDataFactory, $customerDataFactory, $customerUrl, $registration, $escaper, $customerExtractor, $dataObjectHelper, $accountRedirect, $formKeyValidator);
        $this->_transportBuilder = $transportBuilder;
        $this->_inlineTranslation = $stateInterface;
        $this->_helperData = $helperData;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect|void
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            $post = $this->getRequest()->getParams();
            $senderEmail = $this->_helperData->getSenderEmail();

            $sender = [
                'name' => $this->escaper->escapeHtml($post['firstname']),
                'email' => $this->escaper->escapeHtml($senderEmail),
            ];
            $postObject = new \Magento\Framework\DataObject();
            $postObject->setData($post);
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $transport =
                $this->_transportBuilder
                    ->setTemplateIdentifier('3') // Send the ID of Email template which is created in Admin panel
                    ->setTemplateOptions(
                        ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, // using frontend area to get the template file
                            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,]
                    )
                    ->setTemplateVars(['data' => $postObject])
                    ->setFrom($sender)
                    ->addTo($this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope))
                    ->getTransport();
            $transport->sendMessage();
            $this->_inlineTranslation->resume();
            $this->messageManager->addSuccessMessage(__('Your request Email sent successfully.'));
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('Something Problem.'));
        }
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;
    }
}