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

namespace MomMock\Method\Logistics\WarehouseManagement;

use MomMock\Method\AbstractIncomingMethod;
use MomMock\Entity\Order;
use MomMock\Entity\Order\Item;

/**
 * Class LinesShipped
 * @package MomMock\Method\Sales\OrderManagement
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
class LinesShipped extends AbstractIncomingMethod
{
    /**
     * @inheritdoc
     */
    public function handleRequestData($data)
    {
        if (!isset($data['params'])) {
            throw new \Exception('No line data was given');
        }

        // Todo: Implement handling of lines shipped

        return [];
    }
}