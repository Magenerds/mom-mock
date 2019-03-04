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
use MomMock\Entity\Package;
use MomMock\Entity\Rma;
use MomMock\Entity\Order;
use MomMock\Entity\Rma\Item as RmaItem;
use MomMock\Entity\Source;
use Slim\Http\Request;
use Slim\Http\Response;

use function array_map as map;
use function array_reduce as reduce;
use function array_filter as filter;
use function array_values as values;

/**
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
    public function listAction(Request $request, Response $response): Response
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
    public function detailAction(Request $request, Response $response, $params): Response
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
                'returns' => $this->getReturnDetails($id),
                'sources' => $this->getSources()
            ]
        ));

        return $response;
    }
    
    /**
     * Get order details by id.
     *
     * @param int|string $id
     * @return array[]
     */
    private function getOrderDetails($id): array
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

        return ['general' => $general, 'items' => $this->mergeShippingPackageData($items)];
    }

    /**
     * @param mixed[] $item
     * @param array[] $packages
     * @return array[]
     */
    private function filterPackagesForItem(array $item, array $packages): array
    {
        $packagesForItem = filter($packages, function (array $package) use ($item) {
            return $package['order_item_id'] === $item['id'];
        });
        $packagesGroupedByTrackingNumber = values(reduce($packagesForItem, function (array $acc, array $package) {
            $acc[$package['tracking_number']] = $package;
            return $acc;
        }, []));

        return $packagesGroupedByTrackingNumber;
    }

    /**
     * @param int|string $orderId
     * @return array[]
     */
    private function getReturnDetails($orderId): array
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

    /**
     * Returns all available sources
     *
     * @return array
     */
    private function getSources()
    {
        return $this->getDb()->createQueryBuilder()
            ->select('*')
            ->from('`' . Source::TABLE_NAME . '`')
            ->execute()
            ->fetchAll();
    }

    /**
     * @param array[] $items
     * @return int[]
     */
    private function nonShippingOrderItemIds(array $items): array
    {
        $orderItemIds = map(function (array $item) {
            return $item['id'];
        }, $items);
        $nonShippingOrderItemIds = filter($orderItemIds, function ($orderItemId) {
            return $orderItemId !== 'SHIPPING';
        });

        return values(map('intval', $nonShippingOrderItemIds));
    }

    /**
     * @param array[] $items
     * @return array[]
     */
    private function mergeShippingPackageData(array $items): array
    {
        $packages = Package::fetchForOrderItemIds($this->getDb(), ...$this->nonShippingOrderItemIds($items));

        return map(function (array $item) use ($packages) {
            $item['packages'] = $this->filterPackagesForItem($item, $packages);
            return $item;
        }, $items);
    }
}
