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

namespace MomMock\Controller\Backend;

use MomMock\Entity\Rma;
use MomMock\Method\Postsales\ReturnManagement\Updated as ReturnUpdated;
use MomMock\Method\Postsales\RefundManagement\Updated as RefundUpdated;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class RmaController
 * @package MomMock\Controller\Backend
 * @author  Harald Deiser <h.deiser@techdivision.com>
 */
class RmaController extends AbstractBackendController
{
    /**
     * Create a rma and its message
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function approveRmaAction(Request $request, Response $response)
    {
        $params = $request->getQueryParams();

        if (empty($params['rma_id'])) {
            return $response->withStatus(404, 'Rma Id is missing.');
        }

        $returnUpdated = new ReturnUpdated(
            $this->getDb(),
            $this->getMethodResolver(),
            $this->getTemplateHelper(),
            $this->getRpcClient()
        );

        $refundUpdated = new RefundUpdated(
            $this->getDb(),
            $this->getMethodResolver(),
            $this->getTemplateHelper(),
            $this->getRpcClient()
        );

        try {
            $params['status'] = strtoupper(Rma::STATUS_COMPLETE);
            $resultReturn = $returnUpdated->send($params);
            $resultRefund = $refundUpdated->send($params);

            $result = json_encode(
                array_merge(
                    $this->getArray(json_decode($resultReturn)),
                    $this->getArray(json_decode($resultRefund))
                )
            );

        } catch (\Exception $e) {
            return $response->withStatus(500, $e->getMessage());
        }

        return $response->write($result);
    }

    /**
     * Get the value as array.
     *
     * @param mixed $value
     * @return array
     */
    protected function getArray($value)
    {
        if (is_array($value)) {
            return $value;
        }

        return [$value];
    }
}
