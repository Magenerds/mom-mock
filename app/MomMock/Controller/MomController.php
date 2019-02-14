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

use Interop\Container\Exception\ContainerException;
use MomMock\Helper\RpcClient;
use MomMock\Helper\TemplateHelper;
use Slim\Container;
use MomMock\Helper\MethodResolver;
use MomMock\Entity\Journal\Request as JournalRequest;
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
     * @var TemplateHelper
     */
    private $templateHelper;

    /**
     * @var RpcClient
     */
    private $restClient;

    /**
     * MomController constructor.
     * @param Container $container
     * @throws ContainerException
     */
    public function __construct(
        Container $container
    ){
        $this->methodResolver = $container->get('method_resolver');
        $this->db = $container->get('db');
        $this->templateHelper = $container->get('template_helper');
        $this->restClient = $container->get('rpc_client');
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

        if(!$data) {
            $data = $request->getParsedBody();
        }

        if (!array_key_exists('method', $data)
            || !in_array($data['method'], $this->methodResolver->getValidMethods()))
        {
            return $response->withJson(
                json_encode(['error_message' => 'No valid method provided']),
                404
            );
        }

        $this->logRequest($data);

        $responseData = $this->methodResolver
            ->getServiceClassForMethod($data['method'])
            ->setDb($this->db)
            ->setMethodResolver($this->methodResolver)
            ->setTemplateHelper($this->templateHelper)
            ->setRestClient($this->restClient)
            ->handleRequestData($data);

        return $response->withJson($responseData);
    }

    /**
     * @param $data
     */
    protected function logRequest($data)
    {
        $journal = new JournalRequest($this->db);
        $journal->setData([
            'delivery_id' => $data['id'],
            'status' => JournalRequest::STATUS_SUCCESS,
            'topic' => $data['method'],
            'sent_at'=> date('Y-m-d H:i:s'),
            'direction' => JournalRequest::DIRECTION_INCOMING,
            'to' => JournalRequest::OMS_TARGET
        ]);
        $journal->save();
    }
}
