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

namespace MomMock\Method\Logistics\FulfillmentManagement;

use MomMock\Method\AbstractOutgoingMethod;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use MomMock\Entity\Order\Item;
use MomMock\Entity\Order;
use MomMock\Method\Sales\OrderManagement\Updated;

/**
 * Class CustomerShipmentDone
 * @package MomMock\Method\Logistics\FulfillmentManagement
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
class CustomerShipmentDone extends AbstractOutgoingMethod
{
    /**
     * @inheritdoc
     */
    public function send($data)
    {
        $orderId = $data['order_id'];
        $orderItemIds = explode(',', $data['order_item_ids']);

        $order = $this->db->createQueryBuilder()
            ->select('*')
            ->from('`order`')
            ->where('`id` = ?')
            ->setParameter(0, $orderId)
            ->execute()
            ->fetch();

        $queryBuilder = $this->db->createQueryBuilder();
        $orComposite = new CompositeExpression(CompositeExpression::TYPE_OR);

        $i = 1;
        foreach ($orderItemIds as $orderItemId) {
            $orComposite->add('`id` = :order_item_id' . $i);
            $queryBuilder->setParameter('order_item_id' . $i++, $orderItemId);
        }

        $orderItems = $queryBuilder
            ->select('*')
            ->from('`order_item`')
            ->where($orComposite)
            ->execute()
            ->fetchAll();

        // insert order data to shipment template
        $method = $this->methodResolver->getMethodForServiceClass(get_class($this));
        $template = $this->templateHelper->getTemplateForMethod($method);

        // insert order data
        foreach ($order as $key => $value) {
            $template = str_replace(sprintf('{{order.%s}}', $key), $value, $template);
        }

        // insert order item data
        $shipmentData = json_decode($template, true);

        $items = [];
        $aggregatedItems = [];
        $packageItems = [];

        foreach ($orderItems as $orderItem) {
            $itemTemplate = json_encode($shipmentData['shipment']['items'], true);
            $aggregatedItemTemplate = json_encode($shipmentData['shipment']['packages'][0]['aggregated_items'], true);
            $packageItemTemplate = json_encode($shipmentData['shipment']['packages'][0]['items'], true);

            foreach ($orderItem as $key => $value) {
                $itemTemplate = str_replace(sprintf('{{order_item.%s}}', $key), $value, $itemTemplate);
                $aggregatedItemTemplate = str_replace(sprintf('{{order_item.%s}}', $key), $value, $aggregatedItemTemplate);
                $packageItemTemplate = str_replace(sprintf('{{order_item.%s}}', $key), $value, $packageItemTemplate);
            }

            $items = array_merge($items, json_decode($itemTemplate, true));
            $aggregatedItems = array_merge($aggregatedItems, json_decode($aggregatedItemTemplate, true));
            $packageItems = array_merge($packageItems, json_decode($packageItemTemplate, true));
        }

        $shipmentData['shipment']['items'] = $items;
        $shipmentData['shipment']['packages'][0]['aggregated_items'] = $aggregatedItems;
        $shipmentData['shipment']['packages'][0]['items'] = $packageItems;

        $result = $this->rpcClient->send($shipmentData, $method);

        $this->setShippedStatus($orderItemIds);
        $this->checkForCompleteStatus($orderId);

        return $result;
    }

    /**
     * Set shipped status to order items
     *
     * @param $orderItemIds
     */
    protected function setShippedStatus($orderItemIds)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $orComposite = new CompositeExpression(CompositeExpression::TYPE_OR);

        $i = 1;
        foreach ($orderItemIds as $orderItemId) {
            $orComposite->add('`id` = :order_item_id' . $i);
            $queryBuilder->setParameter('order_item_id' . $i++, $orderItemId);
        }

        $queryBuilder->update('order_item', 'oi')
            ->set('oi.status', ':status')
            ->setParameter(':status', Item::STATUS_SHIPPED)
            ->where($orComposite)
            ->execute();
    }

    /**
     * Checks if order is complete, sets its status and sends the complete message
     *
     * @param $orderId
     */
    protected function checkForCompleteStatus($orderId)
    {
        $orderItems = $this->db->createQueryBuilder()
            ->select('*')
            ->from('`order_item`')
            ->where('`order_id` = ?')
            ->setParameter(0, $orderId)
            ->execute()
            ->fetchAll();

        foreach ($orderItems as $item) {
            if ($item['id'] == 'SHIPPING') continue;
            if ($item['status'] == Item::STATUS_NEW) return;
        }

        $this->db->createQueryBuilder()
            ->update('`order`')
            ->set('status', ':status')
            ->setParameter(':status', Order::STATUS_COMPLETE)
            ->where('id = :order_id')
            ->setParameter(':order_id', $orderId)
            ->execute();

        $updated = new Updated(
            $this->db,
            $this->methodResolver,
            $this->templateHelper,
            $this->rpcClient
        );

        $updated->send(['order_id' => $orderId]);
    }
}