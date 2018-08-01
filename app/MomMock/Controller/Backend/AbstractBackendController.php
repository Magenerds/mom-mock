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

namespace MomMock\Controller\Backend;

use Doctrine\DBAL\Connection;
use Slim\Container;
use MomMock\Helper\MethodResolver;
use MomMock\Helper\TemplateHelper;
use MomMock\Helper\RpcClient;

/**
 * Class AbstractBackendController
 * @package MomMock\Controller\Backend
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
class AbstractBackendController
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * @var \Twig_Environment
     */
    private $templ;

    /**
     * @var MethodResolver
     */
    private $methodResolver;

    /**
     * @var TemplateHelper
     */
    private $templateHelper;

    /**
     * @var RpcClient
     */
    private $rpcClient;

    /**
     * AbstractBackendController constructor.
     * @param Container $container
     */
    public function __construct(
        Container $container
    ){
        $this->db = $container->get('db');
        $this->templ = $container->get('templ');
        $this->methodResolver = $container->get('method_resolver');
        $this->templateHelper = $container->get('template_helper');
        $this->rpcClient = $container->get('rpc_client');
    }

    /**
     * @return \Twig_Environment
     */
    protected function getTemplateEngine()
    {
        return $this->templ;
    }

    /**
     * @return Connection
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @return MethodResolver
     */
    public function getMethodResolver()
    {
        return $this->methodResolver;
    }

    /**
     * @return TemplateHelper
     */
    public function getTemplateHelper()
    {
        return $this->templateHelper;
    }

    /**
     * @return RpcClient
     */
    public function getRpcClient()
    {
        return $this->rpcClient;
    }
}