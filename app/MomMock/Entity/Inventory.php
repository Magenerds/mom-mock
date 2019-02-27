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
 * Class Inventory
 * @package MomMock\Entity
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
class Inventory extends AbstractEntity
{
    /**
     * Holds the table name
     */
    const TABLE_NAME = 'inventory';

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
     * Saves an inventory
     *
     * @return string
     */
    public function save()
    {
        return $this->db->lastInsertId();
    }
}