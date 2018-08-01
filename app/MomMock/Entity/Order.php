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
 * Class Order
 * @package MomMock\Entity
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
class Order extends AbstractEntity
{
    /**
     * Item status
     */
    const STATUS_NEW = 'new';
    const STATUS_COMPLETE = 'complete';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Holds the table name
     */
    const TABLE_NAME = 'order';

    /**
     * @var []
     */
    private $data;

    /**
     * @param [] $data
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Saves an order
     *
     * @return string
     */
    public function save()
    {
        $address = $this->data['addresses'][0];

        $this->db->createQueryBuilder()
            ->insert(sprintf("`%s`", self::TABLE_NAME))
            ->values([
                '`increment_id`' => "'{$this->data['id']}'",
                '`store`' => "'{$this->data['store']}'",
                '`status`' => "'{$this->data['status']}'",
                '`status_reason`' => "'{$this->data['status_reason']}'",
                '`origin_date`' => "'{$this->data['origin_date']}'",
                '`address_type`' => "'{$address['address_type']}'",
                '`first_name`' => "'{$address['first_name']}'",
                '`last_name`' => "'{$address['last_name']}'",
                '`address1`' => "'{$address['address1']}'",
                '`city`' => "'{$address['city']}'",
                '`zip`' => "'{$address['zip']}'",
                '`country_code`' => "'{$address['country_code']}'",
                '`email`' => "'{$address['email']}'",
                '`segment`' => "'{$this->data['customer']['segment']}'",
                '`type`' => "'{$this->data['customer']['type']}'"
            ])
            ->execute();

        return $this->db->lastInsertId();
    }
}