<?php

namespace ARI\Partstream\Helper;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\UrlInterface;

class Customer extends AbstractHelper
{

    /**
     * @var Session
     */
    private $customerSession;
    /**
     * @var UrlInterface
     */
    private $urlInterface;

    public function __construct(

        Context $context,
        Session $customerSession,
        UrlInterface $urlInterface
    )
    {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->urlInterface = $context->getUrlBuilder();
    }



    public function redirectIfNotLoggedIn()

    {

        

        if (!$this->customerSession->isLoggedIn()) {

           $this->customerSession->setAfterAuthUrl($this->urlInterface->getCurrentUrl());

          //  $this->customerSession->setBeforeAuthUrl($this->urlInterface->getCurrentUrl());
           
            $this->customerSession->authenticate();
        }

        

    }

}