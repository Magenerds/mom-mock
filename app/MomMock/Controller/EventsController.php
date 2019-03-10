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

use Doctrine\DBAL\Connection;
use MomMock\Helper\MethodResolver;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use MomMock\Entity\Journal\Request as JournalRequest;

/**
 * Class MomController
 * @package MomMock\Controller
 * @author  Mahmood Dhia <m.dhia@techdivision.com>
 */
class EventsController
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
     * @var JournalRequest
     */
    private $apiJournal;

    /**
     * MomController constructor.
     * @param Container $container
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function __construct(
        Container $container
    )
    {
        $this->methodResolver = $container->get('method_resolver');
        $this->db = $container->get('db');
        $this->apiJournal = new JournalRequest($this->db);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response|string
     * @throws \Exception
     */
    public function indexAction(Request $request, Response $response)
    {
        $data = json_decode($request->getBody(), true);

        if (!array_key_exists('method', $data) || !in_array($data['method'], $this->methodResolver->getValidMethods())) {
            return $response->withJson(
                json_encode(['error_message' => 'No valid method provided']),
                404
            );
        }

        $exceptionMessage = null;
        try {
            $responseData = $this->methodResolver
                ->getServiceClassForMethod($data['method'])
                ->setDb($this->db)
                ->handleRequestData($data);

            return $response->withJson($responseData);
        } catch (\Exception $e) {
            $exceptionMessage = $e->getMessage();
        } finally {
            $status = $exceptionMessage === null ? JournalRequest::STATUS_SUCCESS : JournalRequest::STATUS_ERROR;

            $this->apiJournal->logRequest(
                $data,
                $status,
                JournalRequest::DIRECTION_INCOMING,
                JournalRequest::OMS_TARGET,
                $exceptionMessage
            );
        }
    }
}