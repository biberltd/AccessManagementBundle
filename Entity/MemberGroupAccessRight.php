<?php
/**
 * @name        MemberGroupAccessRight
 * @package		BiberLtd\Bundle\CoreBundle\AccessManagementBundle
 *
 * @author		Murat Ünal
 * @version     1.0.2
 * @date        09.10.2013
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com)
 * @license     GPL v3.0
 *
 * @description Model / Entity class.
 *
 */
namespace BiberLtd\Bundle\AccessManagementBundle\Entity;
use BiberLtd\Bundle\CoreBundle\CoreEntity;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="member_group_access_right",
 *     options={"charset":"utf8","collate":"utf8_turkish_ci","engine":"innodb"},
 *     indexes={@ORM\Index(name="idxNMemberGroupAccessRightDateAssigned", columns={"date_assigned"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="idxUMemberGroupAccessRight", columns={"member_group","action"})}
 * )
 */
class MemberGroupAccessRight extends CoreEntity
{
    /**
     * @ORM\Column(type="string", length=1, nullable=false)
     */
    private $right;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $date_assigned;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\LogBundle\Entity\Action")
     * @ORM\JoinColumn(name="action", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $action;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\MemberManagementBundle\Entity\MemberGroup")
     * @ORM\JoinColumn(name="member_group", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $member_group;
    /******************************************************************
     * PUBLIC SET AND GET FUNCTIONS                                   *
     ******************************************************************/

    /**
     * @name            setRight()
     *  				Sets $right property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @param           string          $right
     *
     * @return          object          $this
     */
    public function setRight($right){
        if(!$this->setModified('right', $right)->isModified()){
            return $this;
        }
        $this->right = $right;

        return $this;
    }
    /**
     * @name            getRight()
     *  				Gets $right property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->right
     */
    public function getRight(){
        return $this->right;
    }
    /**
     * @name            setDateAssigned()
     *  				Sets $date_assigned property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @param           datetime          $date_assigned
     *
     * @return          object          $this
     */
    public function setDateAssigned($date_assigned){
        if(!$this->setModified('date_assigned', $date_assigned)->isModified()){
            return $this;
        }
        $this->date_assigned = $date_assigned;

        return $this;
    }
    /**
     * @name            getDateAssigned()
     *  				Gets $date_assigned property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          datetime          $this->date_assigned
     */
    public function getDateAssigned(){
        return $this->date_assigned;
    }
    /**
     * @name            setAction()
     *  				Sets $action property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @param           BiberLtd\Bundle\LogBundle\Entity\Action          $action
     *
     * @return          object          $this
     */
    public function setAction($action){
        if(!$this->setModified('action', $action)->isModified()){
            return $this;
        }
        $this->action = $action;

        return $this;
    }
    /**
     * @name            getAction()
     *  				Gets $action property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          BiberLtd\Bundle\LogBundle\Entity\Action          $this->action
     */
    public function getAction(){
        return $this->action;
    }
    /**
     * @name            setMember_group()
     *  				Sets $member_group property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @param           BiberLtd\Bundle\MemberManagementBundle\Entity\MemberGroup          $member_group
     *
     * @return          object          $this
     */
    public function setMemberGroup($member_group){
        if(!$this->setModified('member_group', $member_group)->isModified()){
            return $this;
        }
        $this->member_group = $member_group;

        return $this;
    }
    /**
     * @name            getMemberGroup()
     *  				Gets $member_group property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          BiberLtd\Bundle\MemberManagementBundle\Entity\MemberGroup          $this->member_group
     */
    public function getMemberGroup(){
        return $this->member_group;
    }
}
/**
 * Change Log:
 * **************************************
 * v1.0.1                      Murat Ünal
 * 18.07.2013
 * **************************************
 * A getAction()
 * A getDateAssigned()
 * A getMemberGroup()
 * A getRight()
 *
 * A setAction()
 * A setDateAssigned()
 * A setMemberGroup()
 * A setRight()
 */