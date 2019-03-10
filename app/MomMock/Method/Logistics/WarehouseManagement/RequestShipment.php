<?php
/**
 * Copyright (c) 2019 Magenerds
 * All rights reserved
 *
 * This product includes proprietary software developed at Magenerds, Germany
 * For more information see http://www.magenerds.com/
 *
 * To obtain a valid license for using this software please contact us at
 * info@magenerds.com
 */

namespace MomMock\Method\Logistics\WarehouseManagement;

use MomMock\Method\AbstractOutgoingMethod;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use MomMock\Entity\Order\Item;
use MomMock\Entity\Order;
use MomMock\Method\Sales\OrderManagement\Updated;
use Doctrine\DBAL\Connection;

/**
 * Class RequestShipment
 * @package MomMock\Method\Logistics\FulfillmentManagement
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
class RequestShipment extends AbstractOutgoingMethod
{
    /**
     * @inheritdoc
     */
    public function send($data)
    {
        if (empty($data['source_id']) || $data['source_id'] == 'select-source') {
            throw new \Exception('Please provide a source id');
        }

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

        // insert source id
        $template = str_replace('{{source_id}}', $data['source_id'], $template);

        // insert random request id
        $template = str_replace('{{request_id}}', uniqid(), $template);

        // insert order data
        foreach ($order as $key => $value) {
            $template = str_replace(sprintf('{{order.%s}}', $key), $value, $template);
        }

        // insert order item data
        $requestShipmentData = json_decode($template, true);

        $items = [];
        $aggregatedItems = [];

        foreach ($orderItems as $orderItem) {
            $itemTemplate = json_encode($requestShipmentData['items'], true);
            $aggregatedItemTemplate = json_encode($requestShipmentData['aggregated_items'], true);

            foreach ($orderItem as $key => $value) {
                $itemTemplate = str_replace(sprintf('{{order_item.%s}}', $key), $value, $itemTemplate);
                $aggregatedItemTemplate = str_replace(sprintf('{{order_item.%s}}', $key), $value, $aggregatedItemTemplate);
            }

            $items = array_merge($items, json_decode($itemTemplate, true));
            $aggregatedItems = array_merge($aggregatedItems, json_decode($aggregatedItemTemplate, true));
        }

        $requestShipmentData['items'] = $items;
        $requestShipmentData['aggregated_items'] = $aggregatedItems;

        $result = $this->rpcClient->send($requestShipmentData, $method);

        return $result;
    }
}