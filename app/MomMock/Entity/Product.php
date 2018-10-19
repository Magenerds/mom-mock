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

namespace MomMock\Entity;

use DateTime;
use MomMock\Entity\Product\Attribute;

/**
 * Class Product
 * @package MomMock\Entity
 * @author  Mahmood Dhia <m.dhia@techdivision.com>
 */
class Product extends AbstractEntity
{
    /**
     * Product type
     */
    const TYPE_CONFIGURABLE = 'configurable';
    const TYPE_SIMPLE = 'simple';

    /**
     * Holds the table name
     */
    const TABLE_NAME = 'product';
    const TABLE_CHILD_NAME = 'product_child';

    /** @var int */
    protected $id;

    /** @var string */
    protected $type;

    /** @var string */
    protected $sku;

    /** @var string */
    protected $name;

    /** @var bool */
    protected $enabled;

    /** @var Product[] */
    protected $children;

    /** @var array */
    protected $attributes;

    /** @var DateTime */
    protected $createdAt;

    /** @var DateTime */
    protected $updatedAt;

    /** @var bool */
    protected $child;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string: string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return Product[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param Product[] $children
     */
    public function setChildren(array $children)
    {
        $this->children = $children;
    }

    /**
     * @param string $sku
     * @return Product
     */
    public function getChildBySku($sku)
    {
        return $this->children[$sku];
    }

    /**
     * @param string $sku
     * @param Product $child
     */
    public function setChildBySku($sku, $child)
    {
        $this->children[$sku] = $child;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTime $updatedAt
     */
    public function setUpdatedAt(DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return bool
     */
    public function isChild()
    {
        return $this->child;
    }

    /**
     * @param bool $child
     */
    public function setChild($child)
    {
        $this->child = $child;
    }

    /**
     * Load product by id
     *
     * @param $id
     * @return Product
     */
    public function load($id)
    {
        $productData = $this->db->createQueryBuilder()
            ->select('*')
            ->from(sprintf('`%s`', self::TABLE_NAME))
            ->where('`id` = ?')
            ->setParameter(0, $id)
            ->execute()
            ->fetch();

        return (empty($productData) ? null : $this->loadData($productData));
    }

    /**
     * Load product by sku
     *
     * @param $sku
     * @return Product
     */
    public function loadBySku($sku)
    {
        $productData = $this->db->createQueryBuilder()
            ->select('*')
            ->from(sprintf('`%s`', self::TABLE_NAME))
            ->where('`sku` = ?')
            ->setParameter(0, $sku)
            ->execute()
            ->fetch();

        return (empty($productData) ? null : $this->loadData($productData));
    }

    /**
     * Load product data
     *
     * @param array $productData
     * @return Product
     */
    public function loadData(array $productData)
    {
        // Assign simple product data
        $this->id = $productData['id'];
        $this->type = $productData['type'];
        $this->sku = $productData['sku'];
        $this->name = $productData['name'];
        $this->enabled = $productData['enabled'];
        $this->attributes = (empty($productData['attributes']) ? [] : json_decode($productData['attributes']));
        $this->createdAt = new DateTime($productData['created_at']);
        $this->updatedAt = new DateTime($productData['updated_at']);

        // Skip child and Attribute if id not set
        if (empty($this->id)) {
            return $this;
        }

        //<editor-fold desc="Load Children">
        if (!$this->child) {
            $productChildrenData = $this->db->createQueryBuilder()
                ->select('p.*')
                ->from(sprintf('`%s`', self::TABLE_CHILD_NAME), 'pc')
                ->leftJoin('pc', sprintf('`%s`', self::TABLE_NAME), 'p', 'pc.child_sku = p.sku')
                ->where('pc.`product_id` = ?')
                ->setParameter(0, $this->id)
                ->execute()
                ->fetchAll();

            if (!empty($productChildrenData)) {
                foreach ($productChildrenData as $childData) {
                    $child = new self($this->db);
                    $child->setChild(true);
                    $child->loadData($childData);

                    $this->children[$child->getSku()] = $child;
                }
            }
        }
        //</editor-fold>

        return $this;
    }

    /**
     * Import product
     *
     * @param $productData
     */
    public function importProduct($productData)
    {
        // Try to load product by SKU
        $this->loadBySku($productData['sku']);

        // Set product data
        $this->setType($productData['type']);
        $this->setSku($productData['sku']);
        $this->setName($productData['name'][0]['value']);
        $this->setEnabled($productData['enabled']);

        //<editor-fold desc="Clear child relation data">
        $this->setChildren([]);

        if ($this->id) {
            // Delete child relation
            $this->db->createQueryBuilder()
                ->delete(sprintf("`%s`", self::TABLE_CHILD_NAME))
                ->where('`product_id` = :id')
                ->setParameter(':id', $this->id)
                ->execute();
        }
        //</editor-fold>

        // Save product attributes
        $this->attributes = [];
        foreach ($productData['custom_attributes'] as $attributeData) {
            $this->attributes[$attributeData['attribute_code']] = $attributeData['value'];
        }

        // Save product
        $this->save();

        // Save product children relation
        foreach ($productData['children_skus'] as $child_sku) {
            $this->db->createQueryBuilder()
                ->insert(sprintf("`%s`", self::TABLE_CHILD_NAME))
                ->setValue('product_id', ':product_id')
                ->setValue('child_sku', ':child_sku')
                ->setParameter(':product_id', $this->id)
                ->setParameter(':child_sku', $child_sku)
                ->execute();
        }
    }

    /**
     * Saves an product
     *
     * @return int
     */
    public function save()
    {
        $query = $this->db->createQueryBuilder();

        if (!$this->id) {
            $query->insert(sprintf("`%s`", self::TABLE_NAME))
                ->setValue('sku', ':sku')
                ->setValue('type', ':type')
                ->setValue('name', ':name')
                ->setValue('enabled', ':enabled')
                ->setValue('attributes', ':attributes')
                ->setParameter(':sku', $this->sku)
                ->setParameter(':type', $this->type)
                ->setParameter(':name', $this->name)
                ->setParameter(':enabled', $this->enabled)
                ->setParameter(':attributes', json_encode($this->attributes))
                ->execute();

            $this->id = $this->db->lastInsertId();
        } else {
            $query->update(sprintf("`%s`", self::TABLE_NAME))
                ->set('sku', ':sku')
                ->set('type', ':type')
                ->set('name', ':name')
                ->set('enabled', ':enabled')
                ->set('attributes', ':attributes')
                ->where('`id` = :id')
                ->setParameter(':sku', $this->sku)
                ->setParameter(':type', $this->type)
                ->setParameter(':name', $this->name)
                ->setParameter(':enabled', $this->enabled)
                ->setParameter(':attributes', json_encode($this->attributes))
                ->setParameter(':created_at', $this->createdAt->format('Y-m-d h:i:s'))
                ->setParameter(':id', $this->id)
                ->execute();
        }

        // Save children
        foreach ($this->children as $child) {
            $child->save();
        }

        return $this->id;
    }
}