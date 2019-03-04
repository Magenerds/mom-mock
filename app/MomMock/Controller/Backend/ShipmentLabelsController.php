<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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

        if (empty($params['order_id']) || empty($params['order_item_ids']) || empty($params['source_id'])) {
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
