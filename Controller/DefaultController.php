<?php
/**
 * DefaultController
 *
 * Default controller of AccessManagementBundle
 *
 * @vendor      BiberLtd
 * @package		AccessManagementBundle
 * @subpackage	Controller
 * @name	    DefaultController
 *
 * @author		Can Berkol
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.0.0
 * @date        01.08.2013
 *
 */

namespace BiberLtd\Bundle\AccessManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpKernel\Exception,
    Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $model = $this->get('core_access_management_bundle.model');

        print_r($model->isMemberGroupGrantedAction(3,5));
        return new Response('AccessManagementBundle');
    }
}
/**
 * Change Log:
 * **************************************
 * v1.0.0                      Can Berkol
 * 01.08.2013
 * **************************************
 * A
 *
 */