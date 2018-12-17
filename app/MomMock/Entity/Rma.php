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

namespace MomMock\Entity;

/**
 * Class Rma
 *
 * @package MomMock\Entity
 * @author  Harald Deiser <h.deiser@techdivision.com>
 */
class Rma extends AbstractEntity
{
    /**
     * Rma status
     */
    const STATUS_REQUESTED = 'requested';
    const STATUS_COMPLETE = 'approved';
    const STATUS_CANCELLED = 'cancelled';

    const RMA_PREFIX = 'RMA-CS-';

    /**
     * Holds the table name
     */
    const TABLE_NAME = 'rma';

    /**
     * Id field.
     */
    const ID_FIELD = 'id';

    /**
     * @var []
     */
    private $data;

    /**
     * @param [] $data
     * @return Rma
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Saves a rma
     *
     * @return string
     */
    public function save()
    {
        $rmaId = self::RMA_PREFIX . $this->data['increment_id'] . '-' . $this->getRmaCount($this->data['increment_id']);
        $this->db->createQueryBuilder()
            ->insert(sprintf("`%s`", self::TABLE_NAME))
            ->values([
                '`order_id`' => "'{$this->data['order_id']}'",
                '`increment_id`' => "'{$this->data['increment_id']}'",
                '`rma_id`' => "'{$rmaId}'",
                '`source`' => "'{$this->data['source_id']}'",
                '`sales_channel`' => "'{$this->data['sales_channel_id']}'",
                '`status`' => "'{$this->data['status']}'",
                '`tracking_number`' => "'{$this->data['return_tracking_number']}'",
                '`carrier`' => "'{$this->data['return_tracking_carrier']}'",
                '`label`' => "'{$this->data['return_tracking_label_url']}'",
                '`credit_note`' => "'{$this->data['credit_note']}'"
            ])
            ->execute();

        return $this->db->lastInsertId();
    }

    /**
     * Get Rma Count.
     *
     * @param $orderId
     * @return int
     */
    protected function getRmaCount($orderId)
    {
        $rmas = $this->db->createQueryBuilder()
            ->select('count(*)')
            ->from(sprintf("`%s`", self::TABLE_NAME))
            ->where('order_id = :orderId')
            ->setParameter(':orderId', $orderId)
            ->execute()
            ->fetchColumn();

        return $rmas + 1;
    }
}