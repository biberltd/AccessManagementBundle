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
 */
namespace BiberLtd\Bundle\AccessManagementBundle\Services;


use BiberLtd\Bundle\AccessManagementBundle\Entity as BundleEntity;
use BiberLtd\Bundle\CoreBundle\Responses\ModelResponse;
use BiberLtd\Bundle\LogBundle\Entity as LBEntity;
use BiberLtd\Bundle\MemberManagementBundle\Entity as MMBEntity;

use BiberLtd\Bundle\LogBundle\Services as LBService;
use BiberLtd\Bundle\MemberManagementBundle\Services as MMBService;

use BiberLtd\Bundle\CoreBundle\CoreModel;
use BiberLtd\Bundle\CoreBundle\Services as CoreServices;
use BiberLtd\Bundle\CoreBundle\Exceptions as CoreExceptions;

class AccessManagementModel extends CoreModel {

    /**
     * AccessManagementModel constructor.
     *
     * @param object $kernel
     * @param string $dbConnection
     * @param string $orm
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
     * Destructor
     */
    public function __destruct() {
        foreach ($this as $property => $value) {
            $this->$property = null;
        }
    }

    /**
     * @param $member
     * @param $action
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse|mixed
     *
     * In response:
     *                  Returns true if the action right is set to g or false if the action right is not set or set to r
     *                  g: granted
     *                  r: revoked
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
     * @param $group
     * @param $action
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse|mixed
     *
     * In response:
     *                  Returns true if the action right is set to g or false if the action right is not set or set to r
     *                  g: granted
     *                  r: revoked
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
     * @param      $member
     * @param      $action
     * @param bool $bypass
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse|bool|mixed
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
     * @param      $group
     * @param      $action
     * @param bool $bypass
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse|bool
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
     * @param $member
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse|mixed
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
     * @param $group
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse|mixed
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
     * @param null $filter
     * @param null $sortOrder
     * @param null $limit
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
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
     * @param null $filter
     * @param null $sortOrder
     * @param null $limit
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse
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
     * @param $member
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse|mixed
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
     * @param $group
     *
     * @return \BiberLtd\Bundle\CoreBundle\Responses\ModelResponse|mixed
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