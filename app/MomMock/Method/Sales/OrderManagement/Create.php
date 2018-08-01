<?php
/**
 * Copyright (c) 2018 Magenerds
 * All rights reserved
 *
 * This product includes proprietary software developed at Magenerds, Germany
 * For more information see http://www.magenerds.com/
 *
 * To obtain a valid license for using this software please contact us at
 * info@magenerds.com
 */

namespace MomMock\Method\Sales\OrderManagement;

use MomMock\Method\AbstractIncomingMethod;
use MomMock\Entity\Order;
use MomMock\Entity\Order\Item;

/**
 * Class Create
 * @package MomMock\Method\Sales\OrderManagement
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
class Create extends AbstractIncomingMethod
{
    /**
     * @inheritdoc
     */
    public function handleRequestData($data)
    {
        if (!isset($data['params']) || !isset($data['params']['order'])) {
            throw new \Exception('No order data was given');
        }

        $order = $data['params']['order'];

        $orderId = $this->createOrder($order);

        foreach ($order['lines'] as $line) {
            $this->createOrderItem($orderId, $line);
        }

        return [];
    }

    /**
     * @param $orderData
     * @return string
     */
    protected function createOrder($orderData)
    {
        $order = new Order($this->getDb());
        $orderData['status'] = Order::STATUS_NEW;
        return $order->setData($orderData)->save();
    }

    /**
     * @param $orderId
     * @param $itemData
     * @return string
     */
    protected function createOrderItem($orderId, $itemData)
    {
        $item = new Item($this->getDb());
        $itemData['status'] = Item::STATUS_NEW;
        return $item->setData($orderId, $itemData)->save();
    }
}