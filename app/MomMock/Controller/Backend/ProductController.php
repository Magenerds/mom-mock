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
 * Class ProductController
 * @package MomMock\Controller\Backend
 * @author  Mahmood Dhia <m.dhia@techdivision.com>
 */
class ProductController extends AbstractBackendController
{
    /**
     * Product list action
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function indexAction(Request $request, Response $response)
    {
        $subSelect = $this->getDb()->createQueryBuilder()
            ->select('`child_sku`')
            ->from('`' . Product::TABLE_CHILD_NAME . '`', 'c');

        $products = $this->getDb()->createQueryBuilder()
            ->select('`sku`, `id`, `type`, `name`')
            ->from('`' . Product::TABLE_NAME . '`', 'p')
            ->where($this->getDb()->createQueryBuilder()->expr()->notIn('p.sku', $subSelect->getSQL()))
            ->setMaxResults(20)
            ->execute()
            ->fetchAll();

        $templ = $this->getTemplateEngine();

        $response->write($templ->render(
            'product/index.twig',
            ['products' => $products]
        ));

        return $response;
    }

    /**
     * Product detail action
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

        $product = $this->getDb()->createQueryBuilder()
            ->select('*')
            ->from('`' . Product::TABLE_NAME . '`')
            ->where('`id` = ?')
            ->setParameter(0, $id)
            ->execute()
            ->fetch();

        $children = $this->getDb()->createQueryBuilder()
            ->select('`sku`, `id`, `type`, `name`')
            ->from('`' . Product::TABLE_NAME . '`', 'p')
            ->rightJoin('p', Product::TABLE_CHILD_NAME, 'c', 'p.sku = c.child_sku')
            ->where('product_id = ?')
            ->setParameter(0, $id)
            ->execute()
            ->fetchAll();

        // handle inventory dependent on existing child products
        if (count($children) == 0) {
            $product = $this->getInventoryForProducts([$product]);
            $product = array_shift($product);
        } else {
            $children = $this->getInventoryForProducts($children);
        }

        $templ = $this->getTemplateEngine();

        $response->write($templ->render(
            'product/detail.twig',
            ['product' => $product, 'children' => $children]
        ));

        return $response;
    }

    /**
     * Gets the inventory for given products
     *
     * @param $products
     * @return mixed
     */
    private function getInventoryForProducts($products)
    {
        foreach ($products as &$product) {
            $sku = $product['sku'];

            $inventory = $this->getDb()->createQueryBuilder()
                ->select('`i`.`qty`')
                ->addSelect('`s`.`source_id`')
                ->from('`' . Inventory::TABLE_NAME . '`', 'i')
                ->rightJoin('i', Source::TABLE_NAME, 's', 'i.source_id = s.id')
                ->where('`sku` = ?')
                ->orderBy('`s`.`id`')
                ->setParameter(0, $sku)
                ->execute()
                ->fetchAll();

            $product['inventory'] = $inventory;
        }

        return $products;
    }
}