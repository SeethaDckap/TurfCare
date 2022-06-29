<?php
/**
 * MageVision Mass Email Customers Extension
 *
 * @category     MageVision
 * @package      MageVision_MassEmailCustomers
 * @author       MageVision Team
 * @copyright    Copyright (c) 2019 MageVision (http://www.magevision.com)
 * @license      http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Turfcare\MassEmailCustomers\Controller\Adminhtml\Email;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as SalesCollectionFactory;
use MageVision\MassEmailCustomers\Model\Config;
use Magento\Framework\App\Area;
use Magento\Sales\Model\Order;
use Magento\Framework\Exception\MailException;
use Magento\Backend\Model\View\Result\Redirect;

class MassSend extends \MageVision\MassEmailCustomers\Controller\Adminhtml\Email\MassSend
{

    /**
     * @param object $item
     * @return $this
     * @throws LocalizedException
     * @throws MailException
     */
    public function send($item)
    {
        $this->inlineTranslation->suspend();

        if ($item instanceof Order) {
            $email = $item->getCustomerEmail();
            $orderId = $item->getIncrementId();
            if (!$item->getCustomerIsGuest()) {
                $name = $item->getCustomerFirstname().' '.$item->getCustomerLastname();
                $customerId = $item->getCustomerId();
                $rpToken = $item->getData("rp_token");
            } else {
                $name = '';
            }
        } else {
            $email = $item->getEmail();
            $name = $item->getName();
            $customerId = $item->getId();
            $rpToken = $item->getRpToken();
            $orderId = '';
        }
        $storeId = $item->getData('store_id');
        $this->transportBuilder->setTemplateIdentifier(
            $this->config->getEmailTemplate($storeId)
        )->setTemplateOptions(
            [
                'area' => Area::AREA_FRONTEND,
                'store' => $storeId,
            ]
        )->setFromByScope(
            $this->config->getSender($storeId),
            $storeId
        )->setTemplateVars(
            [
                'customer_name' => $name,
                'customer_email' => $email,
                'customer_id' => $customerId,
                'rp_token' => $rpToken,
                'increment_id' => $orderId
            ]
        )->addTo(
            $email,
            $name
        );
        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();

        $this->inlineTranslation->resume();

        return $this;
    }
}
