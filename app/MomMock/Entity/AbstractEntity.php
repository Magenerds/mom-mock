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

use Doctrine\DBAL\Connection;

/**
 * Class AbstractEntity
 * @package MomMock\Entity
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
abstract class AbstractEntity
{
    /**
     * @var Connection
     */
    protected $db;

    /**
     * AbstractEntity constructor.
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }
}