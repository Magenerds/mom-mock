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

use Doctrine\DBAL\Connection;

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
        $queryBuilder = $this->db->createQueryBuilder()
            ->insert(sprintf("`%s`", self::TABLE_NAME));

        foreach ($this->data as $entry) {
            $queryBuilder->values([
                '`sku`' => "'{$entry['sku']}'",
                '`source_id`' => "'{$entry['source_id']}'",
                '`qty`' => "'{$entry['qty']}'",
            ])->execute();
        }

        return $this->db->lastInsertId();
    }

    /**
     * Deletes all entries
     *
     * @param $skus
     */
    public function delete($skus = null)
    {
        $querybuilder = $this->db->createQueryBuilder()
            ->delete('`' . self::TABLE_NAME . '`');

        if ($skus) {
            $querybuilder->where('`sku` IN (:skus)')
                ->setParameter('skus', $skus, Connection::PARAM_INT_ARRAY);
        }

        $querybuilder->execute();
    }
}