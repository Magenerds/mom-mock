<?php declare(strict_types=1);
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace MomMock\Method\Logistics\CarrierManagement;

use Doctrine\DBAL\Connection;
use MomMock\Entity\Package;
use MomMock\Method\AbstractOutgoingMethod;

use function array_map as map;
use function array_reduce as reduce;
use function array_reverse as reverse;
use function array_walk as walk;

class RequestShippingDetails extends AbstractOutgoingMethod
{
    /**
     * @param int $orderId
     * @return mixed[]
     */
    private function loadOrder(int $orderId): array
    {
        return $this->db->createQueryBuilder()
            ->select('*')
            ->from('`order`')
            ->where('`id` = ?')
            ->setParameter(0, $orderId)
            ->execute()
            ->fetch();
    }

    /**
     * @param int ...$orderItemIds
     * @return array[]
     */
    private function loadOrderItems(int ...$orderItemIds): array
    {
        return $this->db->createQueryBuilder()
            ->select('*')
            ->from('`order_item`')
            ->where('`id` IN (:order_item_ids)')
            ->setParameter('order_item_ids', $orderItemIds, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll();
    }

    /**
     * @param string $entityName
     * @param string $template
     * @param mixed[] $entity
     * @return string
     */
    private function insertEntityIntoTemplateString(string $entityName, string $template, array $entity): string
    {
        foreach ($entity as $key => $value) {
            $template = str_replace(sprintf('{{' . $entityName . '.%s}}', $key), $value, $template);
        }

        return $template;
    }

    /**
     * @param string $entityName
     * @param string $template
     * @param mixed[] $entity
     * @return string|array
     */
    private function decodeEntityIntoTemplate(string $entityName, string $template, array $entity)
    {
        return json_decode($this->insertEntityIntoTemplateString($entityName, $template, $entity), true);
    }

    /**
     * @param mixed[] $templateData
     * @param string[] $path
     * @return mixed[]
     */
    private function buildPathIntoArray(array $templateData, array $path): array
    {
        foreach ($path as $pathSegment) {
            $templateData =& $templateData[$pathSegment];
        }

        return $templateData;
    }

    /**
     * @param string $entityName
     * @param array[] $entities
     * @param mixed[] $payload
     * @param string $path
     * @return mixed[]
     */
    private function insertEntitiesArrayIntoPayload(
        string $entityName,
        array $entities,
        array $payload,
        string $path
    ): array {
        $pathSegments = explode('/', $path);
        $entityTemplateData = $this->buildPathIntoArray($payload, $pathSegments);

        $entityTemplate = json_encode($entityTemplateData[0]);

        // insert entity data into 
        $items = map(function (array $entity) use ($entityName, $entityTemplate) {
            return $this->decodeEntityIntoTemplate($entityName, $entityTemplate, $entity);
        }, $entities);

        $mergeArray = reduce(reverse($pathSegments), function (array $acc, $pathSegment) {
            return [$pathSegment => $acc];
        }, $items);

        return array_replace_recursive($payload, $mergeArray);
    }

    /**
     * @param string $method
     * @param mixed[] $order
     * @param array[] $orderItems
     * @return mixed[]
     */
    private function buildRequestShipmentLabelPayload(string $method, array $order, array $orderItems, $sourceId): array
    {
        $template = $this->templateHelper->getTemplateForMethod($method);

        // insert source id
        $template = str_replace('{{source_id}}', $sourceId, $template);

        // insert order data
        $template = $this->insertEntityIntoTemplateString('order', $template, $order);

        // insert order item data
        $payload = json_decode($template, true);
        $payload = $this->insertEntitiesArrayIntoPayload('order_item', $orderItems, $payload, 'shipment/items');
        $payload = $this->insertEntitiesArrayIntoPayload('order_item', $orderItems, $payload, 'shipment/packages/0/aggregated_items');
        $payload = $this->insertEntitiesArrayIntoPayload('order_item', $orderItems, $payload, 'shipment/packages/0/items');

        return $payload;
    }

    /**
     * @param mixed[] $data
     * @return string
     */
    public function send($data)
    {
        $orderId = (int) $data['order_id'];
        $orderItemIds = map('intval', explode(',', $data['order_item_ids']));

        $order = $this->loadOrder($orderId);
        $orderItems = $this->loadOrderItems(...$orderItemIds);

        $method = $this->methodResolver->getMethodForServiceClass(get_class($this));
        $payload = $this->buildRequestShipmentLabelPayload($method, $order, $orderItems, $data['source_id']);

        $result = (string) $this->rpcClient->send($payload, $method);

        $this->storePackageShipmentLabels($result);

        return $result;
    }

    /**
     * @param string $result
     */
    private function storePackageShipmentLabels(string $result): void
    {
        $packagesData = json_decode($result, true);
        if (json_last_error()) {
            throw new \RuntimeException('Unable to request labels:' . json_last_error_msg());
        }

        if (! isset($packagesData['result'])) {
            throw new \RuntimeException('No result found in response');
        }

        walk($packagesData['result'], function (array $packageData) {
            $package = Package::createFromArray($this->db, $packageData);
            $package->save();
        });
    }
}
