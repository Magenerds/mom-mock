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
     * @param $data
     */
    public function logRequest($data, $status, $direction, $to, $resultMessage)
    {
        $this->setData([
            'delivery_id' => $data['id'],
            'status' => $status,
            'topic' => $data['method'],
            'body' => json_encode($data['params']),
            'sent_at'=> date('Y-m-d H:i:s'),
            'direction' => $direction,
            'to' => $to,
            'result' => $resultMessage
        ]);
        $this->save();
    }

    /**
     * Saves an order item
     *
     * @return string
     */
    public function save()
    {
        $queryBuilder = $this->db->createQueryBuilder();

        $queryBuilder->insert(self::TABLE_NAME);

        foreach (['delivery_id', 'status', 'topic', 'body', 'sent_at', 'direction', 'to', 'result'] as $field) {
            $value = isset($this->data[$field]) ? $this->data[$field] : null;
            $queryBuilder->setValue("`$field`", $queryBuilder->expr()->literal($value));
        }

        $queryBuilder->execute();

        return $this->db->lastInsertId();
    }
}