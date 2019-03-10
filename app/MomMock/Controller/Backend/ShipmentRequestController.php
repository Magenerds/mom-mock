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

namespace MomMock\Controller\Backend;

use Slim\Http\Request;
use Slim\Http\Response;
use MomMock\Method\Logistics\WarehouseManagement\RequestShipment;

/**
 * Class ShipmentRequestController
 * @package MomMock\Controller\Backend
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
class ShipmentRequestController extends AbstractBackendController
{
    /**
     * Requests a shipment
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function requestShipmentAction(Request $request, Response $response)
    {
        $params = $request->getQueryParams();

        if (empty($params['order_id']) || empty($params['order_item_ids']) || empty($params['source_id'])) {
            return $response->withStatus(404, 'No data was given');
        }

        $customerShipmentDone = new RequestShipment(
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