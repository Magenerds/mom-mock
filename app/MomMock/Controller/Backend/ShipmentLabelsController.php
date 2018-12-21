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

use MomMock\Method\Logistics\CarrierManagement\RequestShippingDetails;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * @package MomMock\Controller\Backend
 */
class ShipmentLabelsController extends AbstractBackendController
{
    /**
     * Request shipment labels for each package for the given order line items
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function requestShipmentLabelsAction(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();

        if (empty($params['order_id']) || empty($params['order_item_ids'])) {
            return $response->withStatus(404, 'No order id was specified given');
        }

        try {
            $requestShippingLabels = new RequestShippingDetails(
                $this->getDb(),
                $this->getMethodResolver(),
                $this->getTemplateHelper(),
                $this->getRpcClient()
            );

            return $response->write($requestShippingLabels->send($params));
        } catch (\Exception $e) {
            return $response->withStatus(500, $e->getMessage());
        }
    }
}
