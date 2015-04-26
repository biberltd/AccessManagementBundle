<?php
/**
 * @vendor      BiberLtd
 * @package		AccessManagementBundle
 * @subpackage	Services
 * @name	    SessionManager
 *
 * @author		Can Berkol
 *
 * @version     1.0.0
 * @date        26.04.2015
 *
 */
namespace BiberLtd\Bundle\AccessManagementBundle\Services;
use BiberLtd\Bundle\CoreBundle\Core as Core;

use BiberLtd\Bundle\AccessManagementBundle\Services as AMBService;
use BiberLtd\Bundle\MemberManagementBundle\Services as MMBService;
class SessionManager extends Core{
    private $session;

    /**
     * @name            __construct()
     *                  Constructor.
     *
     * @author          Can Berkol
     * @since           1.0.0
     * @version         1.0.0
     *
     * @param           string          $kernel
     */
    public function __construct($kernel){
        parent::__construct($kernel);
        $this->session = $kernel->getContainer()->get('session');
    }
    /**
     * @name            __destruct()
     *                  Destructor.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     *
     */
    public function __destruct(){
        foreach($this as $key => $value) {
            $this->$key = null;
        }
    }
    /**
     * @name            authenticate()
     *                  Authenticates a user and sets session.
     *
     * @author          Can Berkol
     * @since           1.0.0
     * @version         1.0.0
     *
     * @param           string      $username
     * @param           string      $password
     *
     * @return          bool
     */
    public function authenticate($username, $password){
        $MMModel = new MMBService\MemberManagementModel($this->kernel, 'default', 'doctrine');
        $AMModel = new AMBService\AccessManagementModel($this->kernel, 'default', 'doctrine');
        /** Validate account */
        $response = $MMModel->validateAccount($username, $password);

        if($response['error']){
            $this->session->set('authentication_data', false);
            $this->session->set('is_logged_in', false);

            return false;
        }
        /**
         * Get Member Details
         */
        $member = $response['result']['set'];

        /**
         * Get member's groups.
         */
        $response = $MMModel->listGroupsOfMember($member);
        $group_codes = array();
        $groupIds = array();
        if(!$response['error']){
            $groups = $response['result']['set'];
            foreach($groups as $group){
                $groupIds[] = $group->getId();
                $group_codes[] = $group->getCode();
            }
        }

        $grantedActions = array();
        $response = $AMModel->listGrantedActionsOfMember($member->getId());
        if(!$response['error']){
            foreach($response['result']['set'] as $action){
                $grantedActions[$action->getId()] = $action->getCode();
            }
        }
        foreach($groupIds as $groupId){
            $response = $AMModel->listGrantedActionsOfMemberGroup($groupId);
            if(!$response['error']){
                foreach($response['result']['set'] as $action){
                    $grantedActions[$action->getId()] = $action->getCode();
                }
            }
        }
        $revokedActions = array();
        $response = $AMModel->listRevokedActionsOfMember($member->getId());
        if(!$response['error']){
            foreach($response['result']['set'] as $action){
                $revokedActions[$action->getId()] = $action->getCode();
            }
        }
        foreach($groupIds as $groupId){
            $response = $AMModel->listRevokedActionsOfMemberGroup($groupId);
            if(!$response['error']){
                foreach($response['result']['set'] as $action){
                    $revokedActions[$action->getId()] = $action->getCode();
                }
            }
        }
        /**
         * Prepare user details to be stored in $session
         */
        $member_details = array(
            'id'            => $member->getId(),
            'username'      => $member->getUsername(),
            'locale'        => $member->getLanguage()->getIsoCode(),
            'email'         => $member->getEmail(),
            'full_name'     => $member->getFullName(),
            'name_first'    => $member->getNameFirst(),
            'name_last'     => $member->getNameLast(),
            'status'        => $member->getStatus(),
            'date_birth'    => $member->getDateBirth(),
            'date_last_login'    => $member->getDateLastLogin(),
            'site'          => $member->getSite()->getId(),
            'file_avatar'   => $member->getFileAvatar(),
            // @todo 'sites'         => $member->dump_sites(),
            'sites'         => array(1),
            'groups'        => $group_codes,
            'granted_actions'=> $grantedActions,
            'revoked_actions'=> $revokedActions,
            'session_id'    => $this->session->getId(),
        );
        $encrypted_data = $this->encrypt($member_details);
        $this->session->set('is_logged_in', true);
        $this->session->set('login_type', 'manuel');
        $this->session->set('authentication_data', $encrypted_data);
        return true;
    }
    /**
     * @name            encrypt()
     *                  Prepares the given data to store with session.
     *
     * @author          Can Berkol
     * @since           1.0.0
     * @version         1.0.0
     *
     * @param           mixed           $data
     *
     * @return          string
     */
    private function encrypt($data){
        if(is_null($data) || !$data){
            return '';
        }
        $data = base64_encode(serialize($data));
        $enc = $this->kernel->getContainer()->get('encryption');
        $hashed_data = $enc->input($data)->key($this->kernel->getContainer()->getParameter('app_key'))->encrypt('enc_reversible_pkey')->output();

        return $hashed_data;
    }
    /**
     * @name            decrypt()
     *                  Decrypts the session data and returns an array.
     *
     * @author          Can Berkol
     * @since           1.0.0
     * @version         1.0.0
     *
     * @param           Session         $hashed_data
     *
     * @return          array           $data
     */
    public function decrypt($hashed_data){
        if(is_null($hashed_data) || !$hashed_data){
            return array();
        }
        $enc = $this->kernel->getContainer()->get('encryption');
        $data = $enc->input($hashed_data)->key($this->kernel->getContainer()->getParameter('app_key'))->decrypt('enc_reversible_pkey')->output();
        $data = unserialize(base64_decode($data));
        return $data;
    }
    /**
     * @name            logout()
     *                  Logouts a session.
     *
     * @author          Can Berkol
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          true
     */
    public function logout(){
        $this->session->invalidate();
        $this->session->set('authentication_data', false);
        $this->session->set('is_logged_in', false);
        return true;
    }
    /**
     * @name            addDetail()
     *                  Adds authentication detail.
     *
     * @author          Can Berkol
     * @since           1.0.0
     * @version         1.0.0
     *
     * @param           string          $key
     * @param           mixed           $value
     *
     * @return          true
     */
    public function addDetail($key, $value){
        $current = $this->session->get('authentication_data');

        $current = $this->decrypt($current);

        $current[$key] = $value;

        $current = $this->encrypt($current);

        $this->session->set('authentication_data', $current);

        return true;
    }
    /**
     * @name            add_detail()
     *                  Adds authentication detail.
     *
     * @author          Can Berkol
     * @since           1.0.0
     * @version         1.0.0
     *
     * @deprecated      Use $this->addDetail() instead. Will be removed in v1.1.0
     *
     * @param           string          $key
     * @param           mixed           $value
     *
     * @return          true
     */
    public function add_detail($key, $value){
        return $this->addDetail($key, $value);
    }
    /**
     * @name            getDetail()
     *                  Gets authentication detail.
     *
     * @author          Can Berkol
     * @since           1.0.0
     * @version         1.0.0
     *
     * @param           $key            string
     *
     * @return          mixed
     */
    public function getDetail($key){
        $current = $this->session->get('authentication_data');
        $current = $this->decrypt($current);
        if(isset($current[$key])){
            return $current[$key];
        }

        return false;
    }
    /**
     * @name            get_detail()
     *                  Gets authentication detail.
     *
     * @author          Can Berkol
     * @since           1.0.0
     * @version         1.0.0
     *
     * @param           $key            string
     *
     * @deprecated      Use $this->getDetail() instead. Will be removed in v1.1.0
     *
     * @return          mixed
     */
    public function get_detail($key){
        return $this->getDetail($key);
    }
    /**
     * @name            getId()
     *                  Returns server session id
     *
     * @author          Can Berkol
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          string
     */
    public function getId(){
        return $this->session->getId();
    }
    /**
     * @name            dumpDetails()
     *                  Gets all authentication details.
     *
     * @author          Can Berkol
     * @since           1.0.0
     * @version         1.0.0
     *
     *
     * @return          array
     */
    public function dumpDetails(){
        $current = $this->session->get('authentication_data');

        $current = $this->decrypt($current);

        return $current;
    }
    /**
     * @name            dump_details()
     *                  Gets all authentication details.
     *
     * @author          Can Berkol
     * @since           1.0.0
     * @version         1.0.0
     *
     * @deprecated      Use $this->dumpDetails() instead. Will be removed in v1.1.0
     *
     * @return          array
     */
    public function dump_details(){
        return $this->dumpDetails();
    }
    /**
     * @name            register()
     *                  Registers a session into database.
     *
     * @author          Can Berkol
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          mixed                   bool or Session entity.
     */
    public function register(){
        $logModel = $this->kernel->getContainer()->get('logbundle.model');
        $cookieSessionExists = false;
        $sessionExists = false;

        $session_id = $this->session->getId();
        if(empty($session_id)){
            $this->session->start();
            $session_id = $this->session->getId();
        }
        $generatedSessionId = $session_id;
        $cookieSessionId = $this->getDetail('session_id');
        if($cookieSessionId != false){
            $cookieSessionExists = true;
            $session_id = $cookieSessionId;
        }
        if($cookieSessionExists){
            $response = $logModel->getSession($cookieSessionId, 'session_id');
        }
        else{
            $response = $logModel->getSession($generatedSessionId, 'session_id');
        }
        if(!$response['error']){
            $sessionExists = true;
            $sessionEntry = $response['result']['set'];
        }
        /**
         * If session exists we do not need to register a new one.
         */
        if($sessionExists){
            return false;
        }

        /** Register a new session entry */
        $now = new \DateTime('now', new \DateTimeZone($this->timezone));

        $sessionEntryData = array(
            'date_created'  => $now,
            'date_access'   => $now,
            'session_id'    => $session_id,
            'site'          => 1,   /** @todo multi site */
        );
        $response = $logModel->insertSession($sessionEntryData);
        if(!$response['error']){
            $insertedSession = $response['result']['set'][0];
            return $insertedSession;
        }
        return false;
    }
    /**
     * @name            logAction()
     *                  Logs a user action in database.
     *
     * @author          Can Berkol
     * @since           1.0.0
     * @version         1.0.0
     *
     * @param           string      $action
     * @param           integer     $site
     * @param           array       $extra
     *
     * @return          bool
     */
    public function logAction($action, $site = 1, $extra = array()){
        $logModel = $this->kernel->getContainer()->get('logbundle.model');
        $cookieSessionExists = false;
        $sessionExists = false;

        $session_id = $generatedSessionId = $this->session->getId();
        $cookieSessionId = $this->get_detail('session_id');
        if($cookieSessionId != false){
            $cookieSessionExists = true;
            $session_id = $cookieSessionId;
        }
        if($cookieSessionExists){
            $response = $logModel->getSession($cookieSessionId, 'session_id');
        }
        else{
            $response = $logModel->getSession($generatedSessionId, 'session_id');
        }
        if(!$response['error']){
            $sessionExists = true;
            $sessionEntry = $response['result']['set'];
        }
        /**
         * If session does not exists create one.
         */
        if(!$sessionExists){
            $sessionEntry = $this->register();
        }
        $details = null;
        if(count($extra) > 0){
            $details = json_encode($extra);
        }
        /** Register a new session entry */
        $now = new \DateTime('now', new \DateTimeZone($this->timezone));
        $logEntryData = array(
            'ip_v4'         => $this->kernel->getContainer()->get('request')->getClientIp(),
            'url'           => $this->kernel->getContainer()->get('request')->getHost().$this->kernel->getContainer()->get('request')->getRequestUri(),
            'agent'         => $this->kernel->getContainer()->get('request')->headers->get('user-agent'),
            'date_action'   => $now,
            'action'        => $action,
            'site'          => $site,
            'details'       => $details,
            'session'       => $sessionEntry,
        );
        $logModel->insertLog($logEntryData);
        return true;
    }
    /**
     * @name            update()
     *                  Updates a session
     *
     * @author          Can Berkol
     * @since           1.0.3
     * @version         1.0.3
     *
     * @param           string      $log        login, logout
     * @return          mixed                   bool or Session entity.
     */
    public function update($log = 'login'){
        $logModel = $this->kernel->getContainer()->get('logbundle.model');
        $memberModel = $this->kernel->getContainer()->get('membermanagement.model');
        $cookieSessionExists = false;
        $sessionExists = false;

        $session_id = $generatedSessionId = $this->session->getId();
        $cookieSessionId = $this->get_detail('session_id');
        if($cookieSessionId != false){
            $cookieSessionExists = true;
            $session_id = $cookieSessionId;
        }
        if($cookieSessionExists){
            $response = $logModel->getSession($cookieSessionId, 'session_id');
        }
        else{
            $response = $logModel->getSession($generatedSessionId, 'session_id');
        }
        if(!$response['error']){
            $sessionExists = true;
            $sessionEntry = $response['result']['set'];
        }
        /**
         * If session exists we do not need to register a new one.
         */
        if(!$sessionExists || !$cookieSessionExists){
            return false;
        }

        $now = new \DateTime('now', new \DateTimeZone($this->timezone));

        switch($log){
            case 'login':

                $response = $memberModel->getMember($this->get_detail('id'), 'id');
                if($response['error']){
                    return false;
                }
                $member = $response['result']['set'];
                unset($response);
                $sessionEntry->setUsername($this->get_detail('username'));
                $sessionEntry->setMember($member);
                $sessionEntry->setDateLogin($now);
                break;
            case 'logout':

                $sessionEntry->setDateLogout($now);
                break;
        }
        $sessionEntry->setData(json_encode($this->dump_details()));

        $response = $logModel->updateSession($sessionEntry, 'entity');

        if(!$response['error']){
            unset($response);
            return $sessionEntry;
        }
        return false;
    }
}
/**
 * Change Log
 * ****************************************
 * v1.0.0						26.04.2015
 * TW #
 * Can Berkol
 * ****************************************
 * - Class moved to AccessManagementBundle from CoreBundle.
 */