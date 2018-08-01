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

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class TokenController
 * @package MomMock\Controller
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
class TokenController
{
    /**
     * @param Request $request
     * @param Response $response
     * @return static
     */
    public function indexAction(Request $request, Response $response)
    {
        return $response->withJson(
            ['access_token' => 'token'],
            201
        );
    }
}