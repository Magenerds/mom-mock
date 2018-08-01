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

use MomMock\Method\AbstractOutgoingMethod;

/**
 * Class Create
 * @package MomMock\Method\Sales\OrderManagement
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
class Updated extends AbstractOutgoingMethod
{
    /**
     * @inheritdoc
     */
    public function send($data)
    {
        $orderId = $data['order_id'];

        $order = $this->db->createQueryBuilder()
            ->select('*')
            ->from('`order`')
            ->where('`id` = ?')
            ->setParameter(0, $orderId)
            ->execute()
            ->fetch();

        $orderItems = $this->db->createQueryBuilder()
            ->select('*')
            ->from('`order_item`')
            ->where('`order_id` = ?')
            ->setParameter(0, $orderId)
            ->execute()
            ->fetchAll();

        // insert order data to updated template
        $method = $this->methodResolver->getMethodForServiceClass(get_class($this));
        $template = $this->templateHelper->getTemplateForMethod($method);

        // insert order data
        foreach ($order as $key => $value) {
            $template = str_replace(sprintf('{{order.%s}}', $key), $value, $template);
        }

        // insert order item data
        $updatedData = json_decode($template, true);

        $lines = [];
        $totalAmount = 0;

        foreach ($orderItems as $orderItem) {
            $lineTemplate = json_encode($updatedData['order']['lines'], true);

            if ($orderItem['status'] == 'shipped') {
                $totalAmount += $orderItem['gross_amount'];
            }

            foreach ($orderItem as $key => $value) {
                $lineTemplate = str_replace(sprintf('{{order_item.%s}}', $key), $value, $lineTemplate);
            }

            $lines = array_merge($lines, json_decode($lineTemplate, true));
        }

        $updatedData['order']['lines'] = $lines;
        $updatedData['order']['payments'][0]['transactions'][0]['amount'] = $totalAmount;
        $result = $this->rpcClient->send($updatedData, $method);

        return $result;
    }
}