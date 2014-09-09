<?php
/**
 * @name        DuplicateAccessRights
 * @package		BiberLtd\Bundle\AccessManagementBundle
 *
 * @author		Can Berkol
 * @version     1.0.0
 * @date        01.08.2013
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com)
 * @license     GPL v3.0
 *
 * @description Throws if there is more than one line for the given member group / action pair
 *              in member_group_access_right table..
 *
 */
namespace BiberLtd\Bundle\AccessManagementBundle\Exceptions;

use BiberLtd\Bundle\ExceptionBundle\Services;

class BypassException extends Services\ExceptionAdapter {
    public function __construct($kernel, $message = "", $code = 999000, Exception $previous = null) {
        parent::__construct(
            $kernel,
            'There are multiple entries im member_grou_access_right table for the following pair: '.$message,
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