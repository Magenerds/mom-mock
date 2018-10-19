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

namespace MomMock\Method\Catalog\ProductManagement;

use MomMock\Entity\Product;
use MomMock\Method\AbstractIncomingMethod;

/**
 * Class Updated
 * @package MomMock\Method\Catalog\ProductManagement
 * @author  Mahmood Dhia <m.dhia@techdivision.com>
 */
class Updated extends AbstractIncomingMethod
{
    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function handleRequestData($data)
    {
        if (!isset($data['params']) || !isset($data['params']['product'])) {
            throw new \Exception('No product data was given');
        }

        $this->importProduct($data['params']['product']);

        return [];
    }

    /**
     * Import product
     *
     * @param $productData
     */
    public function importProduct($productData)
    {
        $product = new Product($this->db);

        // Try to load product by SKU
        $product->loadBySku($productData['sku']);

        // Set product data
        $product->setType($productData['type']);
        $product->setSku($productData['sku']);
        $product->setName($productData['name'][0]['value']);
        $product->setEnabled($productData['enabled']);

        //<editor-fold desc="Clear child relation data">
        $product->setChildren([]);

        if ($product->getId()) {
            $this->db->createQueryBuilder()
                ->delete(sprintf("`%s`", Product::TABLE_CHILD_NAME))
                ->where('`product_id` = :id')
                ->setParameter(':id', $product->getId())
                ->execute();
        }
        //</editor-fold>

        //<editor-fold desc="Parse product attributes">
        $attributes = [];
        foreach ($productData['custom_attributes'] as $attributeData) {
            $attributes[$attributeData['attribute_code']] = $attributeData['value'];
        }

        $product->setAttributes($attributes);
        //</editor-fold>

        // Save product
        $product->save();

        // Save product children relation
        foreach ($productData['children_skus'] as $sku) {
            $this->db->createQueryBuilder()
                ->insert(sprintf("`%s`", Product::TABLE_CHILD_NAME))
                ->setValue('product_id', ':product_id')
                ->setValue('child_sku', ':child_sku')
                ->setParameter(':product_id', $product->getId())
                ->setParameter(':child_sku', $sku)
                ->execute();
        }
    }
}
