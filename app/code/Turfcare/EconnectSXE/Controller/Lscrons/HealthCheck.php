<?php
/**
 * Created by PhpStorm.
 * User: Vidhyasahar
 * Date: 22/5/20
 * Time: 12:27 AM
 */

namespace Turfcare\EconnectSXE\Controller\Lscrons;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class HealthCheck extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;


    /**
     * HealthCheck constructor.
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     */
    public function __construct(Context $context, \Magento\Framework\View\Result\PageFactory $pageFactory)
    {
        $this->_pageFactory = $pageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $jobCode = array("leanswift_econnectsxesxe_send_orders_erp", "leanswift_econnectsxe_order_sync", "leanswift_econnectsxe_orderhistory_sync", "leanswift_econnectsxesxe_product_sync", "leanswift_econnectsxesxe_stock_sync", "leanswift_econnect_customer_address_sync", "leanswift_econnectsxe_accountbalance_sync");
        $job = implode("','", $jobCode);

        try {
            $select = $connection->select()->from('cron_schedule')
                ->where('job_code IN (' . "'" . $job . "'" . ')')->where('created_at >= NOW() - INTERVAL 1 DAY and status = \'success\'');

        } catch (\Exception $e) {
            http_response_code(500);
            echo $e->getMessage();
            header("Status: " . $e->getMessage());
            exit(1);
        }
        $result = $connection->fetchAll($select);
        $cronReport = $this->getCronReport($result);

        return $cronReport;
    }

    /**
     * @param $cronParam
     */
    public function getCronReport($cronParam)
    {
        $jobCode = $this->jobCode();
        if (!empty($cronParam)) {
            foreach ($cronParam as $cron) {
                $jobList[] = $cron['job_code'];
            }
            $missingJobList = array_unique($jobList);
            $missedCron = array_diff($jobCode, $missingJobList);
        } else {
            $missedCron = $jobCode;
        }
        //Send error report to NewRelic
        if (isset($missedCron)) {
            http_response_code(500);
            header("Status:Error Job " . implode(",", $missedCron));
            exit(1);
        }
        http_response_code(200);
        exit(1);
    }

    /**
     * @return array
     */
    public function jobCode()
    {
        $jobCode = array("leanswift_econnectsxesxe_send_orders_erp", "leanswift_econnectsxe_order_sync", "leanswift_econnectsxe_orderhistory_sync", "leanswift_econnectsxesxe_product_sync", "leanswift_econnectsxesxe_stock_sync", "leanswift_econnect_customer_address_sync", "leanswift_econnectsxe_accountbalance_sync");

        return $jobCode;
    }
}