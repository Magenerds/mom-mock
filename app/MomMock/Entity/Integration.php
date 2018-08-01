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
 * Class Integration
 * @package MomMock\Entity
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
class Integration extends AbstractEntity
{
    /**
     * Holds the table name
     */
    const TABLE_NAME = 'integration';

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $secret;

    /**
     * @param [] $data
     */
    public function setData(array $data)
    {
        $this->id = $data['id'];
        $this->url = $data['url'];
        $this->secret = $data['secret'];

        return $this;
    }

    /**
     * Saves an integration
     *
     * @return string
     */
    public function save()
    {
        $this->db->createQueryBuilder()
            ->insert(sprintf("`%s`", self::TABLE_NAME))
            ->values([
                '`id`' => "'{$this->id}'",
                '`url`' => "'{$this->url}'",
                '`secret`' => "'{$this->secret}'"
            ])
            ->execute();

        return $this->db->lastInsertId();
    }
}