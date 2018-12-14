<?php declare(strict_types=1);

namespace MomMock\Entity;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\DBAL\Query\QueryBuilder;

use function array_map as map;

class Package extends AbstractEntity
{
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

    public function getTrackingComment(): string
    {
        return $this->trackingComment;
    }

    public function setTrackingComment(string $trackingComment)
    {
        $this->trackingComment = $trackingComment;
    }

    public function getShippingLabelLink(): string
    {
        return $this->shippingLabelLink;
    }

    public function setShippingLabelLink(string $shippingLabelLink)
    {
        $this->shippingLabelLink = $shippingLabelLink;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items)
    {
        $this->items = $items;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getCarrier(): string
    {
        return $this->carrier;
    }

    public function setCarrier(string $carrier)
    {
        $this->carrier = $carrier;
    }

    public function getTrackingNumber(): string
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber(string $trackingNumber)
    {
        $this->trackingNumber = $trackingNumber;
    }

    public function getTrackingLink(): string
    {
        return $this->trackingLink;
    }

    public function setTrackingLink(string $trackingLink)
    {
        $this->trackingLink = $trackingLink;
    }

    /**
     * @throws InvalidArgumentException
     * @throws ConnectionException
     */
    public function save()
    {
        $this->db->beginTransaction();
        $this->savePackage();
        $this->savePackageItems();
        $this->db->commit();
    }

    private function setField(QueryBuilder $queryBuilder, string $field, $value)
    {
        $placeholder = ':' . $field;
        $queryBuilder->setValue($field, $placeholder)->setParameter($placeholder, $value);
    }

    private function savePackage()
    {
        $queryBuilder = $this->db->createQueryBuilder();

        $this->isKnownPackageEntity() ?
            $queryBuilder->update($this->quotedTableName()) :
            $queryBuilder->insert($this->quotedTableName());

        $this->setField($queryBuilder, 'id', $this->getId());
        $this->setField($queryBuilder, 'carrier', $this->getCarrier());
        $this->setField($queryBuilder, 'tracking_number', $this->getTrackingNumber());
        $this->setField($queryBuilder, 'tracking_link', $this->getTrackingLink());
        $this->setField($queryBuilder, 'tracking_comment', $this->getTrackingComment());
        $this->setField($queryBuilder, 'shipping_label_link', $this->getShippingLabelLink());

        $queryBuilder->execute();
    }

    /**
     * @throws InvalidArgumentException
     */
    private function savePackageItems()
    {
        $this->db->delete($this->quotedLinkTableName(), ['package_id' => $this->getId()]);

        map(function (int $itemId) {
            $this->db->insert(
                $this->quotedLinkTableName(),
                ['package_id' => $this->getId(), 'order_item_id' => $itemId]
            );
        }, $this->getItems());
    }

    /**
     * @return string
     */
    private function quotedTableName(): string
    {
        return sprintf("`%s`", self::TABLE_NAME);
    }

    /**
     * @return string
     */
    private function quotedLinkTableName(): string
    {
        return sprintf("`%s`", self::ORDER_ITEMS_LINK_TABLE_NAME);
    }

    private function isKnownPackageEntity(): bool
    {
        if (! $this->getId()) {
            return false;
        }

        return (bool) $this->db->createQueryBuilder()->select('COUNT(*)')
            ->from($this->quotedTableName())
            ->where('`id` = ?')
            ->setParameter(0, $this->getId())
            ->execute()
            ->fetchColumn(0);
    }
}
