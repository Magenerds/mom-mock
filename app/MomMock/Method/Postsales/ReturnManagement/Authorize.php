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
namespace MomMock\Method\Postsales\ReturnManagement;

use MomMock\Entity\Order;
use MomMock\Entity\Order\Item as OrderItem;
use MomMock\Method\AbstractIncomingMethod;
use MomMock\Entity\Rma;
use MomMock\Entity\Rma\Item;
use MomMock\Method\Postsales\ReturnManagement\Updated;

/**
 * Class Authorize
 *
 * @package MomMock\Method\Postsales\ReturnManagement
 * @author  Harald Deiser <h.deiser@techdivision.com>
 */
class Authorize extends AbstractIncomingMethod
{
    /**
     * Constant for column name
     */
    const CREDIT_NOTE_COUNT_COLUMN = 'credit_note_counter';

    /**
     * Constant for credit note prefix.
     */
    const CREDIT_NOTE_PREFIX = 'CDE';

    /**
     * Handle the incoming request and its data
     *
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public function handleRequestData($data)
    {
        if (!isset($data['params']) || !isset($data['params']['return'])) {
            throw new \Exception('No rma data was given');
        }

        $return = $data['params']['return'];

        $returnId = $this->createReturn($return);

        foreach ($return['lines'] as $line) {
            $this->createReturnItem($returnId, $line, $return['order_id']);
        }

        $update = new Updated(
            $this->getDb(),
            $this->getMethodResolver(),
            $this->getTemplateHelper(),
            $this->getRestClient()
        );
        $data['rma_id'] = $returnId;
        $data['status'] = strtoupper(Rma::STATUS_REQUESTED);

        $update->send($data);

        return [];
    }

    /**
     * @param $returnData
     * @return string
     */
    protected function createReturn($returnData)
    {
        $return = new Rma($this->getDb());

        $returnData['increment_id'] = $returnData['order_id'];
        $returnData['order_id'] = $this->getOrderIdByIncrementId($returnData['order_id']);
        $returnData['status'] = Rma::STATUS_REQUESTED;
        $returnData['credit_note'] = self::CREDIT_NOTE_PREFIX . $this->getCNCount();

        return $return->setData($returnData)->save();
    }

    /**
     * @param $returnId
     * @param $itemData
     * @return string
     */
    protected function createReturnItem($returnId, $itemData, $incrementId)
    {
        $item = new Item($this->getDb());
        $itemData['status'] = Item::STATUS_PENDING;
        $this->addPriceInformation($itemData, $incrementId);

        if (in_array('id', $itemData)) {
            unset($itemData['id']);
        }

        return $item->setData($returnId, $itemData)->save();
    }

    /**
     * Get order id by increment id.
     *
     * @param $incrementId
     * @return mixed
     */
    protected function getOrderIdByIncrementId($incrementId)
    {
        return $this->getDb()->createQueryBuilder()
            ->select('id')
            ->from('`' . Order::TABLE_NAME . '`')
            ->where('`increment_id` = :incrementId')
            ->setParameter(':incrementId', $incrementId)
            ->execute()
            ->fetchColumn();
    }

    /**
     * Add prices to return.
     *
     * @param $itemData
     * @param $incrementId
     */
    protected function addPriceInformation(&$itemData, $incrementId)
    {
        $orderId = $this->db->createQueryBuilder()
            ->select('id')
            ->from('`' . Order::TABLE_NAME . '`')
            ->where('`increment_id` = :increment_id')
            ->setParameter(':increment_id', $incrementId)
            ->execute()
            ->fetchColumn();

        $orderItem = $this->db->createQueryBuilder()
            ->select('*')
            ->from('`' . OrderItem::TABLE_NAME . '`')
            ->where('`order_id` = :order_id AND `sku` = :sku')
            ->setParameter(':order_id', $orderId)
            ->setParameter(':sku', $itemData['sku'])
            ->execute()
            ->fetch();

        foreach(['product_name', 'net_amount', 'gross_amount', 'taxes_amount', 'taxes_rate'] as $field) {
            $itemData[$field] = $orderItem[$field];
        }
    }

    /**
     * Get credit note count.
     *
     * @return int
     */
    protected function getCNCount()
    {
        $incrementValue = $this->db->createQueryBuilder()
            ->select('value')
            ->from('flags')
            ->where('`name` = :name')
            ->setParameter(':name', self::CREDIT_NOTE_COUNT_COLUMN)
            ->execute()
            ->fetchColumn();

        $this->db->createQueryBuilder('count')
            ->update('flags', 'count')
            ->set('count.value', $incrementValue + 1)
            ->where('count.name = :count')
            ->setParameter(':count', self::CREDIT_NOTE_COUNT_COLUMN)
            ->execute();

        return $incrementValue;
    }
}
