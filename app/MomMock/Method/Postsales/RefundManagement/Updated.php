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
namespace MomMock\Method\Postsales\RefundManagement;

use MomMock\Method\Postsales\AbstractUpdated;

/**
 * Class Updated
 *
 * @package MomMock\Method\Postsales\RefundManagement
 * @author  Harald Deiser <h.deiser@techdivision.com>
 */
class Updated extends AbstractUpdated
{
    /**
     * Define refund types.
     */
    const REFUND_TYPE_CANCELLED = 'CANCELLED';
    const REFUND_TYPE_RETURN = 'return';

    /**
     * Send data to registered integrations
     *
     * @param $data
     * @return mixed
     */
    public function send($data)
    {
        return $this->sendType($data, 'refund');
    }
}