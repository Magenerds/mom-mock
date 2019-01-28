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
use Magento\FirstModule\Model\Model;
use MomMock\Helper\MethodResolver;
use MomMock\Helper\RpcClient;
use MomMock\Helper\TemplateHelper;

/**
 * Class AbstractIncomingMethod
 * @package MomMock\Method
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
abstract class AbstractIncomingMethod
{
    /**
     * @var Connection
     */
    protected $db;

    /**
     * @var RpcClient
     */
    protected $restClient;

    /**
     * @var MethodResolver
     */
    protected $methodResolver;

    /**
     * @var TemplateHelper
     */
    protected $templateHelper;

    /**
     * Handle the incoming request and its data
     *
     * @param $data
     * @return mixed
     */
    abstract public function handleRequestData($data);

    /**
     * @inheritdoc
     */
    public function setDb(Connection $db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @inheritdoc
     */
    public function setRestClient(RpcClient $rc)
    {
        $this->restClient = $rc;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRestClient()
    {
        return $this->restClient;
    }

    /**
     * @inheritdoc
     */
    public function setMethodResolver(MethodResolver $mr)
    {
        $this->methodResolver = $mr;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMethodResolver()
    {
        return $this->methodResolver;
    }

    /**
     * @inheritdoc
     */
    public function setTemplateHelper(TemplateHelper $th)
    {
        $this->templateHelper = $th;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTemplateHelper()
    {
        return $this->templateHelper;
    }
}