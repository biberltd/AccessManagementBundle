<?php

namespace BiberLtd\Bundle\AccessManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('BiberLtdAccessManagementBundle:Default:index.html.twig', array('name' => $name));
    }
}
