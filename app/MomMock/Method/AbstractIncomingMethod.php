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

namespace MomMock\Method;

use Doctrine\DBAL\Connection;

/**
 * Class AbstractIncomingMethod
 * @package MomMock\Method
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
abstract class AbstractIncomingMethod
{
    /**
     * Connection
     */
    protected $db;

    /**
     * Handle the incoming request and its data
     *
     * @param $data
     * @return mixed
     */
    abstract public function handleRequestData($data);

    /**
     * @inheritdoc
     */
    public function setDb(Connection $db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDb()
    {
        return $this->db;
    }
}