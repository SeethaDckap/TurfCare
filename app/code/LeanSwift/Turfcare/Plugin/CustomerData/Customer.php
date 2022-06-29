<?php

namespace LeanSwift\Turfcare\Plugin\CustomerData;

use Magento\Customer\Helper\Session\CurrentCustomer;

/**
 * Customer section
 */
class Customer
{
    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @param CurrentCustomer $currentCustomer
     */
    public function __construct(
        CurrentCustomer $currentCustomer
    ) {
        $this->currentCustomer = $currentCustomer;
    }

    /**
     * Customer firstname format for cache issue fixes
     *
     * {@inheritdoc}
     */
    public function afterGetSectionData(\Magento\Customer\CustomerData\Customer $customer, $result)
    {
        $currentCustomer = $this->currentCustomer->getCustomer();

        /* Format firstnaem and return*/
        $firstName = ucwords(strtolower($currentCustomer->getFirstname()));

        if($firstName)
            $result['firstname'] = $firstName;
        return $result;
    }
}