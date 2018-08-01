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
     */
    public function listAction(Request $request, Response $response)
    {
        $db = $this->getDb();

        $orders = $db->createQueryBuilder()
            ->select('*')
            ->from('`order`')
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
     */
    public function detailAction(Request $request, Response $response, $params)
    {
        $db = $this->getDb();

        $id = 0;
        if (isset($params['id'])) {
            $id = $params['id'];
        }

        $order = $db->createQueryBuilder()
            ->select('*')
            ->from('`order`')
            ->where('`id` = ?')
            ->setParameter(0, $id)
            ->execute()
            ->fetch();

        $items = $db->createQueryBuilder()
            ->select('*')
            ->from('`order_item`')
            ->where('`order_id` = ?')
            ->setParameter(0, $id)
            ->execute()
            ->fetchAll();

        $templ = $this->getTemplateEngine();

        $response->write($templ->render(
            'order/detail.twig',
            [
                'order' => $order,
                'items' => $items
            ]
        ));

        return $response;
    }
}