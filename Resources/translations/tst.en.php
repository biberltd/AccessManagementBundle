<?php
/**
 * tst.en.php
 *
 * This file registers the bundle's test messages in English.
 *
 * @vendor      BiberLtd
 * @package		Core\Bundles\AccessManagementBundle
 * @subpackage	Resources
 * @name	    tst.en.php
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
    /** Hata mesajları */
    'tst'       => array(
        /** Group: locale */
        'locale'   => array(
            'current'                   => 'Your selected locale is %locale%.',
            'set'                       => 'Your current locale is set to "%locale%".',
        ),
    ),
);
/**
 * Change Log
 * **************************************
 * v1.0.0                      Can Berkol
 * 02.08.2013
 * **************************************
 * A tst
 * A tst.locale
 * A tst.locale.current
 * A tst.locale.set
 */