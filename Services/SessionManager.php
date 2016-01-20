<?php
/**
 * @author		Can Berkol
 * @author		Said İmamoğlu
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com) (C) 2015
 * @license     GPLv3
 *
 * @date        11.12.2015
 */
namespace BiberLtd\Bundle\AccessManagementBundle\Services;

use BiberLtd\Bundle\CoreBundle\Core as Core;

use BiberLtd\Bundle\AccessManagementBundle\Services as AMBService;
use BiberLtd\Bundle\MemberManagementBundle\Services as MMBService;
use Symfony\Component\HttpFoundation\RequestStack;

class SessionManager extends Core
{
    private $session;

    /**
     * @name            __construct ()
     *                  Constructor.
     *
     * @author          Can Berkol
     * @since           1.0.0
     * @version         1.0.0
     *
     * @param           string $kernel
     */
    public function __construct($kernel, RequestStack $requestStack)
    {
        parent::__construct($kernel, $requestStack);
        $this->session = $kernel->getContainer()->get('session');
    }

    /**
     * @name            __destruct ()
     *                  Destructor.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.2
     *
     *
     */
    public function __destruct()
    {
        foreach ($this as $key => $value) {
            $this->$key = null;
        }
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return bool
     */
    public function authenticate(string $username, string $password)
    {
        $MMModel = new MMBService\MemberManagementModel($this->kernel, 'default', 'doctrine');
        $AMModel = new AMBService\AccessManagementModel($this->kernel, 'default', 'doctrine');

        $response = $MMModel->validateAccount($username, $password);

        if ($response->error->exist) {
            $this->session->set('authentication_data', false);
            $this->session->set('is_logged_in', false);

            return false;
        }
        /**
         * Get Member Details
         */
        $member = $response->result->set;

        /**
         * Get member's groups.
         */
        $response = $MMModel->listGroupsOfMember($member);
        $group_codes = [];
        $groupIds = [];
        if (!$response->error->exist) {
            $groups = $response->result->set;
            foreach ($groups as $group) {
                $groupIds[] = $group->getId();
                $group_codes[] = $group->getCode();
            }
        }
        /**
         * Get member's sites.
         */
        $response = $MMModel->listSitesOfMember($member);
        $siteIds = [];
        $siteIds[] = $member->getSite()->getId();
        if (!$response->error->exist) {
            $sites = $response->result->set;
            foreach ($sites as $site) {
                if (!in_array($site->getId(), $siteIds)) {
                    $siteIds[] = $site->getId();
                }
            }
        }
	    /**
	     * Which actions are granted?
	     */
        $grantedActions = [];
        $response = $AMModel->listGrantedActionsOfMember($member->getId());
        if (!$response->error->exist) {
            foreach ($response->result->set as $action) {
                $grantedActions[$action->getId()] = $action->getCode();
            }
        }
        foreach ($groupIds as $groupId) {
            $response = $AMModel->listGrantedActionsOfMemberGroup($groupId);
            if (!$response->error->exist) {
                foreach ($response->result->set as $action) {
                    $grantedActions[$action->getId()] = $action->getCode();
                }
            }
        }

	    /**
	     * Which actions are revooked?
	     */
        $revokedActions = [];
        $response = $AMModel->listRevokedActionsOfMember($member->getId());
        if (!$response->error->exist) {
            foreach ($response->result->set as $action) {
                $revokedActions[$action->getId()] = $action->getCode();
            }
        }
        foreach ($groupIds as $groupId) {
            $response = $AMModel->listRevokedActionsOfMemberGroup($groupId);
            if (!$response->error->exist) {
                foreach ($response->result->set as $action) {
                    $revokedActions[$action->getId()] = $action->getCode();
                }
            }
        }
        /**
         * Prepare user details to be stored in $session
         */
        $memberDetails = array(
            'id' => $member->getId(),
            'username' => $member->getUsername(),
            'locale' => $member->getLanguage()->getIsoCode(),
            'email' => $member->getEmail(),
            'full_name' => $member->getFullName(),
            'name_first' => $member->getNameFirst(),
            'name_last' => $member->getNameLast(),
            'status' => $member->getStatus(),
            'date_birth' => $member->getDateBirth(),
            'date_last_login' => $member->getDateLastLogin(),
            'site' => $member->getSite()->getId(),
            'file_avatar' => $member->getFileAvatar(),
            'sites' => $siteIds,
            'groups' => $group_codes,
            'granted_actions' => $grantedActions,
            'revoked_actions' => $revokedActions,
            'session_id' => $this->session->getId(),
        );
        $encryptedData = $this->encrypt($memberDetails);
        $this->session->set('is_logged_in', true);
        $this->session->set('login_type', 'manuel');
        $this->session->set('authentication_data', $encryptedData);
        return true;
    }

	/**
	 * @param  mixed $data
	 *
	 * @return string
	 */
    private function encrypt($data){
        if (is_null($data) || !$data) {
            return '';
        }
        $data = base64_encode(serialize($data));
        $enc = $this->kernel->getContainer()->get('encryption');
        $hashed_data = $enc->input($data)->key($this->kernel->getContainer()->getParameter('app_key'))->encrypt('enc_reversible_pkey')->output();

        return $hashed_data;
    }

	/**
	 * @param $hashed_data
	 *
	 * @return array|mixed
	 */
    public function decrypt($hashed_data){
        if (is_null($hashed_data) || !$hashed_data) {
            return [];
        }
        $enc = $this->kernel->getContainer()->get('encryption');
        $data = $enc->input($hashed_data)->key($this->kernel->getContainer()->getParameter('app_key'))->decrypt('enc_reversible_pkey')->output();
        $data = unserialize(base64_decode($data));
        return $data;
    }

	/**
	 * @return bool
	 */
    public function logout()
    {
        $this->session->invalidate();
        $this->session->set('authentication_data', false);
        $this->session->set('is_logged_in', false);
        return true;
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return bool
     */
    public function addDetail(string $key, $value)
    {
        $current = $this->session->get('authentication_data');
        $current = $this->decrypt($current);
        $current[$key] = $value;
        $current = $this->encrypt($current);

        $this->session->set('authentication_data', $current);

        return true;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function getDetail(string $key)
    {
        $current = $this->session->get('authentication_data');
        $current = $this->decrypt($current);
        if (isset($current[$key])) {
            return $current[$key];
        }

        return false;
    }

	/**
	 * @return mixed
	 */
    public function getId()
    {
        return $this->session->getId();
    }

	/**
	 * return          array
     */
    public function dumpDetails()
    {
        $current = $this->session->get('authentication_data');

        $current = $this->decrypt($current);

        return $current;
    }

	/**
	 * @return bool
	 */
    public function register()
    {
        $logModel = $this->kernel->getContainer()->get('logbundle.model');
        $cookieSessionExists = false;
        $sessionExists = false;

        $session_id = $this->session->getId();
        if (empty($session_id)) {
            $this->session->start();
            $session_id = $this->session->getId();
        }
        $generatedSessionId = $session_id;
        $cookieSessionId = $this->getDetail('session_id');
        if ($cookieSessionId != false) {
            $cookieSessionExists = true;
            $session_id = $cookieSessionId;
        }
        if ($cookieSessionExists) {
            $response = $logModel->getSession($cookieSessionId);
        } else {
            $response = $logModel->getSession($generatedSessionId);
        }
        if (!$response->error->exist) {
            $sessionExists = true;
            $sessionEntry = $response->result->set;
        }
        /**
         * If session exists we do not need to register a new one.
         */
        if ($sessionExists) {
            return false;
        }

        /** Register a new session entry */
        $now = new \DateTime('now', new \DateTimeZone($this->timezone));

        $sessionEntryData = array(
            'date_created' => $now,
            'date_access' => $now,
            'session_id' => $session_id,
            'site' => 1,/** @todo multi site */
        );
        $response = $logModel->insertSession((object)$sessionEntryData);
        if (!$response->error->exist) {
            $insertedSession = $response->result->set[0];
            return $insertedSession;
        }
        return false;
    }

	/**
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return bool
	 */
    public function setDetail(string $key, $value)
    {
        $current = $this->dumpDetails();

        $notAllowed = array('id', 'sites', 'groups', 'granted_actions', 'revoked_actions', 'session_id');
        if (in_array($key, $notAllowed)) {
            return false;
        }
        $current[$key] = $value;

        $encrypted_data = $this->encrypt($current);
        $this->session->set('authentication_data', $encrypted_data);
        return true;
    }

	/**
	 * @param string $sessionId
	 *
	 * @return mixed
	 */
    public function setId(string $sessionId){
        return $this->session->setId($sessionId);
    }

    /**
     * @param string   $action
     * @param int|null $site
     * @param array    $extra
     *
     * @return bool
     */
    public function logAction(string $action, int $site = null, $extra = [])
    {
        $site = $site ?? 1;
        $logModel = $this->kernel->getContainer()->get('logbundle.model');
        $cookieSessionExists = false;
        $sessionExists = false;

        $session_id = $generatedSessionId = $this->session->getId();
        $cookieSessionId = $this->getDetail('session_id');
        if ($cookieSessionId != false) {
            $cookieSessionExists = true;
            $session_id = $cookieSessionId;
        }
        if ($cookieSessionExists) {
            $response = $logModel->getSession($cookieSessionId);
        } else {
            $response = $logModel->getSession($generatedSessionId);
        }
        if (!$response->error->exist) {
            $sessionExists = true;
            $sessionEntry = $response->result->set;
        }
        /**
         * If session does not exists create one.
         */
        if (!$sessionExists) {
            $sessionEntry = $this->register();
        }
        $details = null;
        if (count($extra) > 0) {
            $details = json_encode($extra);
        }
        /** Register a new session entry */
        $now = new \DateTime('now', new \DateTimeZone($this->timezone));
        $logEntryData = array(
            'ip_v4' => $this->requestStack->getCurrentRequest()->getClientIp(),
            'url' => $this->requestStack->getCurrentRequest()->getHost() . $this->requestStack->getCurrentRequest()->getRequestUri(),
            'agent' => $this->requestStack->getCurrentRequest()->headers->get('user-agent'),
            'date_action' => $now,
            'action' => $action,
            'site' => $site,
            'details' => $details,
            'session' => $sessionEntry,
        );
        $logModel->insertLog((object)$logEntryData);
        return true;
    }

    /**
     * @param string|null $log
     *
     * @return bool
     */
    public function update(string $log = null)
    {
        $log = $log ?? 'login';
        $logModel = $this->kernel->getContainer()->get('logbundle.model');
        $memberModel = $this->kernel->getContainer()->get('membermanagement.model');
        $cookieSessionExists = false;
        $sessionExists = false;

        $session_id = $generatedSessionId = $this->session->getId();
        $cookieSessionId = $this->getDetail('session_id');
        if ($cookieSessionId != false) {
            $cookieSessionExists = true;
            $session_id = $cookieSessionId;
        }
        if ($cookieSessionExists) {
            $response = $logModel->getSession($cookieSessionId);
        } else {
            $response = $logModel->getSession($generatedSessionId);
        }
        if (!$response->error->exist) {
            $sessionExists = true;
            $sessionEntry = $response->result->set;
        }
        /**
         * If session exists we do not need to register a new one.
         */
        if (!$sessionExists || !$cookieSessionExists) {
            return false;
        }

        $now = new \DateTime('now', new \DateTimeZone($this->timezone));

        switch ($log) {
            case 'login':
                $response = $memberModel->getMember($this->getDetail('id'), 'id');
                if ($response->error->exist) {
                    return false;
                }
                $member = $response->result->set;
                unset($response);
                $sessionEntry->setUsername($this->getDetail('username'));
                $sessionEntry->setMember($member);
                $sessionEntry->setDateLogin($now);
                break;
            case 'logout':

                $sessionEntry->setDateLogout($now);
                break;
        }
        $sessionEntry->setData(json_encode($this->dumpDetails()));

        $response = $logModel->updateSession($sessionEntry, 'entity');

        if (!$response->error->exist) {
            unset($response);
            return $sessionEntry;
        }
        return false;
    }
}