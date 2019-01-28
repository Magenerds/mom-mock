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
namespace MomMock\Method\Postsales;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use MomMock\Entity\Rma;
use MomMock\Entity\Rma\Item;
use MomMock\Entity\Order\Item as OrderItem;
use MomMock\Method\AbstractOutgoingMethod;

/**
 * Class AbstractUpdated
 *
 * @package MomMock\Method\Postsales
 * @author  Harald Deiser <h.deiser@techdivision.com>
 */
abstract class AbstractUpdated extends AbstractOutgoingMethod
{
    /** @var null|array */
    protected $rmaItems = null;

    /**
     * Send mcom message from type.
     *
     * @param $data
     * @param $type
     * @return mixed
     */
    protected function sendType($data, $type, $status = null)
    {
        $rma = $this->getRmaById($this->getRmaId($data));
        $orderItems = $this->getOrderItemsByOrderId($rma['order_id']);
        $rmaItems = $this->getRmaItemsByRmaId($this->getRmaId($data));

        // insert order data to updated template
        $method = $this->methodResolver->getMethodForServiceClass(get_class($this));
        $template = $this->templateHelper->getTemplateForMethod($method);

        $rma['date'] = date('c');

        if ($status) {
            $rma['status'] = $status;
        }

        // insert order data
        foreach ($rma as $key => $value) {
            $template = str_replace(sprintf('{{rma.%s}}', $key), $value, $template);
        }

        // insert order item data
        $updatedData = json_decode($template, true);

        $lines = [];

        foreach ($rmaItems as $rmaItem) {
            $lineTemplate = json_encode($updatedData[$type]['lines'], true);

            foreach ($rmaItem as $key => $value) {
                $lineTemplate = str_replace(sprintf('{{rma_item.%s}}', $key), $value, $lineTemplate);
            }
            foreach ($orderItems as $orderItem) {
                if ($rmaItem['line_number'] == $orderItem['line_number']) {
                    $lineTemplate = str_replace(
                        sprintf('{{order_item.%s}}', 'id'), $orderItem['id'], $lineTemplate
                    );
                    continue;
                }
            }

            $lines = array_merge($lines, json_decode($lineTemplate, true));
        }

        $updatedData[$type]['lines'] = $lines;

        return $this->rpcClient->send($updatedData, $method);
    }

    /**
     * Get rma id from data array.
     *
     * @param $data
     * @return mixed
     */
    protected function getRmaId($data)
    {
        return $data['rma_id'];
    }

    /**
     * Get Rma by id.
     *
     * @param string $rmaId
     * @return array
     */
    protected function getRmaById($rmaId)
    {
        return $this->db->createQueryBuilder()
            ->select('*')
            ->from('`' . Rma::TABLE_NAME . '`')
            ->where('`' . Rma::ID_FIELD . '` = :rmaId')
            ->setParameter(':rmaId', $rmaId)
            ->execute()
            ->fetch();
    }

    /**
     * Get RmaItems by rma id.
     *
     * @param string $rmaId
     * @return array
     */
    protected function getRmaItemsByRmaId($rmaId)
    {
        if ($this->rmaItems == null) {
            $this->rmaItems = $this->db->createQueryBuilder()
                ->select('*')
                ->from('`' . Item::TABLE_NAME . '`')
                ->where('`' . Item::RMA_ID_FIELD . '` = :rmaId')
                ->setParameter(':rmaId', $rmaId)
                ->execute()
                ->fetchAll();
        }

        return $this->rmaItems;
    }

    /**
     * Get order items.
     *
     * @param $orderId
     * @return array
     */
    protected function getOrderItemsByOrderId($orderId)
    {
        return $this->db->createQueryBuilder()
            ->select('*')
            ->from('`' . OrderItem::TABLE_NAME . '`')
            ->where('`' . OrderItem::ORDER_ID_FIELD . '` = :orderId')
            ->setParameter(':orderId', $orderId)
            ->execute()
            ->fetchAll();
    }

    /**
     * Set the rma status.
     *
     * @param $status
     */
    protected function setRmaCompleteStatus($rmaId)
    {
        $queryBuilder = $this->db->createQueryBuilder();

        $queryBuilder->update('`' . Rma::TABLE_NAME . '`', 'ri')
            ->set('ri.status', ':status')
            ->setParameter(':status', Rma::STATUS_COMPLETE)
            ->where('`' . Rma::ID_FIELD . '` = :rma_id')
            ->setParameter(':rma_id', $rmaId)
            ->execute();
    }

    /**
     * Set rma item status.
     *
     * @param $id
     * @param $status
     */
    protected function setRmaCompleteItemStatus($rmaItems)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $orComposite = new CompositeExpression(CompositeExpression::TYPE_OR);

        $i = 1;
        foreach ($rmaItems as $rmaItem) {
            $rmaItemId = $rmaItem['id'];
            $orComposite->add('`' . Rma::ID_FIELD . '` = :rma_item_id' . $i);
            $queryBuilder->setParameter('rma_item_id' . $i++, $rmaItemId);
        }

        $queryBuilder->update('`' . Item::TABLE_NAME . '`', 'ri')
            ->set('ri.status', ':status')
            ->setParameter(':status', Item::STATUS_COMPLETE)
            ->where($orComposite)
            ->execute();
    }
}