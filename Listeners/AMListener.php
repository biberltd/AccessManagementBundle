<?php
/**
 * @author		Can Berkol
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com) (C) 2015
 * @license     GPLv3
 *
 * @date        10.12.2015
 */
namespace BiberLtd\Bundle\AccessManagementBundle\Listeners;

use BiberLtd\Bundle\CoreBundle\Core as Core;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpKernel\Event;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;


class AMListener extends Core{
    /** @var $session               Current session */
    protected     $session;
    /** @var $cookie                Current session's cookie if set. */
    protected     $cookie;

    /**
     * AMListener constructor.
     *
     * @param $kernel
     */
    public function __construct($kernel){
        parent::__construct($kernel);
        $this->session = $this->kernel->getContainer()->get('session');
        $this->cookie = $this->kernel->getContainer()->get('request')->cookies;
    }
    /*
     * Destructor
     */
    public function __destruct(){
        foreach($this as $property => $value) {
            $this->$property = null;
        }
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $e
     */
    public function onKernelRequest(\Symfony\Component\HttpKernel\Event\GetResponseEvent $e){
        $request = $e->getRequest();
        $enc = $this->kernel->getContainer()->get('encryption');
        $this->session = $this->kernel->getContainer()->get('session');
        $encrypted_cookie = $this->cookie->get('bbr_member');
        $cookie = array();
        if(!is_null($encrypted_cookie) && isset($encrypted_cookie) && !$encrypted_cookie){
            $cookie = $enc->input($encrypted_cookie)->key($this->kernel->getContainer()->getParameter('app_key'))->decrypt('enc_reversible_pkey')->output();
            $cookie = unserialize(base64_decode($cookie));
        }

        if(isset($encrypted_cookie) && !$this->session->get('authentication_data') && $encrypted_cookie != false){
            $this->session->set('authentication_data', $encrypted_cookie);
        }
       /**
        * 1. Check current session and see if the user is logged in.
        */
        if(!$this->session->get('is_logged_in')){
            /**
             * If not check the cookie
             */
            if(isset($cookie['username'])){
                $this->session->set('login_type', 'cookie');
                if(isset($cookie['session_id']) && $this->session->getId() != $cookie['session_id']){
                    $this->session->set('related_session', $cookie['session_id']);
                }
                $this->session->set('is_logged_in', true);
                $this->session->set('authentication_data', $encrypted_cookie);

                return;
            }
        }
        /** If no authentication data is set then the user is not loggedin. */
        $authentication_data = $this->session->get('authentication_data');
        if(!$this->session->get('authentication_data') || empty($authentication_data)){
            $this->session->set('is_logged_in', false);
        }
        return;
    }
}