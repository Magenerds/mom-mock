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

namespace MomMock\Method\ServiceBus\Remote;

use MomMock\Method\AbstractIncomingMethod;
use MomMock\Entity\Integration;

/**
 * Class Register
 * @package MomMock\Method\ServiceBus\Remote
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
class Register extends AbstractIncomingMethod
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

        $this->createIntegration($integration);

        return [];
    }

    /**
     * @param $integrationData
     * @return string
     */
    protected function createIntegration($integrationData)
    {
        $integration = new Integration($this->getDb());
        return $integration->setData($integrationData)->save();
    }
}