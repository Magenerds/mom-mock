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
namespace MomMock\Entity\Journal;
use MomMock\Entity\AbstractEntity;
/**
 * Class Item
 * @package MomMock\Entity\Journal
 * @author  Mahmood Dhia <m.dhia@techdivision.com>
 */
class Request extends AbstractEntity
{
    /**
     * Request status
     */
    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_IGNORED = 'IGNORED';
    const STATUS_ERROR = 'ERROR';

    /**
     * Holds message direction
     */
    const DIRECTION_INCOMING = 'incoming';
    const DIRECTION_OUTGOING = 'outgoing';

    /**
     * Holds protocol name
     */
    const PROTOCOL = 'Service Bus (HTTP)';

    /**
     * Holds the target name for oms
     */
    const OMS_TARGET = 'oms';

    /**
     * Holds the table name
     */
    const TABLE_NAME = 'journal';

    /**
     * @var []
     */
    private $data;

    /**
     * @param [] $data
     * @return Request
     */
    public function setData(array $data)
    {
        $this->data = $data;

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
            ->insert(self::TABLE_NAME)
            ->setValue('id', sprintf('"%s"', (isset($this->data['id']) ? $this->data['id'] : null)))
            ->setValue('delivery_id', sprintf('"%s"', (isset($this->data['delivery_id']) ? $this->data['delivery_id'] : null)))
            ->setValue('status', sprintf('"%s"', (isset($this->data['status']) ? $this->data['status'] : null)))
            ->setValue('topic', sprintf('"%s"', (isset($this->data['status']) ? $this->data['status'] : null)))
            ->setValue('body', sprintf('"%s"', (isset($this->data['status']) ? $this->data['status'] : null)))
            ->setValue('sent_at', sprintf('"%s"', (isset($this->data['status']) ? $this->data['status'] : null)))
            ->setValue('retried_at', sprintf('"%s"', (isset($this->data['retried_at']) ? $this->data['retried_at'] : null)))
            ->setValue('tries', sprintf('"%s"', (isset($this->data['tries']) ? $this->data['tries'] : 0)))
            ->setValue('direction', sprintf('"%s"', (isset($this->data['status']) ? $this->data['status'] : null)))
            ->setValue('to', sprintf('"%s"', (isset($this->data['status']) ? $this->data['status'] : null)))
            ->setValue('protocol', self::PROTOCOL)
            ->execute();
        return $this->db->lastInsertId();
    }
}