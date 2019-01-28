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
namespace MomMock\Method\Postsales\ReturnManagement;

use MomMock\Entity\Rma;
use MomMock\Method\Postsales\AbstractUpdated;

/**
 * Class Updated
 *
 * @package MomMock\Method\Postsales\ReturnManagement
 * @author  Harald Deiser <h.deiser@techdivision.com>
 */
class Updated extends AbstractUpdated
{

    /**
     * Send data to registered integrations
     *
     * @param $data
     * @return mixed
     */
    public function send($data)
    {
        $status = $data['status'];
        unset($data['status']);

        $result = $this->sendType($data, 'return', $status);

        if ($status == strtoupper(Rma::STATUS_COMPLETE)) {
            $this->setRmaCompleteStatus($this->getRmaId($data));
            $this->setRmaCompleteItemStatus($this->getRmaItemsByRmaId($this->getRmaId($data)));
        }

        return $result;
    }
}
