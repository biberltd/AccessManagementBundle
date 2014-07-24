<?php

/**
 * TestController
 *
 * TestController is used to test the internal workings of the bundle
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

namespace BiberLtd\Core\Bundles\AccessManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpKernel\Exception,
    Symfony\Component\HttpFoundation\Response;

class TestController extends Controller {

    /** @var $locale        Locale object */
    private $locale;

    /** @var $request       Request object */
    private $request;

    /** @var $session       Session object */
    private $session;

    /** @var $translator    Translator object. */
    private $translator;

    public function init() {
        /** Use only in TestController */
        if (isset($_SERVER['HTTP_CLIENT_IP']) || isset($_SERVER['HTTP_X_FORWARDED_FOR']) || !in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', 'fe80::1', '::1', '192.168.1.134', '176.43.5.152', '192.168.1.135', '192.168.1.145', '88.235.191.124'))
        ) {
            header('HTTP/1.0 403 Forbidden');
            exit('You are not allowed to access this file. Check ' . basename(__FILE__) . ' for more information.');
        }
        /**         * ***************** */
        $this->request = $this->getRequest();
        $this->session = $this->get('session');
        $this->locale = $this->request->getLocale();
        $this->translator = $this->get('translator');
    }

    public function getLocaleAction() {
        $this->init();
        return new Response($this->translator->trans('tst.locale.current', array('%locale%' => $this->locale), 'tst'));
    }

    public function setLocaleAction($locale) {
        $this->init();
        $this->request->setLocale($locale);
        $this->session->set('_locale', $locale);
        $this->locale = $locale;
        return new Response($this->translator->trans('tst.locale.set', array('%locale%' => $locale), 'tst'));
    }

    public function testAction() {
        $model = $this->get('core_access_management_bundle.model');
        
        $response = $model->listMembersGrantedForActions(array('action'=>array(1,2,3),'member'=>13,'right'=>'g'));

        if (!$response['error']) {
        echo '<pre>'; print_r($response['result']['set'][0]->getUsername()); die;
        } else{
            echo 'veri yok başgan';
        }
        die;
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