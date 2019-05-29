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
use MomMock\Entity\Product;
use MomMock\Entity\Inventory;
use MomMock\Entity\Source;

/**
 * Class InventoryController
 * @package MomMock\Controller\Backend
 * @author  Mahmood Dhia <m.dhia@techdivision.com>
 */
class InventoryController extends AbstractBackendController
{
    /**
     * Adds inventory
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function addInventoryAction(Request $request, Response $response)
    {
        $params = $request->getQueryParams();
        $productId = null;

        if (empty($params['qty'])) {
            return $response->withStatus(400, 'Please specify quantity');
        }

        if (!empty($params['product_id'])) {
            $productId = $params['product_id'];
        }

        $sources = $this->getSourceIds();
        $products = $this->getProducts($productId);

        $inventoryData = [];
        $qty = $params['qty'];
        if ($qty < 0) {
            $qty = 0;
        }

        foreach ($sources as $source) {
            foreach ($products as $product) {
                if ($params['qty'] == 'random') {
                    $qty = rand(0, 100);
                }

                $inventoryData[] = [
                    'sku' => $product,
                    'source_id' => $source,
                    'qty' => $qty
                ];
            }
        }

        $inventory = new Inventory($this->getDb());

        $inventory->delete($products);
        $inventory->setData($inventoryData)->save();
    }

    /**
     * Returns all source ids
     *
     * @return array
     */
    private function getSourceIds()
    {
        $sourcesData = $this->getDb()->createQueryBuilder()
            ->select('`id`')
            ->from(Source::TABLE_NAME)
            ->execute()
            ->fetchAll();

        $sources = [];
        foreach ($sourcesData as $data) {
            $sources[] = $data['id'];
        }

        return $sources;
    }

    /**
     * Returns all child products or simple products
     *
     * @return array
     */
    private function getProducts($productId = null)
    {
        if ($productId) {
            $productsData = $this->getDb()->createQueryBuilder()
                ->select('`child_sku` as sku')
                ->from('`' .Product::TABLE_NAME . '`', 'p')
                ->leftJoin('p', Product::TABLE_CHILD_NAME, 'c', 'c.product_id = p.id')
                ->where('c.product_id = ?')
                ->setParameter(0, $productId)
                ->execute()
                ->fetchAll();

            if (count($productsData) == 0) {
                $productsData = $this->getDb()->createQueryBuilder()
                    ->select('`sku`')
                    ->from('`' .Product::TABLE_NAME . '`', 'p')
                    ->leftJoin('p', Product::TABLE_CHILD_NAME, 'c', 'c.product_id = p.id')
                    ->where('p.id = ?')
                    ->setParameter(0, $productId)
                    ->execute()
                    ->fetchAll();
            }
        } else {
            $productsData = $this->getDb()->createQueryBuilder()
                ->select('`sku`, `product_id`')
                ->from('`' .Product::TABLE_NAME . '`', 'p')
                ->leftJoin('p', Product::TABLE_CHILD_NAME, 'c', 'c.product_id = p.id')
                ->where('c.product_id IS NULL')
                ->execute()
                ->fetchAll();
        }

        $products = [];
        foreach ($productsData as $data) {
            $products[] = $data['sku'];
        }

        return $products;
    }
}