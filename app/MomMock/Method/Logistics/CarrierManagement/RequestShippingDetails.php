<?php declare(strict_types=1);

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

    private function insertEntityIntoTemplateString(string $entityName, string $template, array $entity)
    {
        foreach ($entity as $key => $value) {
            $template = str_replace(sprintf('{{' . $entityName . '.%s}}', $key), $value, $template);
        }
        return $template;
    }
    
    private function decodeEntityIntoTemplate(string $entityName, string $template, array $entity)
    {
        return json_decode($this->insertEntityIntoTemplateString($entityName, $template, $entity), true);
    }

    private function buildPathIntoArray(array $templateData, array $path)
    {
        foreach ($path as $pathSegment) {
            $templateData =& $templateData[$pathSegment];
        }
        return $templateData;
    }

    private function insertEntitiesArrayIntoPayload(string $entityName, array $entities, array $payload, string $path)
    {
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

    protected function buildRequestShipmentLabelPayload(string $method, array $order, array $orderItems)
    {
        $template = $this->templateHelper->getTemplateForMethod($method);

        // insert order data
        $template = $this->insertEntityIntoTemplateString('order', $template, $order);

        // insert order item data
        $payload = json_decode($template, true);
        $payload = $this->insertEntitiesArrayIntoPayload('order_item', $orderItems, $payload, 'shipment/items');
        $payload = $this->insertEntitiesArrayIntoPayload('order_item', $orderItems, $payload, 'shipment/packages/0/aggregated_items');
        $payload = $this->insertEntitiesArrayIntoPayload('order_item', $orderItems, $payload, 'shipment/packages/0/items');

        return $payload;
    }

    public function send($data)
    {
        $orderId = (int) $data['order_id'];
        $orderItemIds = map('intval', explode(',', $data['order_item_ids']));

        $order = $this->loadOrder($orderId);
        $orderItems = $this->loadOrderItems(...$orderItemIds);
        
        $method = $this->methodResolver->getMethodForServiceClass(get_class($this));
        $payload = $this->buildRequestShipmentLabelPayload($method, $order, $orderItems);
        
        $result = $this->rpcClient->send($payload, $method);
        
        $this->storePackageShipmentLabels($result);

        return $result;
    }
    
    private function storePackageShipmentLabels(string $result)
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
