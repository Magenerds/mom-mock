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

namespace MomMock\Entity\Order;

use MomMock\Entity\AbstractEntity;

/**
 * Class Item
 * @package MomMock\Entity\Order
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
class Item extends AbstractEntity
{
    /**
     * Item status
     */
    const STATUS_NEW = 'new';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Holds the table name
     */
    const TABLE_NAME = 'order_item';

    /**
     * @var []
     */
    private $data;

    /**
     * @param [] $data
     */
    public function setData($orderId, array $data)
    {
        $this->data = $data;
        $this->data['order_id'] = $orderId;

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
                '`order_id`' => "'{$this->data['order_id']}'",
                '`id`' => "'{$this->data['id']}'",
                '`line_number`' => "'{$this->data['line_number']}'",
                '`product_type`' => "'{$this->data['product_type']}'",
                '`sku`' => "'{$this->data['sku']}'",
                '`product_name`' => "'{$this->data['product_name']}'",
                '`image_url`' => "'{$this->data['image_url']}'",
                '`status`' => "'{$this->data['status']}'",
                '`net_amount`' => "'{$this->data['amount']['net_amount']}'",
                '`gross_amount`' => "'{$this->data['amount']['gross_amount']}'",
                '`taxes_amount`' => "'{$this->data['amount']['taxes'][0]['amount']}'",
                '`taxes_rate`' => "'{$this->data['amount']['taxes'][0]['rate']}'"
            ])
            ->execute();

        return $this->db->lastInsertId();
    }
}