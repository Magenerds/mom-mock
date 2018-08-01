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

use Slim\Http\Request;
use Slim\Http\Response;
use MomMock\Method\Logistics\FulfillmentManagement\CustomerShipmentDone;

/**
 * Class ShipmentController
 * @package MomMock\Controller\Backend
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
class ShipmentController extends AbstractBackendController
{
    /**
     * Create a shipment and its customer shipment done message
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function createShipmentAction(Request $request, Response $response)
    {
        $params = $request->getQueryParams();

        if (empty($params['order_id']) || empty($params['order_item_ids'])) {
            return $response->withStatus(404, 'No shipment data was given');
        }

        $customerShipmentDone = new CustomerShipmentDone(
            $this->getDb(),
            $this->getMethodResolver(),
            $this->getTemplateHelper(),
            $this->getRpcClient()
        );

        try {
            $result = $customerShipmentDone->send($params);
        } catch (\Exception $e) {
            return $response->withStatus(500, $e->getMessage());
        }

        return $response->write($result);
    }
}