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
 * Class Source
 * @package MomMock\Entity
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
class Source extends AbstractEntity
{
    /**
     * Holds the table name
     */
    const TABLE_NAME = 'source';

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
     * Saves a source
     *
     * @return string
     */
    public function save()
    {
        $this->db->createQueryBuilder()
            ->insert(sprintf("`%s`", self::TABLE_NAME))
            ->values([
                '`source_id`' => "'{$this->data['source_id']}'",
                '`aggregate_id`' => "'{$this->data['aggregate_id']}'"
            ])
            ->execute();

        return $this->db->lastInsertId();
    }
}