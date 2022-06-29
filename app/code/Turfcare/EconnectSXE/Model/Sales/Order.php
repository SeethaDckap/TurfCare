<?php
namespace Turfcare\EconnectSXE\Model\Sales;


class Order extends \LeanSwift\EconnectSXE\Model\Sales\Order
{

    /**
     * @param $state
     *
     * @return mixed
     */
    protected function _getOrderCollection($state)
    {
        $tomorrow = date('Y-m-d', microtime(true) + 86400); //Adding one more day to adjust the timezone difference
        $fromDate = $this->getOrderSyncFromDate();
        if ($state == 'new') {
            $orderCollection = $this->salesOrderCollection->create()->getCollection()->addFieldToFilter(
                [
                    'state',
                    'status'
                ],
                [
                    ['state', 'eq' => $state],
                    ['status', 'in' => ['pending', 'processing']]
                ]
            );
            $orderCollection = $orderCollection->addFieldToFilter(
                'ext_order_id',
                ['is' => new Zend_Db_Expr('NULL')]
            );

            if ($fromDate) {
                $orderCollection =  $orderCollection
                    ->addAttributeToFilter('created_at', array('from' => $fromDate, 'to' => $tomorrow));
            }

            $orderCollection->setOrder('entity_id', 'DESC');

        } else {
            $orderCollection = $this->salesOrderCollection->create()->getCollection()->addFieldToFilter(
                'state',
                ['eq' => $state]
            );

            if ($fromDate) {
                $orderCollection =  $orderCollection
                    ->addAttributeToFilter('created_at', array('from' => $fromDate, 'to' => $tomorrow));
            }
            $orderCollection->setOrder('entity_id', 'DESC');
        }

        return $orderCollection;
    }

    private function getOrderSyncFromDate()
    {
        $tomorrow = date('Y-m-d', microtime(true) + 86400); //Adding one more day to adjust the timezone difference
        $orderSyncMonthsLimit = '1';//$this->_helper->getOrderSyncMonthsLimit();
        if (!empty($orderSyncMonthsLimit)) {
            $difference = "-" . $orderSyncMonthsLimit . " months";
            $fromDate = date('Y-m-d', strtotime($difference, strtotime($tomorrow)));
            return $fromDate;
        }

        return false;
    }
}
