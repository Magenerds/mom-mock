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
namespace MomMock\Method\Postsales;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use MomMock\Entity\Rma;
use MomMock\Entity\Rma\Item;
use MomMock\Method\AbstractOutgoingMethod;

/**
 * Class Updated
 * @author  Harald Deiser <h.deiser@techdivision.com>
 */
abstract class AbstractUpdated extends AbstractOutgoingMethod
{
    /**
     * Get Rma by id.
     *
     * @param string $rmaId
     * @return array
     */
    protected function getRmaById($rmaId)
    {
        return $this->db->createQueryBuilder()
            ->select('*')
            ->from('`' . Rma::TABLE_NAME . '`')
            ->where('`' . Rma::ID_FIELD . '` = :rmaId')
            ->setParameter(':rmaId', $rmaId)
            ->execute()
            ->fetch();
    }

    /**
     * Get RmaItems by rma id.
     *
     * @param string $rmaId
     * @return array
     */
    protected function getRmaItemsByRmaId($rmaId)
    {
        return $this->db->createQueryBuilder()
            ->select('*')
            ->from('`' . Item::TABLE_NAME . '`')
            ->where('`' . Item::ID_FIELD . '` = :rmaId')
            ->setParameter(':rmaId', $rmaId)
            ->execute()
            ->fetchAll();
    }

    /**
     * Set the rma status.
     *
     * @param $status
     */
    protected function setRmaStatus($rmaId)
    {
        $queryBuilder = $this->db->createQueryBuilder();

        $queryBuilder->update('`' . Rma::TABLE_NAME . '`', 'ri')
            ->set('ri.status', ':status')
            ->setParameter(':status', Rma::STATUS_COMPLETE)
            ->where('`' . Rma::ID_FIELD . ' = :rma_id')
            ->setParameter(':rma_id', $rmaId)
            ->execute();
    }

    /**
     * Set rma item status.
     *
     * @param $id
     * @param $status
     */
    protected function setRmaItemStatus($rmaItems)
    {
        $queryBuilder = $this->db->createQueryBuilder();
        $orComposite = new CompositeExpression(CompositeExpression::TYPE_OR);

        $i = 1;
        foreach ($rmaItems as $rmaItem) {
            $rmaItemId = $rmaItem['id'];
            $orComposite->add('`' . Rma::ID_FIELD . '` = :rma_item_id' . $i);
            $queryBuilder->setParameter('rma_item_id' . $i++, $rmaItemId);
        }

        $queryBuilder->update('`' . Item::TABLE_NAME . '`', 'ri')
            ->set('ri.status', ':status')
            ->setParameter(':status', Item::STATUS_COMPLETE)
            ->where($orComposite)
            ->execute();
    }
}