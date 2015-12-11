<?php
/**
 * @author		Can Berkol
 * @author		Said İmamoğlu
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com) (C) 2015
 * @license     GPLv3
 *
 * @date        10.12.2015
 */
namespace BiberLtd\Bundle\AccessManagementBundle\Exceptions;

use BiberLtd\Bundle\ExceptionBundle\Services;

class DupplicateAccessRightsException extends Services\ExceptionAdapter {
    public function __construct($kernel, $message = "", $code = 901000, Exception $previous = null) {
        parent::__construct(
            $kernel,
            'There are multiple entries im member_group_access_right table for the following pair: '.$message,
            $code,
            $previous);
    }
}
