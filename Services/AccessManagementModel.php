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
 * @version     1.1.2
 * @date        08.06.2015
 */

namespace BiberLtd\Bundle\AccessManagementBundle\Services;

/** Entities to be used */
use BiberLtd\Bundle\AccessManagementBundle\Entity as BundleEntity;
use BiberLtd\Bundle\CoreBundle\Responses\ModelResponse;
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
     * @version         1.1.2
     *
     * @param           object          $kernel
     * @param           string          $dbConnection  Database connection key as set in app/config.yml
     * @param           string          $orm            ORM that is used.
     */
    public function __construct($kernel, $dbConnection = 'default', $orm = 'doctrine') {
        parent::__construct($kernel, $dbConnection, $orm);
        /**
         * Register entity names for easy reference.
         */
        $this->entity = array(
            'mar' => array('name' => 'AccessManagementBundle:MemberAccessRight', 'alias' => 'mar'),
            'mgar' => array('name' => 'AccessManagementBundle:MemberGroupAccessRight', 'alias' => 'mgar'),
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
     * @version         1.1.2
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
        $timeStamp = time();
        $mModel = new MMBService\MemberManagementModel($this->kernel, $this->dbConnection, $this->orm);
        $aModel = new LBService\LogModel($this->kernel, $this->dbConnection, $this->orm);
        $response = $mModel->getMember($member);
        if($response->error->exist){
            return $response;
        }
        $member = $response->result->set;
        $response = $aModel->getAction($action);
        if($response->error->exist){
            return $response;
        }
        $action = $response->result->set;

        $ar = new BundleEntity\MemberAccessRight();
        $ar->setAction($action);
        $ar->setMember($member);
        $ar->setRight("g");
        $ar->setDateAssigned(new \DateTime('now', new \DateTimeZone(($this->kernel->getContainer()->getParameter('app_timezone')))));

        $this->em->persist($ar);
        $this->em->flush($ar);

        return new ModelResponse($ar, 1, 0, null, false, 'S:D:003', 'Selected entries have been successfully inserted into database.', $timeStamp, time());

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
     * @version         1.1.2
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
        $timeStamp = time();
        $mModel = new MMBService\MemberManagementModel($this->kernel, $this->dbConnection, $this->orm);
        $aModel = new LBService\LogModel($this->kernel, $this->dbConnection, $this->orm);
        $response  = $mModel->getMember($group);
        if($response->error->exist){
            return $response;
        }
        $group = $response->result->set;
        $response = $aModel->getAction($action);
        if($response->error->exist){
            return $response;
        }
        $action = $response->result->set;

        $ar = new BundleEntity\MemberGroupAccessRight();
        $ar->setAction($action);
        $ar->setMemberGroup($group);
        $ar->setRight("g");

        $ar->setDateAssigned(new \DateTime('now', new \DateTimeZone(($this->kernel->getContainer()->getParameter('app_timezone')))));

        $this->em->persist($ar);
        $this->em->flush($ar);

        return new ModelResponse($ar, 1, 0, null, false, 'S:D:003', 'Selected entries have been successfully inserted into database.', $timeStamp, time());
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
     * @version         1.1.2
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
        $timeStamp = time();
        $mModel = new MMBService\MemberManagementModel($this->kernel, $this->dbConnection, $this->orm);
        $aModel = new LBService\LogModel($this->kernel, $this->dbConnection, $this->orm);
        $response = $mModel->getMember($member);
        if($response->error->exist){
            return $response;
        }
        $member = $response->result->set;
        $response = $aModel->getAction($action);
        if($response->error->exist){
            return $response;
        }
        $action = $response->result->set;
        if (!is_bool($bypass)) {
            return $this->createException('InvalidParameter', '$bypass parameter must hold boolean value. '.gettype($bypass).' value supplied.', 'msg.error.invalid.parameter.group');
        }
        /**
         * 2. Prepare query string.
         */
        $query_str = 'SELECT '.$this->entity['mar']['alias'].' FROM '.$this->entity['mar']['name']
            . ' WHERE '.$this->entity['mar']['alias'].'.member = :group'
            . ' AND '.$this->entity['mar']['alias'].'.action = :action'
            . ' AND '.$this->entity['mar']['alias'].'.right = \'g\''
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

        if (!$bypass) {
            return new ModelResponse($set, 1, 0, null, false, 'S:D:003', 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
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
     * @version         1.1.2
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
        $timeStamp = time();
        if (!is_bool($bypass)) {
            return $this->createException('InvalidParameter', '$bypass parameter must hold boolean value. '.gettype($bypass).' value supplied.', 'msg.error.invalid.parameter.group');
        }
        $mModel = new MMBService\MemberManagementModel($this->kernel, $this->dbConnection, $this->orm);
        $aModel = new LBService\LogModel($this->kernel, $this->dbConnection, $this->orm);
        $response = $mModel->getGroup($group);
        if($response->error->exist){
            return $response;
        }
        $group = $response->result->set;
        $response = $aModel->getAction($action);
        if($response->error->exist){
            return $response;
        }
        $action = $response->result->set;
        /**
         * 2. Prepare query string.
         */
        $query_str = 'SELECT ' . $this->entity['mgar']['alias'] . ' FROM ' . $this->entity['mgar']['name']
            . ' WHERE ' . $this->entity['mgar']['alias'] . '.member_group = :group'
            . ' AND ' . $this->entity['mgar']['alias'] . '.action = :action'
            . ' AND ' . $this->entity['mgar']['alias'] . '.right = \'g\''
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

        if (!$bypass) {
            return new ModelResponse($set, 1, 0, null, false, 'S:D:003', 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
        }
        if (!$result) {
            return $result;
        }
        return true;
    }
    /**
     * @name            listGrantedActionsOfMember()
     *                  List all actions with grant right g for given member
     *
     * @since		    1.0.5
     * @version         1.1.2
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
        $timeStamp = time();
        $mModel = new MMBService\MemberManagementModel($this->kernel, $this->dbConnection, $this->orm);

        $response = $mModel->getMember($member);
        if($response->error->exist){
            return $response;
        }
        $member = $response->result->set;

        $qStr = 'SELECT '.$this->entity['mar']['alias']
            .' FROM '.$this->entity['mar']['name'].' '.$this->entity['mar']['alias']
            ." WHERE ".$this->entity['mar']['alias'].".right = 'g'"
            .' AND '.$this->entity['mar']['alias'].'.member = '.$member->getId();

        $query = $this->em->createQuery($qStr);
        $result = $query->getResult();

        $total_rows = count($result);

        if ($total_rows < 1) {
            $this->response->code = 'err.db.entry.notexist';
            return $this->response;
        }
        $actions = array();
        foreach($result as $marEntry){
            $actions[] = $marEntry->getAction();
        }
        return new ModelResponse($actions, count($actions), 0, null, false, 'S:D:003', 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
    }
    /**
     * @name            listGrantedActionsOfMemberGroup()
     *
     * @since		    1.0.5
     * @version         1.1.2
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
        $timeStamp = time();
        $mModel = new MMBService\MemberManagementModel($this->kernel, $this->dbConnection, $this->orm);

        $response = $mModel->getGroup($group);
        if($response->error->exist){
            return $response;
        }
        $group = $response->result->set;
        $qStr = 'SELECT '.$this->entity['mgar']['alias']
            .' FROM '.$this->entity['mgar']['name'].' '.$this->entity['mgar']['alias']
            ." WHERE ".$this->entity['mgar']['alias'].".right = 'g'"
            .' AND '.$this->entity['mgar']['alias'].'.member_group = '.$group->getId();

        $query = $this->em->createQuery($qStr);
        $result = $query->getResult();

        $total_rows = count($result);

        if ($total_rows < 1) {
            $this->response->code = 'err.db.entry.notexist';
            return $this->response;
        }
        $actions = array();
        foreach($result as $mgarEntry){
            $actions[] = $mgarEntry->getAction();
        }
        return new ModelResponse($actions, count($actions), 0, null, false, 'S:D:003', 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
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
     * @param           array           $sortOrder              Array
     *                                                              'column'    => 'asc|desc'
     * @param           array           $limit
     *                                      start
     *                                      count
     *
     * @return          array           $response
     */
    public function listMemberAccessRights($filter = null, $sortOrder = null, $limit = null){
        $timeStamp = time();
        if(!is_array($sortOrder) && !is_null($sortOrder)){
            return $this->createException('InvalidSortOrderException', '$sortOrder must be an array with key => value pairs where value can only be "asc" or "desc".', 'E:S:002');
        }

        $oStr = $wStr = $gStr = $fStr = '';

        $qStr = 'SELECT '.$this->entity['mar']['alias']
            .' FROM '.$this->entity['mar']['name'].' '.$this->entity['mar']['alias'];

        if ($sortOrder != null) {
            foreach ($sortOrder as $column => $direction) {
                switch ($column) {
                    case 'date_assigned':
                        $column = $this->entity['mar']['alias'].'.'.$column;
                        break;
                }
                $oStr .= ' '.$column.' '.strtoupper($direction).', ';
            }
            $oStr = rtrim($oStr, ', ');
            $oStr = ' ORDER BY ' . $oStr . ' ';
        }

        if ($filter != null) {
            $fStr = $this->prepareWhere($filter);
            $wStr .= ' WHERE ' . $fStr;
        }

        $qStr .= $wStr.$gStr.$oStr;

        $q = $this->em->createQuery($qStr);
        if(!is_null($limit)){
            $q = $this->em->createQuery($qStr);
        }

        $result = $q->getResult();

        $totalRows = count($result);

        if ($totalRows < 1) {
            return new ModelResponse(null, 0, 0, null, true, 'E:D:002', 'No entries found in database that matches to your criterion.', $timeStamp, time());
        }
        return new ModelResponse($result, $totalRows, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
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
     * @param           array           $sortOrder              Array
     *                                                              'column'    => 'asc|desc'
     * @param           array           $limit
     *                                      start
     *                                      count
     *
     * @return          array           $response
     */
    public function listMemberGroupAccessRights($filter = null, $sortOrder = null, $limit = null){
        $timeStamp = time();
        if(!is_array($sortOrder) && !is_null($sortOrder)){
            return $this->createException('InvalidSortOrderException', '$sortOrder must be an array with key => value pairs where value can only be "asc" or "desc".', 'E:S:002');
        }

        $oStr = $wStr = $gStr = $fStr = '';

        $qStr = 'SELECT '.$this->entity['mgar']['alias']
            .' FROM '.$this->entity['mgar']['name'].' '.$this->entity['mar']['alias'];

        if ($sortOrder != null) {
            foreach ($sortOrder as $column => $direction) {
                switch ($column) {
                    case 'date_assigned':
                        $column = $this->entity['mgar']['alias'].'.'.$column;
                        break;
                }
                $oStr .= ' '.$column.' '.strtoupper($direction).', ';
            }
            $oStr = rtrim($oStr, ', ');
            $oStr = ' ORDER BY ' . $oStr . ' ';
        }

        /**
         * Prepare WHERE section of query.
         */
        if ($filter != null) {
            $fStr = $this->prepareWhere($filter);
            $wStr .= ' WHERE ' . $fStr;
        }

        $qStr .= $wStr.$gStr.$oStr;

        $q = $this->em->createQuery($qStr);
        if(!is_null($limit)){
            $q = $this->em->createQuery($qStr);
        }

        $result = $q->getResult();

        $totalRows = count($result);

        if ($totalRows < 1) {
            return new ModelResponse(null, 0, 0, null, true, 'E:D:002', 'No entries found in database that matches to your criterion.', $timeStamp, time());
        }
        return new ModelResponse($result, $totalRows, 0, null, false, 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
    }
    /**
     * @name            listRevokedActionsOfMember()
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
        $timeStamp = time();
        $mModel = new MMBService\MemberManagementModel($this->kernel, $this->dbConnection, $this->orm);

        $response = $mModel->getMember($member);
        if($response->error->exist){
            return $response;
        }
        $member = $response->result->set;

        $qStr = 'SELECT '.$this->entity['mar']['alias']
            .' FROM '.$this->entity['mar']['name'].' '.$this->entity['mar']['alias']
            ." WHERE ".$this->entity['mar']['alias'].".right = 'r'"
            .' AND '.$this->entity['mar']['alias'].'.member = '.$member->getId();

        $query = $this->em->createQuery($qStr);
        $result = $query->getResult();

        $total_rows = count($result);

        if ($total_rows < 1) {
            $this->response->code = 'err.db.entry.notexist';
            return $this->response;
        }
        $actions = array();
        foreach($result as $marEntry){
            $actions[] = $marEntry->getAction();
        }

        return new ModelResponse($actions, count($actions), 0, null, false, 'S:D:003', 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
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
        $timeStamp = time();
        $mModel = new MMBService\MemberManagementModel($this->kernel, $this->dbConnection, $this->orm);

        $response = $mModel->getGroup($group);
        if($response->error->exist){
            return $response;
        }
        $group = $response->result->set;
        $qStr = 'SELECT '.$this->entity['mgar']['alias']
            .' FROM '.$this->entity['mgar']['name'].' '.$this->entity['mgar']['alias']
            ." WHERE ".$this->entity['mgar']['alias'].".right = 'r'"
            .' AND '.$this->entity['mgar']['alias'].'.member_group = '.$group->getId();

        $query = $this->em->createQuery($qStr);
        $result = $query->getResult();

        $total_rows = count($result);

        if ($total_rows < 1) {
            $this->response->code = 'err.db.entry.notexist';
            return $this->response;
        }
        $actions = array();
        foreach($result as $mgarEntry){
            $actions[] = $mgarEntry->getAction();
        }
        return new ModelResponse($actions, count($actions), 0, null, false, 'S:D:003', 'S:D:002', 'Entries successfully fetched from database.', $timeStamp, time());
    }
}

/**
 * Change Log
 * **************************************
 * v1.1.2                      08.06.2015
 * Can Berkol
 * **************************************
 * BF :: resetResponse() removed.
 * BF :: Now ModelResponse is being utilized.
 *
 * **************************************
 * v1.1.1                      25.05.2015
 * Can Berkol
 * **************************************
 * BF :: db_connection is replaced with dbConnection
 *
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