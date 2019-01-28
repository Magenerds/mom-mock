<?php declare(strict_types=1);
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace MomMock\Entity;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\DBAL\Query\QueryBuilder;

use function array_map as map;

class Package extends AbstractEntity
{
    /**
     * Holds table names
     */
    const TABLE_NAME = 'shipping_package';
    const ORDER_ITEMS_LINK_TABLE_NAME = 'shipping_package_item';

    /** @var int */
    private $id;

    /** @var string */
    private $carrier;

    /** @var string */
    private $trackingNumber;

    /** @var string */
    private $trackingLink;

    /** @var string */
    private $trackingComment;

    /** @var string */
    private $shippingLabelLink;

    /** @var int[] */
    private $items = [];

    /**
     * @param Connection $db
     * @param mixed[] $data
     * @return Package
     */
    public static function createFromArray(Connection $db, array $data): Package
    {
        $package = new self($db);

        $package->setId((int) $data['id']);
        $package->setCarrier($data['carrier'] ?? '');
        $package->setTrackingNumber($data['tracking_number'] ?? '');
        $package->setTrackingLink($data['tracking_link'] ?? '');
        $package->setTrackingComment($data['tracking_comment'] ?? '');
        $package->setShippingLabelLink($data['shipping_label_link'] ?? '');
        $package->setItems($data['items']);

        return $package;
    }

    /**
     * @return string
     */
    public function getTrackingComment(): string
    {
        return $this->trackingComment;
    }

    /**
     * @param string $trackingComment
     */
    public function setTrackingComment(string $trackingComment): void
    {
        $this->trackingComment = $trackingComment;
    }

    /**
     * @return string
     */
    public function getShippingLabelLink(): string
    {
        return $this->shippingLabelLink;
    }

    /**
     * @param string $shippingLabelLink
     */
    public function setShippingLabelLink(string $shippingLabelLink): void
    {
        $this->shippingLabelLink = $shippingLabelLink;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param array $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getCarrier(): string
    {
        return $this->carrier;
    }

    /**
     * @param string $carrier
     */
    public function setCarrier(string $carrier): void
    {
        $this->carrier = $carrier;
    }

    /**
     * @return string
     */
    public function getTrackingNumber(): string
    {
        return $this->trackingNumber;
    }

    /**
     * @param string $trackingNumber
     */
    public function setTrackingNumber(string $trackingNumber): void
    {
        $this->trackingNumber = $trackingNumber;
    }

    /**
     * @return string
     */
    public function getTrackingLink(): string
    {
        return $this->trackingLink;
    }

    /**
     * @param string $trackingLink
     */
    public function setTrackingLink(string $trackingLink): void
    {
        $this->trackingLink = $trackingLink;
    }

    /**
     * @throws InvalidArgumentException
     * @throws ConnectionException
     */
    public function save(): void
    {
        $this->db->beginTransaction();
        $this->savePackage();
        $this->savePackageItems();
        $this->db->commit();
    }

    /**
     *
     */
    private function savePackage(): void
    {
        $queryBuilder = $this->db->createQueryBuilder();

        $values = [
            'id' => $this->getId(),
            'carrier' => $this->getCarrier(),
            'tracking_number' => $this->getTrackingNumber(),
            'tracking_link' => $this->getTrackingLink(),
            'tracking_comment' => $this->getTrackingComment(),
            'shipping_label_link' => $this->getShippingLabelLink(),
        ];

        $this->isKnownPackageEntity() ?
            $this->updatePackage($queryBuilder, $values) :
            $this->insertPackage($queryBuilder, $values);

        $queryBuilder->execute();
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param mixed[] $values
     */
    private function insertPackage(QueryBuilder $queryBuilder, array $values): void
    {
        $queryBuilder->insert(self::quotedTableName());
        foreach ($values as $field => $value) {
            $queryBuilder->setValue($field, $queryBuilder->expr()->literal($value));
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param mixed[] $values
     */
    private function updatePackage(QueryBuilder $queryBuilder, array $values): void
    {
        $queryBuilder->update(self::quotedTableName());
        $queryBuilder->where('id = :id');
        $queryBuilder->setParameter(':id', $this->getId());

        foreach ($values as $field => $value) {
            $queryBuilder->set($field, $queryBuilder->expr()->literal($value));
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function savePackageItems(): void
    {
        $this->db->delete(self::quotedLinkTableName(), ['package_id' => $this->getId()]);
        map([$this, 'linkPackageItem'], $this->getItems());
    }

    /**
     * @param int $itemId
     */
    private function linkPackageItem(int $itemId): void
    {
        $values = ['package_id' => $this->getId(), 'order_item_id' => $itemId];
        $this->db->insert(self::quotedLinkTableName(), $values);
    }

    /**
     * @return string
     */
    private static function quotedTableName(): string
    {
        return sprintf("`%s`", self::TABLE_NAME);
    }

    /**
     * @return string
     */
    private static function quotedLinkTableName(): string
    {
        return sprintf("`%s`", self::ORDER_ITEMS_LINK_TABLE_NAME);
    }

    /**
     * @return bool
     */
    private function isKnownPackageEntity(): bool
    {
        if (! $this->getId()) {
            return false;
        }

        return (bool) $this->db->createQueryBuilder()->select('COUNT(*)')
            ->from(self::quotedTableName())
            ->where('`id` = ?')
            ->setParameter(0, $this->getId())
            ->execute()
            ->fetchColumn(0);
    }

    /**
     * @param Connection $db
     * @param int ...$orderItemIds
     * @return array[]
     */
    public static function fetchForOrderItemIds(Connection $db, int ...$orderItemIds): array
    {
        $queryBuilder = $db->createQueryBuilder();

        return $queryBuilder->select('p.*, link.order_item_id')
            ->from(self::quotedTableName(), 'p')
            ->leftJoin('p', self::quotedLinkTableName(), 'link', 'link.order_item_id IN (:order_item_ids)')
            ->setParameter('order_item_ids', $orderItemIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll();
    }
}
