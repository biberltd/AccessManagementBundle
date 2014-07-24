<?php
/**
 * sys.en.php
 *
 * This file registers the bundle's system (error and success) messages in English.
 *
 * @vendor      BiberLtd
 * @package		Core\Bundles\AccessManagementBundle
 * @subpackage	Resources
 * @name	    sys.tr.php
 *
 * @author		Can Berkol
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.0.0
 * @date        02.08.2013
 *
 * =============================================================================================================
 * !!! IMPORTANT !!!
 *
 * Depending your environment run the following code after you have modified this file to clear Symfony Cache.
 * Otherwise your changes will NOT take affect!
 *
 * $ sudo -u apache php app/console cache:clear
 * OR
 * $ php app/console cache:clear
 * =============================================================================================================
 * TODOs:
 * None
 */
/** Nested keys are accepted */
return array(
    /** Error messages */
    'err'       => array(
        /** Access Management Model */
        'smm'   => array(
            'unknown'                   => 'An unknown error occured or the AccessManagementModel has NOT been created.',
        ),
    ),
    /** Success messages */
    'scc'       => array(
        /** Access Management Model */
        'amm'   => array(
            'default'                   => 'Database transaction is processed successfuly.'
        ),
    ),
);
/**
 * Change Log
 * **************************************
 * v1.0.0                      Can Berkol
 * 02.08.2013
 * **************************************
 * A err
 * A err.amm
 * A err.amm.unknown
 * A scc
 * A scc.amm
 * A scc.amm.default
 */