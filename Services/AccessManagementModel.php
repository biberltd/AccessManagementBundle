<?php
/**
 * AccessManagementModel Class
 *
 * This class acts as a database proxy model for AccessManagementBundle functionalities..
 *
 * @vendor      BiberLtd
 * @package		Core\Bundles\AccessManagementBundle
 * @subpackage	Services
 * @name	    AccessManagementModel
 *
 * @author		Can Berkol
 * @author      Said İmamoğlu
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.1.0
 * @date        09.07.2014
 */

namespace BiberLtd\Bundle\AccessManagementBundle\Services;

/** Entities to be used */
use BiberLtd\Bundle\AccessManagementBundle\Entity as BundleEntity;
use BiberLtd\Bundle\LogBundle\Entity as LBEntity;
use BiberLtd\Bundle\MemberManagementBundle\Entity as MMBEntity;
/** Models to be loaded */
use BiberLtd\Bundle\LogBundle\Services as LBService;
use BiberLtd\Bundle\MemberManagementBundle\Services as MMBService;
/** Core Service */
use BiberLtd\Bundle\CoreBundle\CoreModel;
use BiberLtd\Bundle\CoreBundle\Services as CoreServices;
use BiberLtd\Bundle\CoreBundle\Exceptions as CoreExceptions;

class AccessManagementModel extends CoreModel {

    /**
     * @name            __construct()
     *                  Constructor.
     *
     * @author          Can Berkol
     *
     * @since           1.0.0
     * @version         1.0.0
     *
     * @param           object          $kernel
     * @param           string          $db_connection  Database connection key as set in app/config.yml
     * @param           string          $orm            ORM that is used.
     */
    public function __construct($kernel, $db_connection = 'default', $orm = 'doctrine') {
        parent::__construct($kernel, $db_connection, $orm);
        /**
         * Register entity names for easy reference.
         */
        $this->entity = array(
            'member_access_right' => array('name' => 'AccessManagementBundle:MemberAccessRight', 'alias' => 'mar'),
            'member_group_access_right' => array('name' => 'AccessManagementBundle:MemberGroupAccessRight', 'alias' => 'mgar'),
        );
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
     */
    public function __destruct() {
        foreach ($this as $property => $value) {
            $this->$property = null;
        }
    }
    /**
     * @name 			grantRightToMember()
     *  				Grants access right for action to a specific member.
     *                  In short, inserts an entry to the database table.
     *
     *                  In response:
     *                  Returns true if the action right is set to g or false if the action right is not set or set to r
     *                  g: granted
     *                  r: revoked
     *
     * @since			1.0.0
     * @version         1.0.9
     * @author          Can Berkol
     *
     * @use             $this->resetResponse()
     * @use             $this->validateAndGetMember()
     *
     * @param           mixed           $member     Member Entity or member group id.
     * @param           mixed           $action     Action Entity or action id.
     *
     * @return          mixed           $response
     */
    public function grantRightToMember($member, $action) {
        $this->resetResponse();
        $member = $this->validateAndGetMember($member);
        $action = $this->validateAndGetAction($action);

        $ar = new BundleEntity\MemberAccessRight();
        $ar->setAction($action);
        $ar->setMember($member);
        $ar->setRight("g");
        $ar->setDateAssigned(new \DateTime('now', new \DateTimeZone(($this->kernel->getContainer()->getParameter('app_timezone')))));

        $this->em->persist($ar);
        $this->em->flush($ar);

        return $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $ar,
                'total_rows' => 1,
                'last_insert_id' => false,
            ),
            'error' => false,
            'code' => 'msg.success.db.insert',
        );
    }
    /**
     * @name 			grantRightToMemberGroup()
     *  				Grants access right for action to a specific member.
     *                  In short, inserts an entry to the database table.
     *
     *                  In response:
     *                  Returns true if the action right is set to g or false if the action right is not set or set to r
     *                  g: granted
     *                  r: revoked
     *
     * @since			1.0.0
     * @version         1.0.9
     * @author          Can Berkol
     *
     * @use             $this->resetResponse()
     *
     * @param           mixed           $group      Member Group Entity or member group id.
     * @param           mixed           $action     Action Entity or action id.
     *
     * @return          mixed           $response
     */
    public function grantRightToMemberGroup($group, $action) {
        $this->resetResponse();
        $group = $this->validateAndGetMemberGroup($group);
        $action = $this->validateAndGetAction($action);
        
        $ar = new BundleEntity\MemberGroupAccessRight();
        $ar->setAction($action);
        $ar->setMemberGroup($group);
        $ar->setRight("g");

        $ar->setDateAssigned(new \DateTime('now', new \DateTimeZone(($this->kernel->getContainer()->getParameter('app_timezone')))));

        $this->em->persist($ar);
        $this->em->flush($ar);

        return $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $ar,
                'total_rows' => 1,
                'last_insert_id' => false,
            ),
            'error' => false,
            'code' => 'msg.success.db.insert',
        );
    }
    /**
     * @name 			isMemberGrantedAction()
     *  				Checks if the member has access rights for given action.
     *
     *                  In response:
     *                  Returns true if the action right is set to g or false if the action right is not set or set to r
     *                  g: granted
     *                  r: revoked
     *
     * @since			1.0.0
     * @version         1.1.0
     * @author          Can Berkol
     *
     * @use             $this->createException()
     * @use             $this->resetResponse()
     * @use             $this->validateAndGetAction()
     * @use             $this->validateAndGetMember()
     *
     * @param           mixed           $member     Member Entity or member group id.
     * @param           mixed           $action     Action Entity or action id.
     * @param           bool            $bypass     If set to true, the function does return result set directly.
     *
     * @return          mixed           $response
     */
    public function isMemberGrantedAction($member, $action, $bypass = false) {
        $this->resetResponse();
        $member = $this->validateAndGetMember($member);
        $action = $this->validateAndGetAction($action);
        if (!is_bool($bypass)) {
            return $this->createException('InvalidParameter', '$bypass parameter must hold boolean value. '.gettype($bypass).' value supplied.', 'msg.error.invalid.parameter.group');
        }
        /**
         * 2. Prepare query string.
         */
        $query_str = 'SELECT '.$this->entity['member_access_right']['alias'].' FROM '.$this->entity['member_access_right']['name']
                . ' WHERE '.$this->entity['member_access_right']['alias'].'.member = :group'
                . ' AND '.$this->entity['member_access_right']['alias'].'.action = :action'
                . ' AND '.$this->entity['member_access_right']['alias'].'.right = \'g\''
                . ' LIMIT 1';
        /**
         * 3. Create query object.
         */
        $query = $this->em->createQuery($query_str);
        $query->setParameter('action', $action);
        $query->setParameter('member', $member);

        $result = $query->getSingleResult();
        $set = false;
        if($result){
            $set = true;
        }
        /**
         * 5. Prepare and return response
         */
        $this->response = array(
	    'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $set,
                'total_rows' => 1,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'msg.success.db.entity.exists',
        );
        /**
         * If response is not bypassed return $response
         * Otherwise either return $result or true.
         */
        if (!$bypass) {
            return $this->response;
        }
        if (!result) {
            return $result;
        }
        return true;
    }
    /**
     * @name 			isMemberGroupGrantedAction()
     *  				Checks if the member group has access rights for given action.
     *
     *                  In response:
     *                  Returns true if the action right is set to g or false if the action right is not set or set to r
     *                  g: granted
     *                  r: revoked
     *
     * @since			1.0.0
     * @version         1.1.0
     * @author          Can Berkol
     *
     * @use             $this->createException()
     * @use             $this->resetResponse()
     * @use             $this->validateAndGetMemberGroup()
     * @use             $this->validateAndGetAction()
     *
     * @param           mixed           $group      MemberGroup Entity or member group id.
     * @param           mixed           $action     Action Entity or action id.
     * @param           bool            $bypass     If set to true, the function does return result set directly.
     *
     * @return          mixed           $response
     */
    public function isMemberGroupGrantedAction($group, $action, $bypass = false) {
        $this->resetResponse();
        if (!is_bool($bypass)) {
            return $this->createException('InvalidParameter', '$bypass parameter must hold boolean value. '.gettype($bypass).' value supplied.', 'msg.error.invalid.parameter.group');
        }
        $group = $this->validateAndGetMemberGroup($group);
        $action = $this->validateAndGetAction($action);
        /**
         * 2. Prepare query string.
         */
        $query_str = 'SELECT ' . $this->entity['member_group_access_right']['alias'] . ' FROM ' . $this->entity['member_group_access_right']['name']
            . ' WHERE ' . $this->entity['member_group_access_right']['alias'] . '.member_group = :group'
            . ' AND ' . $this->entity['member_group_access_right']['alias'] . '.action = :action'
            . ' AND ' . $this->entity['member_group_access_right']['alias'] . '.right = \'g\''
            . ' LIMIT 1';
        /**
         * 3. Create query object.
         */
        $query = $this->em->createQuery($query_str);
        $query->setParameter('action', $action);
        $query->setParameter('group', $group);

        $result = $query->getSingleResult();
        $set = false;
        if($result){
            $set = true;
        }
        /**
         * 5. Prepare and return response
         */
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $set,
                'total_rows' => 1,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.access.validated',
        );
        /**
         * If response is not bypassed return $response
         * Otherwise either return $result or true.
         */
        if (!$bypass) {
            return $this->response;
        }
        if (!result) {
            return $result;
        }
        return true;
    }
    /**
     * @name            listGrantedActionsOfMember()
     *                  List all actions with grant right g for given member
     *
     * @since		    1.0.5
     * @version         1.0.7
     *
     * @author          Can Berkol
     * @author          Said Imamoglu
     *
     * @use             $this->resetResponse()
     * @use             $this->createException()
     *
     *
     * @param           mixed           $member         integer / id, or Member entity.
     *
     * @return          array           $response
     */
    public function listGrantedActionsOfMember($member){
        $this->resetResponse();
        if($member instanceof MMBEntity\Member){
            $member = $member->getId();
        }

        $qStr = 'SELECT '.$this->entity['member_access_right']['alias']
            .' FROM '.$this->entity['member_access_right']['name'].' '.$this->entity['member_access_right']['alias']
            ." WHERE ".$this->entity['member_access_right']['alias'].".right = 'g'"
            .' AND '.$this->entity['member_access_right']['alias'].'.member = '.$member;

        $query = $this->em->createQuery($qStr);
        $result = $query->getResult();

        $total_rows = count($result);

        if ($total_rows < 1) {
            $this->response['code'] = 'err.db.entry.notexist';
            return $this->response;
        }
        $actions = array();
        foreach($result as $marEntry){
            $actions[] = $marEntry->getAction();
        }

        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $actions,
                'total_rows' => $total_rows,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }
    /**
     * @name            listGrantedActionsOfMemberGroup()
     *                  List all actions with grant right g for given member
     *
     * @since		    1.0.5
     * @version         1.0.7
     *
     * @author          Can Berkol
     * @author          Said Imamoglu
     *
     * @use             $this->resetResponse()
     * @use             $this->createException()
     *
     *
     * @param           mixed           $group         integer / id, or Member entity.
     *
     * @return          array           $response
     */
    public function listGrantedActionsOfMemberGroup($group){
        $this->resetResponse();
        if($group instanceof MMBEntity\MemberGroup){
            $group = $group->getId();
        }

        $qStr = 'SELECT '.$this->entity['member_group_access_right']['alias']
            .' FROM '.$this->entity['member_group_access_right']['name'].' '.$this->entity['member_group_access_right']['alias']
            ." WHERE ".$this->entity['member_group_access_right']['alias'].".right = 'g'"
            .' AND '.$this->entity['member_group_access_right']['alias'].'.member_group = '.$group;

        $query = $this->em->createQuery($qStr);
        $result = $query->getResult();

        $total_rows = count($result);

        if ($total_rows < 1) {
            $this->response['code'] = 'err.db.entry.notexist';
            return $this->response;
        }
        $actions = array();
        foreach($result as $mgarEntry){
            $actions[] = $mgarEntry->getAction();
        }
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $actions,
                'total_rows' => $total_rows,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }

    /**
     * @name            listMemberAccessRights()
     *                  List items of a given collection.
     *
     * @since		    1.0.0
     * @version         1.0.5
     *
     * @author          Can Berkol
     * @author          Said Imamoglu
     *
     * @use             $this->resetResponse()
     * @use             $this->createException()
     * 
     *
     * @param           mixed           $filter                Multi dimensional array
     * @param           array           $sortorder              Array
     *                                                              'column'    => 'asc|desc'
     * @param           array           $limit
     *                                      start
     *                                      count
     * @param           string           $query_str             If a custom query string needs to be defined.
     *
     * @return          array           $response
     */
    public function listMemberAccessRights($filter = null, $sortorder = null, $limit = null, $query_str = null){
        $this->resetResponse();
        if (!is_array($sortorder) && !is_null($sortorder)) {
            return $this->createException('InvalidSortOrder', '', 'err.invalid.parameter.sortorder');
        }
        /**
         * Add filter checks to below to set join_needed to true.
         */
        /**         * ************************************************** */
        $order_str = '';
        $where_str = '';
        $group_str = '';
        $filter_str = '';

        /**
         * Start creating the query.
         *
         * Note that if no custom select query is provided we will use the below query as a start.
         */
        if (is_null($query_str)) {
            $query_str = 'SELECT '.$this->entity['member_access_rights']['alias']
                            .' FROM '.$this->entity['member_access_rights']['name'].' '.$this->entity['member_access_rights']['alias'];
        }
        /**
         * Prepare ORDER BY section of query.
         */
        if ($sortorder != null) {
            foreach ($sortorder as $column => $direction) {
                switch ($column) {
                    case 'date_assigned':
                        $column = $this->entity['member_access_rights']['alias'].'.'.$column;
                        break;
                }
                $order_str .= ' '.$column.' '.strtoupper($direction).', ';
            }
            $order_str = rtrim($order_str, ', ');
            $order_str = ' ORDER BY ' . $order_str . ' ';
        }

        /**
         * Prepare WHERE section of query.
         */
        if ($filter != null) {
            $filter_str = $this->prepareWhere($filter);
            $where_str .= ' WHERE ' . $filter_str;
        }

        $qStr = $where_str.$group_str.$order_str;

        $query = $this->em->createQuery($qStr);
        if(!is_null($limit)){
            $query = $this->em->createQuery($qStr);
        }

        /**
         * Prepare & Return Response
         */
        $result = $query->getResult();

        $total_rows = count($result);

        if ($total_rows < 1) {
            $this->response['code'] = 'err.db.entry.notexist';
            return $this->response;
        }
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $result,
                'total_rows' => $total_rows,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }

    /**
     * @name            listMemberGroupAccessRights()
     *                  List items of a given collection.
     *
     * @since		    1.0.0
     * @version         1.0.5
     *
     * @author          Can Berkol
     * @author          Said Imamoglu
     *
     * @use             $this->resetResponse()
     * @use             $this->createException()
     *
     *
     * @param           mixed           $filter                Multi dimensional array
     * @param           array           $sortorder              Array
     *                                                              'column'    => 'asc|desc'
     * @param           array           $limit
     *                                      start
     *                                      count
     * @param           string           $query_str             If a custom query string needs to be defined.
     *
     * @return          array           $response
     */
    public function listMemberGroupAccessRights($filter = null, $sortorder = null, $limit = null, $query_str = null){
        $this->resetResponse();
        if (!is_array($sortorder) && !is_null($sortorder)) {
            return $this->createException('InvalidSortOrder', '', 'err.invalid.parameter.sortorder');
        }
        /**
         * Add filter checks to below to set join_needed to true.
         */
        /**         * ************************************************** */
        $order_str = '';
        $where_str = '';
        $group_str = '';
        $filter_str = '';

        /**
         * Start creating the query.
         *
         * Note that if no custom select query is provided we will use the below query as a start.
         */
        if (is_null($query_str)) {
            $query_str = 'SELECT '.$this->entity['member_group_access_rights']['alias']
                .' FROM '.$this->entity['member_group_access_rights']['name'].' '.$this->entity['member_group_access_rights']['alias'];
        }
        /**
         * Prepare ORDER BY section of query.
         */
        if ($sortorder != null) {
            foreach ($sortorder as $column => $direction) {
                switch ($column) {
                    case 'date_assigned':
                        $column = $this->entity['member_group_access_rights']['alias'].'.'.$column;
                        break;
                }
                $order_str .= ' '.$column.' '.strtoupper($direction).', ';
            }
            $order_str = rtrim($order_str, ', ');
            $order_str = ' ORDER BY ' . $order_str . ' ';
        }

        /**
         * Prepare WHERE section of query.
         */
        if ($filter != null) {
            $filter_str = $this->prepareWhere($filter);
            $where_str .= ' WHERE ' . $filter_str;
        }

        $qStr = $where_str.$group_str.$order_str;

        $query = $this->em->createQuery($qStr);
        if(!is_null($limit)){
            $query = $this->em->createQuery($qStr);
        }

        /**
         * Prepare & Return Response
         */
        $result = $query->getResult();

        $total_rows = count($result);

        if ($total_rows < 1) {
            $this->response['code'] = 'err.db.entry.notexist';
            return $this->response;
        }
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $result,
                'total_rows' => $total_rows,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }
    /**
     * @name            listRevokedActionsOfMember()
     *                  List all actions with grant right r for given member
     *
     * @since		    1.0.8
     * @version         1.0.8
     *
     * @author          Can Berkol
     *
     * @use             $this->resetResponse()
     * @use             $this->createException()
     *
     *
     * @param           mixed           $member         integer / id, or Member entity.
     *
     * @return          array           $response
     */
    public function listRevokedActionsOfMember($member){
        $this->resetResponse();
        if($member instanceof MMBEntity\Member){
            $member = $member->getId();
        }

        $qStr = 'SELECT '.$this->entity['member_access_right']['alias']
            .' FROM '.$this->entity['member_access_right']['name'].' '.$this->entity['member_access_right']['alias']
            ." WHERE ".$this->entity['member_access_right']['alias'].".right = 'r'"
            .' AND '.$this->entity['member_access_right']['alias'].'.member = '.$member;

        $query = $this->em->createQuery($qStr);
        $result = $query->getResult();

        $total_rows = count($result);

        if ($total_rows < 1) {
            $this->response['code'] = 'err.db.entry.notexist';
            return $this->response;
        }
        $actions = array();
        foreach($result as $marEntry){
            $actions[] = $marEntry->getAction();
        }

        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $actions,
                'total_rows' => $total_rows,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }
    /**
     * @name            listRevokedActionsOfMemberGroup()
     *                  List all actions with grant right g for given member
     *
     * @since		    1.0.8
     * @version         1.0.8
     *
     * @author          Can Berkol
     *
     * @use             $this->resetResponse()
     * @use             $this->createException()
     *
     *
     * @param           mixed           $group         integer / id, or Member entity.
     *
     * @return          array           $response
     */
    public function listRevokedActionsOfMemberGroup($group){
        $this->resetResponse();
        if($group instanceof MMBEntity\MemberGroup){
            $group = $group->getId();
        }

        $qStr = 'SELECT '.$this->entity['member_group_access_right']['alias']
            .' FROM '.$this->entity['member_group_access_right']['name'].' '.$this->entity['member_group_access_right']['alias']
            ." WHERE ".$this->entity['member_group_access_right']['alias'].".right = 'r'"
            .' AND '.$this->entity['member_group_access_right']['alias'].'.member_group = '.$group;

        $query = $this->em->createQuery($qStr);
        $result = $query->getResult();

        $total_rows = count($result);

        if ($total_rows < 1) {
            $this->response['code'] = 'err.db.entry.notexist';
            return $this->response;
        }
        $actions = array();
        foreach($result as $mgarEntry){
            $actions[] = $mgarEntry->getAction();
        }
        $this->response = array(
            'rowCount' => $this->response['rowCount'],
            'result' => array(
                'set' => $actions,
                'total_rows' => $total_rows,
                'last_insert_id' => null,
            ),
            'error' => false,
            'code' => 'scc.db.entry.exist',
        );
        return $this->response;
    }
    /**
     * @name            validateAndGetAction()
     *                  Validates $action  parameter and returns BiberLtd\Bundle\LogBundle\Entity\Action if found in database.
     *
     * @since           1.0.9
     * @version         1.0.9
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           mixed           $action
     *
     * @return          object          BiberLtd\Bundle\LogBundle\Entity\Action
     */
    private function validateAndGetAction($action){
        $model = new LBService\LogModel($this->kernel, $this->db_connection, $this->orm);
        if (!is_numeric($action) && !is_string($action) && !$action instanceof LBEntity\Action) {
            return $this->createException('InvalidParameter', '$action parameter must hold BiberLtd\\Core\\Bundles\\LogBundle\\Entity\\Action Entity, string representing action code, or integer representing database row id', 'msg.error.invalid.parameter.action');
        }
        if (is_numeric($action)) {
            $response = $model->getAction($action, 'id');
            if ($response['error']) {
                return $this->createException('EntityDoesNotExist', 'Table: member, id: '.$action, 'msg.error.db.action.notfound');
            }
            $action = $response['result']['set'];
            unset($response);
        }
        else if(is_string($action)){
            $response = $model->getAction($action, 'code');
            if($response['error']) {
                return $this->createException('EntityDoesNotExist', 'Table: member, id: '.$action, 'msg.error.db.action.notfound');
            }
            $action = $response['result']['set'];
            unset($response);
        }
        return $action;
    }
    /**
     * @name            validateAndGetMember()
     *                  Validates $member parameter and returns BiberLtd\Bundle\MemberManagementBundle\Entity\Member if found in database.
     *
     * @since           1.0.9
     * @version         1.0.9
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           mixed           $member
     *
     * @return          object          BiberLtd\Bundle\ProductManagementBundle\Entity\Member
     */
    private function validateAndGetMember($member){
        $model = new MMBService\MemberManagementModel($this->kernel, $this->db_connection, $this->orm);
        if (!is_numeric($member) && !is_string($member) && !$member instanceof MMBEntity\Member) {
            return $this->createException('InvalidParameter', '$member parameter must hold BiberLtd\\Core\\Bundles\\MemberManagementBundle\\Entity\\Member Entity, string representing username or email, or integer representing database row id', 'msg.error.invalid.parameter.member');
        }
        if (is_numeric($member)) {
            $response = $model->getMember($member, 'id');
            if ($response['error']) {
                return $this->createException('EntityDoesNotExist', 'Table: member, id: '.$member, 'msg.error.db.member.notfound');
            }
            $member = $response['result']['set'];
            unset($response);
        }
        else if(is_string($member)){
            $response = $model->getMember($member, 'username');
            if($response['error']) {
                $response = $model->getMember($member, 'email');
                if($response['error']){
                    return $this->createException('EntityDoesNotExist', 'Table: member, id: '.$member, 'msg.error.db.member.notfound');
                }
            }
            $member = $response['result']['set'];
            unset($response);
        }
        return $member;
    }
    /**
     * @name            validateAndGetMemberGroup()
     *                  Validates $member parameter and returns BiberLtd\Bundle\MemberManagementBundle\Entity\MemberGroup if found in database.
     *
     * @since           1.0.9
     * @version         1.0.9
     * @author          Can Berkol
     *
     * @use             $this->createException()
     *
     * @param           mixed           $group
     *
     * @return          object          BiberLtd\Bundle\ProductManagementBundle\Entity\MemberGroup
     */
    private function validateAndGetMemberGroup($group){
        $model = new MMBService\MemberManagementModel($this->kernel, $this->db_connection, $this->orm);
        if (!is_numeric($group) && !is_string($group) && !$group instanceof MMBEntity\MemberGroup) {
            return $this->createException('InvalidParameter', '$group parameter must hold BiberLtd\\Core\\Bundles\\MemberManagementBundle\\Entity\\MemberGroup Entity, string representing url_key, or integer representing database row id', 'msg.error.invalid.parameter.group');
        }
        if (is_numeric($group)) {
            $response = $model->getMemberGroup($group, 'id');
            if ($response['error']) {
                return $this->createException('EntityDoesNotExist', 'Table: member_group, id: '.$group, 'msg.error.db.member.group.notfound');
            }
            $member = $response['result']['set'];
            unset($response);
        }
        else if(is_string($group)){
            $response = $model->getMemberGroup($group, 'url_key');
            if($response['error']) {
                return $this->createException('EntityDoesNotExist', 'Table: member, id: '.$group, 'msg.error.db.member.group.notfound');
            }
            $group = $response['result']['set'];
            unset($response);
        }
        return $group;
    }
}

/**
 * Change Log
 * **************************************
 * v1.1.0                      Can Berkol
 * 09.07.2014
 * **************************************
 * U validateAndGetAction()
 * U validateAdGetMemberGroup()
 *
 * **************************************
 * v1.0.9                      Can Berkol
 * 06.07.2014
 * **************************************
 * A validateAndGetAction()
 * A validateAndGetMember()
 * A validateAndGetMemberGroup()
 * U grantRightToMember()
 * U grantRightToMemberGroup()
 *
 * **************************************
 * v1.0.8                      Can Berkol
 * 04.06.2014
 * **************************************
 * U listRevokedActionsOfMember()
 * U listRevokedActionsOfMemberGroup()
 *
 * **************************************
 * v1.0.7                      Can Berkol
 * 29.04.2014
 * **************************************
 * U listGrantedActionsOfMember()
 * U listGrantedActionsOfMemberGroup()
 *
 * **************************************
 * v1.0.6                      Can Berkol
 * 24.04.2014
 * **************************************
 * U listGrantedActionsOfMember()
 * U listGrantedActionsOfMemberGroup()
 *
 * **************************************
 * v1.0.5                     Can Berkol
 * 23.04.2014
 * **************************************
 * Rewrite from scratch!!!!
 *
 * **************************************
 * v1.0.4                      Can Berkol
 * 16.11.2013
 * **************************************
 * A isMemberGrantedAction()
 * M Now extends CoreModel
 * M Methods are now camelCase
 *
 * **************************************
 * v1.0.3                      Can Berkol
 * 06.11.2013
 * **************************************
 * M Response error messages updated.
 *
 * **************************************
 * v1.0.0                      Can Berkol
 * 02.08.2013
 * **************************************
 * A __construct()
 * A __destruct()
 * A is_member_group_granted_action()
 */