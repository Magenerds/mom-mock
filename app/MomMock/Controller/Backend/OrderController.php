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

use MomMock\Entity\Order\Item;
use MomMock\Entity\Rma;
use MomMock\Entity\Order;
use MomMock\Entity\Rma\Item as RmaItem;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class OrderController
 * @package MomMock\Controller\Backend
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
class OrderController extends AbstractBackendController
{
    /**
     * Order list action
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function listAction(Request $request, Response $response)
    {
        $db = $this->getDb();

        $orders = $db->createQueryBuilder()
            ->select('*')
            ->from('`' . Order::TABLE_NAME . '`')
            ->execute()
            ->fetchAll();

        $templ = $this->getTemplateEngine();

        $response->write($templ->render(
            'order/list.twig',
            ['orders' => $orders]
        ));

        return $response;
    }

    /**
     * Order detail action
     *
     * @param Request $request
     * @param Response $response
     * @param $params
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function detailAction(Request $request, Response $response, $params)
    {
        $id = 0;
        if (isset($params['id'])) {
            $id = $params['id'];
        }

        $templ = $this->getTemplateEngine();

        $response->write($templ->render(
            'order/detail.twig',
            [
                'order' => $this->getOrderDetails($id),
                'returns' => $this->getReturnDetails($id)
            ]
        ));

        return $response;
    }

    /**
     * Get order details by id.
     *
     * @param $id
     * @return array
     */
    private function getOrderDetails($id)
    {
        $db = $this->getDb();
        $general = $db->createQueryBuilder()
            ->select('*')
            ->from('`' . Order::TABLE_NAME . '`')
            ->where('`id` = ?')
            ->setParameter(0, $id)
            ->execute()
            ->fetch();

        $items = $db->createQueryBuilder()
            ->select('*')
            ->from('`' . Item::TABLE_NAME . '`')
            ->where('`order_id` = ?')
            ->setParameter(0, $id)
            ->execute()
            ->fetchAll();

        return ['general' => $general, 'items' => $items];
    }

    /**
     * @param $id
     * @return array
     */
    private function getReturnDetails($orderId)
    {
        $db = $this->getDb();
        $result = [];
        $general = $db->createQueryBuilder()
            ->select('*')
            ->from('`' . Rma::TABLE_NAME . '`')
            ->where('`order_id` = ?')
            ->setParameter(0, $orderId)
            ->execute()
            ->fetchAll();

        foreach($general as $return) {
            $id = $return['id'];

            $items = $db->createQueryBuilder()
                ->select('*')
                ->from('`' . RmaItem::TABLE_NAME . '`')
                ->where('`'. RmaItem::RMA_ID_FIELD . '` = ?')
                ->setParameter(0, $id)
                ->execute()
                ->fetchAll();

            $result[] = ['general' => $return, 'items' => $items];
        }

        return $result;
    }
}