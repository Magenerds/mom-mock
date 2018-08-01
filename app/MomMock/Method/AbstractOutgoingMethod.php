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

namespace MomMock\Method;

use Doctrine\DBAL\Connection;
use MomMock\Helper\MethodResolver;
use MomMock\Helper\TemplateHelper;
use MomMock\Helper\RpcClient;

/**
 * Class AbstractOutgoingMethod
 * @package MomMock\Method
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
abstract class AbstractOutgoingMethod
{
    /**
     * Connection
     */
    protected $db;

    /**
     * @var MethodResolver
     */
    protected $methodResolver;

    /**
     * @var TemplateHelper
     */
    protected $templateHelper;

    /**
     * @var RpcClient
     */
    protected $rpcClient;

    /**
     * AbstractOutgoingMethod constructor.
     * @param Connection $db
     * @param MethodResolver $methodResolver
     * @param TemplateHelper $templateHelper
     * @param RpcClient $rpcClient
     */
    public function __construct(
        Connection $db,
        MethodResolver $methodResolver,
        TemplateHelper $templateHelper,
        RpcClient $rpcClient
    ){
        $this->db = $db;
        $this->methodResolver = $methodResolver;
        $this->templateHelper = $templateHelper;
        $this->rpcClient = $rpcClient;
    }

    /**
     * Send data to registered integrations
     *
     * @param $data
     * @return mixed
     */
    abstract public function send($data);
}