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

namespace MomMock\Entity;

/**
 * Class Aggregate
 * @package MomMock\Entity
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
class Aggregate extends AbstractEntity
{
    /**
     * Holds the table name
     */
    const TABLE_NAME = 'aggregate';

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
     * Saves an aggregate
     *
     * @return string
     */
    public function save()
    {
        $this->db->createQueryBuilder()
            ->insert(sprintf("`%s`", self::TABLE_NAME))
            ->values([
                '`name`' => "'{$this->data['name']}'"
            ])
            ->execute();

        return $this->db->lastInsertId();
    }

    /**
     * Deletes an aggregate
     *
     * @param $id
     */
    public function delete($id)
    {
        $this->db->createQueryBuilder()
            ->delete(self::TABLE_NAME)
            ->where('`id` = ?')
            ->setParameter(0, $id)
            ->execute();
    }
}