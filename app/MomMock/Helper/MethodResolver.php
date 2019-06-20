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

namespace MomMock\Helper;

use MomMock\Method\AbstractIncomingMethod;

/**
 * Class MethodResolver
 * @package MomMock\Helper
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
class MethodResolver
{
    /**
     * Holds the namespace for method service classes
     */
    const METHOD_NAMESPACE = 'MomMock\\Method';

    /**
     * Holds valid incoming method names which can be handled by this mock
     */
    const VALID_INCOMING_METHODS = [
        'magento.service_bus.remote.register',
        'magento.service_bus.remote.unregister',
        'magento.sales.order_management.create',
        'magento.catalog.product_management.updated',
        'magento.postsales.return_management.authorize',
        'magento.logistics.warehouse_management.lines_shipped',
        'magento.inventory.source_repository.search'
    ];

    /**
     * Parses a given method and returns its service class
     *
     * @param string $method
     * @return AbstractIncomingMethod
     * @throws \Exception
     */
    public function getServiceClassForMethod(string $method)
    {
        $classParts = explode('.', $method);

        // throw away the first key 'magento'
        array_shift($classParts);

        $className = self::METHOD_NAMESPACE;

        foreach ($classParts as $part) {
            $className .= '\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $part)));
        }

        if (!class_exists($className)) {
            throw new \Exception('Method service class could not be found: ' . $className);
        }

        return new $className();
    }

    /**
     * Parses a given service class name and returns its method name
     *
     * @param string $className
     * @return string
     */
    public function getMethodForServiceClass(string $className)
    {
        $methodParts = explode('\\', str_replace(self::METHOD_NAMESPACE . '\\', '', $className));

        // add the first key 'magento'
        $methodName = 'magento';

        foreach ($methodParts as $part) {
            $methodName .= '.' . strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $part));
        }

        return $methodName;
    }

    /**
     * Returns an array of valid method names which can be handled by this mock
     *
     * @return []
     */
    public function getValidMethods()
    {
        return self::VALID_INCOMING_METHODS;
    }
}