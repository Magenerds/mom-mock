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

namespace MomMock\Method\Inventory\AggregateStockManagement;

use MomMock\Method\AbstractOutgoingMethod;
use Doctrine\DBAL\Connection;
use MomMock\Entity\Inventory;

/**
 * Class Updated
 * @package MomMock\Method\Inventory\AggregateStockManagement
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
class Updated extends AbstractOutgoingMethod
{
    /**
     * @inheritdoc
     */
    public function send($data)
    {
        $sourceIds = [];
        foreach ($data['sources'] as $source) {
            $sourceIds[] = $source['id'];
        }

        $queryBuilder = $this->db->createQueryBuilder();

        // if qty is given overwrite it instead using the real source's qty
        if (!empty($data['qty'])) {
            $qty = intval($data['qty']);
            $queryBuilder->select($qty . ' as quantity, sku');
        } else {
            $queryBuilder->select('SUM(qty) as quantity, sku');
        }

        $inventory = $queryBuilder->from('`' . Inventory::TABLE_NAME . '`')
            ->where('`source_id` IN (:source_ids)')
            ->setParameter('source_ids', $sourceIds, Connection::PARAM_INT_ARRAY)
            ->groupBy('sku')
            ->execute()
            ->fetchAll();

        // insert stock data to stock snapshot template
        $method = $this->methodResolver->getMethodForServiceClass(get_class($this));
        $template = $this->templateHelper->getTemplateForMethod($method);

        $stockSnapshot = json_decode($template, true);
        $stockSnapshot['snapshot']['aggregate_id'] = $data['aggregate_name'];
        $date = new \DateTime();
        $stockSnapshot['snapshot']['created_on'] = $date->format('Y-m-d H:i:s');
        $stockSnapshot['snapshot']['mode'] = strtoupper($data['mode']);
        $stockSnapshot['snapshot']['stock'] = $inventory;

        $result = $this->rpcClient->send($stockSnapshot, $method);

        return $result;
    }
}