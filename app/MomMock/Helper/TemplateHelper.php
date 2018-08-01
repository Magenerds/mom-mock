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

/**
 * Class TemplateHelper
 * @package MomMock\Helper
 * @author  Florian Sydekum <f.sydekum@techdivision.com>
 */
class TemplateHelper
{
    /**
     * Holds the methods template directory
     */
    const TEMPLATE_DIR = __DIR__
        . DIRECTORY_SEPARATOR
        . '..'
        . DIRECTORY_SEPARATOR
        . '..'
        . DIRECTORY_SEPARATOR
        . 'templates'
        . DIRECTORY_SEPARATOR
        . 'methods'
        . DIRECTORY_SEPARATOR;

    /**
     * Returns the method template for a given method
     *
     * @param $method
     * @return bool|string
     */
    public function getTemplateForMethod($method)
    {
        return file_get_contents(self::TEMPLATE_DIR . $method . '.json');
    }
}