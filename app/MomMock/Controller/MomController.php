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

namespace MomMock\Controller;

use Slim\Container;
use MomMock\Helper\MethodResolver;
use Slim\Http\Request;
use Slim\Http\Response;
use Doctrine\DBAL\Connection;

/**
 * Class MomController
 * @package MomMock\Controller
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
class MomController
{
    /**
     * @var MethodResolver
     */
    private $methodResolver;

    /**
     * @var Connection
     */
    private $db;

    /**
     * MomController constructor.
     * @param Container $container
     */
    public function __construct(
        Container $container
    ){
        $this->methodResolver = $container->get('method_resolver');
        $this->db = $container->get('db');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response|string
     */
    public function indexAction(Request $request, Response $response)
    {
        $data = json_decode($request->getBody(), true);

        if (!array_key_exists('method', $data)
            || !in_array($data['method'], $this->methodResolver->getValidMethods()))
        {
            return $response->withJson(
                json_encode(['error_message' => 'No valid method provided']),
                404
            );
        }

        $responseData = $this->methodResolver
            ->getServiceClassForMethod($data['method'])
            ->setDb($this->db)
            ->handleRequestData($data);

        return $response->withJson($responseData);
    }
}