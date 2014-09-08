<?php
/**
 * @name        BypassException
 * @package		BiberLtd\Bundle\AccessManagementBundle
 *
 * @author		Can Berkol
 * @version     1.0.0
 * @date        01.08.2013
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com)
 * @license     GPL v3.0
 *
 * @description Throws if bypass variable has  any other value than a boolean..
 *
 */
namespace BiberLtd\Bundle\AccessManagementBundle\Exceptions;

use BiberLtd\Bundle\ExceptionBundle\Services;

class BypassException extends Services\ExceptionAdapter {
    public function __construct($kernel, $message = "", $code = 999002, Exception $previous = null) {
        parent::__construct(
            $kernel,
            '$bypass variable can only get true or false as values.',
            $code,
            $previous);
    }
}
/**
 * Change Log:
 * **************************************
 * v1.0.0                      Can Berkol
 * 01.08.2013
 * **************************************
 * A __construct()
 *
 */