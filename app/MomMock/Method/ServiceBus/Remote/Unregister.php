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

namespace MomMock\Method\ServiceBus\Remote;

use MomMock\Method\AbstractIncomingMethod;

/**
 * Class Unregister
 * @package MomMock\Method\ServiceBus\Remote
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
class Unregister extends AbstractIncomingMethod
{
    /**
     * @inheritdoc
     */
    public function handleRequestData($data)
    {
        if (!isset($data['params'])) {
            throw new \Exception('No integration data was given');
        }

        $integration = $data['params'];

        $this->deleteIntegration($integration);

        return [];
    }

    /**
     * @param $integrationData
     * @return string
     */
    protected function deleteIntegration($integrationData)
    {
        $this->db->createQueryBuilder()
            ->delete('`integration`')
            ->where('`id` = ?')
            ->setParameter(0, $integrationData['id'])
            ->execute();
    }
}