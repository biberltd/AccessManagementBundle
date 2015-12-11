<?php
/**
 * @author		Can Berkol
 * @author		Said İmamoğlu
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com) (C) 2015
 * @license     GPLv3
 *
 * @date        11.12.2015
 *
 * This class provides mechanism to validate user access based on groups an access management rights
 * stored in database.
 */
namespace BiberLtd\Bundle\AccessManagementBundle\Services;
use BiberLtd\Bundle\CoreBundle\Core as Core;

class AccessValidator extends Core{
    private $session;

    /**
     * AccessValidator constructor.
     *
     * @param $kernel
     */
    public function __construct($kernel){
        parent::__construct($kernel);
        $this->session = $kernel->getContainer()->get('session');

        /** ************************************** */
    }

    /**
     * Destructor
     */
    public function __destruct(){
        foreach($this as $key => $value) {
            $this->$key = null;
        }
    }

    /**
     * @param string $actionCode
     * @param null   $memberData
     *
     * @return bool
     */
    public function isActionGranted(\string $actionCode, $memberData = null){
        $data = $memberData;
        if(is_null($data)){
            $data = $this->decryptSessionData();
        }
        if(!isset($data['granted_actions'])){
            $data['granted_actions'] = array();
        }
        if(in_array($actionCode, $data['granted_actions'])){
            return true;
        }
        return false;
    }

    /**
     * @param string $actionCode
     * @param null   $memberData
     *
     * @return bool
     */
    public function isActionRevoked(\string $actionCode, $memberData = null){
        $data = $memberData;
        if(is_null($data)){
            $data = $this->decryptSessionData();
        }
        if(!isset($data['revoked_actions'])){
            $data['revoked_actions'] = array();
        }
        if(in_array($actionCode, $data['revoked_actions'])){
            return true;
        }
        return false;
    }

    /**
     * @param null $member_data
     *
     * @return bool
     */
    public function isGuest($member_data = null){
        if($this->session->get('is_logged_in')){
            return false;
        }
        $data = $member_data;
        if(is_null($member_data)){
            $data = $this->decryptSessionData();
        }
        if(!isset($data['username']) || $data['username'] == 'guest'){
            return true;
        }
        return false;
    }

    /**
     * @param null $member_data
     *
     * @return bool
     */
    public function isAuthenticated($member_data = null){
        $data = $member_data;
        if(is_null($member_data)){
            $data = $this->decryptSessionData();
        }
        if($this->session->get('authentication_data') != false){
            if(isset($data['username']) && $data['username'] != 'guest'){
                return true;
            }
        }
        return false;
    }

    /**
     * @param null  $member_data
     * @param array $access_map
     * @param bool  $debug
     *
     * @return bool
     */
    public function hasAccess($member_data = null, $access_map = array(), $debug = false){
        $data = $member_data;
        if(is_null($member_data)){
            $data = $this->decryptSessionData();
        }
        if(!isset($data['sites'])){
            $data['sites'] = array(1);
        }
        $is_guest = $this->isGuest($data);
        $is_authenticated = $this->isAuthenticated($data);
        /**
         * 0. If unmanaged
         */
        if($access_map['unmanaged']){
            if($debug){
                echo 'This controller is unmanaged and everyone can access it.';
            }
            return true;
        }
        /**
         * 1. If Guest
         */
        if($is_guest && $access_map['guest']){
            if($debug){
                echo 'This controller is only for guests. If you are already logged-in, you should\'t see this message.';
            }
            return true;
        }
        if(!$is_guest && $access_map['guest']){
            if($debug){
                echo 'This controller is only for guests but you are already logged-in.';
            }
            return false;
        }
        /**
         * 2. If authenticated
         */
        if($is_authenticated && $access_map['authenticated']){
            /**
             * Correct site?
             */
            if(!in_array($this->kernel->getContainer()->get('session')->get('_currentSiteId'), $data['sites'])){
                if($debug){
                    echo 'This controller is only for authenticated users that belong the current site.';
                }
                return false;
            }
            /**
             * Correct status?
             */
            if(isset($access_map['status']) && count($access_map['status']) > 0){
                if(!isset($data['status'])){
                    $data['status'] = 'a';
                }
                if(!in_array($data['status'], $access_map['status'])){
                    if($debug){
                        echo 'This controller is only for authenticated users with specific account status.';
                    }
                    return false;
                }
            }
            /**
             * Correct group or member?
             */
            $member_groups = $data['groups'];
            $has_group_access = false;
            foreach($member_groups as $group){
                if(in_array($group, $access_map['groups'])){
                    $has_group_access = true;
                    break;
                }
            }
            if(count($access_map['groups']) == 0 && count($access_map['members']) == 0){
                $has_group_access = true;
            }
            $has_member_access = false;
            $member = $data['id'];
            if(in_array($member, $access_map['members'])){
                $has_member_access = true;
            }
            if($has_member_access || $has_group_access){
                if($debug){
                    echo 'This controller is only for specific authenticated members / member groups and you are one of them.';
                }
                return true;
            }
            if($debug){
                echo 'This controller is only for specific authenticated members / member groups.';
            }
            return false;
        }
        if($is_authenticated && !$access_map['authenticated']){
            if($debug){
                echo 'This controller is only for authenticated members but you are not authenticated.';
            }
            return false;
        }
    }

    /**
     * @return array|mixed
     */
    private function decryptSessionData(){
        $session_data = $this->kernel->getContainer()->get('session')->get('authentication_data');
        if($session_data){
            $enc = $this->kernel->getContainer()->get('encryption');
            $data = $enc->input($session_data)->key($this->kernel->getContainer()->getParameter('app_key'))->decrypt('enc_reversible_pkey')->output();
            $data = unserialize(base64_decode($data));
        }
        else{
            $data = array(
                'id'            => null,
                'username'      => 'guest',
                'locale'        => $this->kernel->getContainer()->getParameter('locale'),
                'email'         => null,
                'full_name'     => null,
                'name_first'    => null,
                'name_last'     => null,
                'status'        => 'a',
                'date_birth'    => null,
                'site'          => null,
                'groups'        => array(),
                'session_id'    => $this->kernel->getContainer()->get('session')->getId(),
            );
        }
        return $data;
    }
}