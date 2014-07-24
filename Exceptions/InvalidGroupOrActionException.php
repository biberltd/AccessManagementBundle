<?php
/**
 * @name        InvalidGroupOrActionException
 * @package		BiberLtd\Core\Bundles\AccessManagementBundle
 *
 * @author		Can Berkol
 * @version     1.0.0
 * @date        01.08.2013
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com)
 * @license     GPL v3.0
 *
 * @description EThrows if member group or action is not found in database.
 *
 */
namespace BiberLtd\Core\Bundles\AccessManagementBundle\Exceptions;

use BiberLtd\Bundles\ExceptionBundle\Services;

class InvalidGroupOrActionException extends Services\ExceptionAdapter {
    public function __construct($kernel, $message = "", $code = 999001, Exception $previous = null) {
        parent::__construct(
            $kernel,
            'The following group or action action is not registered in database: '.$message,
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