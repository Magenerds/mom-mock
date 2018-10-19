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

        $product = new Product($this->getDb());
        $product->importProduct($data['params']['product']);

        return [];
    }
}