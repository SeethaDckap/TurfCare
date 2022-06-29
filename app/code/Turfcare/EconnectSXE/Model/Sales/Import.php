<?php
/**
 * *
 *   LeanSwift eConnect Extension
 *
 *   NOTICE OF LICENSE
 *
 *   This source file is subject to the LeanSwift eConnect Extension License
 *   that is bundled with this package in the file LICENSE.txt located in the Connector Server.
 *
 *   DISCLAIMER
 *
 *  This extension is licensed and distributed by LeanSwift. Do not edit or add to this file
 *   if you wish to upgrade Extension and Connector to newer versions in the future.
 *   If you wish to customize Extension for your needs please contact LeanSwift for more
 *   information. You may not reverse engineer, decompile,
 *   or disassemble LeanSwift Connector Extension (All Versions), except and only to the extent that
 *   such activity is expressly permitted by applicable law not withstanding this limitation.
 *
 * @category  LeanSwift
 * @package   LeanSwift_EconnectSXE
 * @copyright Copyright (c) 2019 LeanSwift Inc. (http://www.leanswift.com)
 * @license   http://www.leanswift.com/license/connector-extension
 */

namespace Turfcare\EconnectSXE\Model\Sales;

class Import extends \LeanSwift\EconnectSXE\Model\Sales\Import
{

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param $finalOrno
     * @return bool
     * @throws \Exception
     */
    public function syncStatus(\Magento\Sales\Model\Order $order, $finalOrno)
    {
        if (!$finalOrno) {
            return false;
        }
        $this->adapter = $this->getAdapter();
        $orderStatus = $this->getOrderInfo($finalOrno);

        if (!empty($orderStatus)) {
            if ($orderStatus == 9) {
                if ($order->canCancel()) {
                    $order->cancel()->save();
                }
                return true;
            }

            if ($orderStatus == 3 || $orderStatus == 5) {
                $items = $this->_getItems($order);
                $isShipmentNotCreated = $this->createShipment($order, $items);
                if (!$isShipmentNotCreated) {
                    return false;
                }
            }
            if ($orderStatus == 4 || $orderStatus == 5) {
                $items = $this->_getItems($order);
                $this->createInvoice($order, $items);
            }
            if ($orderStatus >= 3) {
                $this->updateTracks($order);
            }
            $erpStatus = 'erp_sts' . $orderStatus;
            $this->_m3status = $erpStatus;
            if ($order->getStatus() == $erpStatus) {
                //No change needed if current status same as ERP status
                return true;
            }
            $order->addStatusToHistory($erpStatus, '', false);
            $order->save();
            return true;
        }
        return false;
    }
}
