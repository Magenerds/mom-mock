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

namespace MomMock\Entity\Rma;

use MomMock\Entity\AbstractEntity;

/**
 * Class Item
 *
 * @package MomMock\Entity\Rma
 * @author  Harald Deiser <h.deiser@techdivision.com>
 */
class Item extends AbstractEntity
{
    /**
     * Item status
     */
    const STATUS_PENDING = 'requested';
    const STATUS_COMPLETE = 'approved';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Holds the table name
     */
    const TABLE_NAME = 'rma_item';

    /**
     * Id field.
     */
    const RMA_ID_FIELD = 'rma_id';

    /**
     * @var []
     */
    private $data;

    /**
     * @param [] $data
     * @return Item
     */
    public function setData($orderId, array $data)
    {
        $this->data = $data;
        $this->data[self::RMA_ID_FIELD] = $orderId;

        return $this;
    }

    /**
     * Saves an order item
     *
     * @return string
     */
    public function save()
    {
        $this->db->createQueryBuilder()
            ->insert(sprintf("`%s`", self::TABLE_NAME))
            ->values([
                '`' . self::RMA_ID_FIELD . '`' => "'{$this->data[self::RMA_ID_FIELD]}'",
                '`line_number`' => "'{$this->data['line_number']}'",
                '`sku`' => "'{$this->data['sku']}'",
                '`product_name`' => "'{$this->data['product_name']}'",
                '`status`' => "'{$this->data['status']}'",
                '`reason`' => "'{$this->data['reason']}'",
                '`reason_description`' => "'{$this->data['reason_description']}'",
                '`base_condition`' => "'{$this->data['condition']}'",
                '`condition_description`' => "'{$this->data['condition_description']}'",
                '`net_amount`' => "{$this->data['net_amount']}",
                '`gross_amount`' => "{$this->data['gross_amount']}",
                '`taxes_amount`' => "{$this->data['taxes_amount']}",
                '`taxes_rate`' => "{$this->data['taxes_rate']}"
            ])
            ->execute();

        return $this->db->lastInsertId();
    }
}