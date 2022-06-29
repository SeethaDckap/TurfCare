<?php
/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Orderbysku
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Orderbysku\Controller\AjaxRequest;

use Magento\Customer\Model\Session;

class Suggestion extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Magedelight\Orderbysku\Helper\Data
     */
    protected $helperData;

    /**
     * Suggestion constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param Session $customerSession
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magedelight\Orderbysku\Helper\Data $helperData
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magedelight\Orderbysku\Helper\Data $helperData,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->session = $customerSession;
        $this->helperData = $helperData;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $data = $this->getRequest()->getPostValue();
        try {
            $suggestions = $this->_objectManager->get('Magedelight\Orderbysku\Model\Search\Product')->getResponseDataByString(trim($data['string']));
            $result->setData(['success' => true]);
            $result->setData(['html' => $suggestions, 'sku_array' => $suggestions['data']]);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result->setData(['msg' => $e->getMessage()]);
        } catch (\Exception $e) {
            $result->setData(['msg' => 'We cannot find the category.']);
        }
        return $result;
    }
}
