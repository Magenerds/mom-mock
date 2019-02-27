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
use MomMock\Entity\Source;
use MomMock\Entity\Aggregate;
use MomMock\Method\Inventory\AggregateStockManagement\Updated;

/**
 * Class StocksnapshotController
 * @package MomMock\Controller\Backend
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
class StocksnapshotController extends AbstractBackendController
{
    /**
     * Sends a stock snapshot for a given aggregate
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function sendSnapshotForAggregateAction(Request $request, Response $response)
    {
        $params = $request->getQueryParams();

        if (empty($params['id']) || empty($params['mode'])) {
            return $response->withStatus(400, 'Please specify aggregate id and mode');
        }

        $params['sources'] = $this->getDb()->createQueryBuilder()
            ->select('*')
            ->from('`' . Source::TABLE_NAME . '`')
            ->where('`aggregate_id` = ?')
            ->setParameter(0, $params['id'])
            ->execute()
            ->fetchAll();

        $aggregate = $this->getDb()->createQueryBuilder()
            ->select('`name`')
            ->from('`' . Aggregate::TABLE_NAME . '`')
            ->where('`id` = ?')
            ->setParameter(0, $params['id'])
            ->execute()
            ->fetch();

        $params['aggregate_name'] = $aggregate['name'];

        $updated = new Updated(
            $this->getDb(),
            $this->getMethodResolver(),
            $this->getTemplateHelper(),
            $this->getRpcClient()
        );

        try {
            $result = $updated->send($params);
        } catch (\Exception $e) {
            return $response->withStatus(500, $e->getMessage());
        }

        return $response->write($result);
    }
}